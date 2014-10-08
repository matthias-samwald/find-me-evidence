<?php

require_once ('./lib/chunk/class.chunk.php');
require_once ('./lib/wiky_wikipedia_parser/wiky.inc.php');
require_once ('./lib/http_post/http_post.php');

include ('./config.php');

$start = microtime(true);
$processed_entries = 0;
$successfully_processed_entries = 0;

$db = new SQLite3('./pubmed/oa_db');

// Iterate through XML files in the directory
$handle = opendir('./pubmed');
while (false !== ($file = readdir($handle))) {
    $extension = strtolower(substr(strrchr($file, '.'), 1));
    if ($extension == "xml") {

        $file = new Chunk($file, array(
            'element' => 'PubmedArticle',
            'path' => './pubmed/'
        ));

        while ($xml_string = $file->read()) {
            $processed_entries ++;
            // print mb_detect_encoding($xml);

            $article = new SimpleXMLElement($xml_string);

            // Fetch PMID
            $pmid = $article->MedlineCitation->PMID;
            // print("Processing entry with PMID " . $pmid . "\n");
            
            // Get citationcount via eutils
//            $citedin_count = pubmed_count_citedin($pmid);
            
            // Fetch DOI
            $doi = $article->xpath("/PubmedArticle/PubmedData/ArticleIdList/ArticleId[@IdType='doi']");
            
            //Fetch PMC
            $pmc = $article->xpath("/PubmedArticle/PubmedData/ArticleIdList/ArticleId[@IdType='pmc']");

            // Fetch Authors
            $authorsXML = $article->xpath("/PubmedArticle/MedlineCitation/Article/AuthorList/Author");
            $authors = array();
            foreach ($authorsXML as $author) {
                $authors[] = $author->LastName . " " . $author->Initials;
            }

            // Fetch article title
            $article_title = $article->MedlineCitation->Article->ArticleTitle;
            
            // Fetch conclusion
            $conclusion = $article->xpath("/PubmedArticle/MedlineCitation/Article/Abstract/AbstractText[@NlmCategory='CONCLUSIONS']");

            // Fetch abstract text
            $abstract_text = "";
            foreach ($article->MedlineCitation->Article->Abstract->AbstractText as $abstract_text_xml_element) {
                if (isset($abstract_text_xml_element ["Label"])) {
                    $abstract_text .= " " . $abstract_text_xml_element ["Label"] . ": ";
                }
                $abstract_text .= $abstract_text_xml_element;
            }
            $abstract_text = trim($abstract_text);

            if (isset($conclusion[0])) {
                $abstract_conclusion_section_text = $conclusion[0];
            } else {                
                // Extract conclusions section
                $abstract_conclusion_section_text = "";
                preg_match("/conclusion[s]?[.]?[:]? ([^©^Â]+)/i", $abstract_text, $abstract_conclusion_section_text);
                if (isset($abstract_conclusion_section_text [1])) {
                    $abstract_conclusion_section_text = trim($abstract_conclusion_section_text [1]);
                } else {
                    $abstract_conclusion_section_text = "";
                    $abstracts = $article->xpath("/PubmedArticle/MedlineCitation/Article/Abstract/AbstractText");
                    preg_match_all("/.*?(?:\.|\?|!|\:)(\s|$)/", $abstracts[0], $sentences);
                    $number_sentences = count($sentences[0]);
                    $last_n_sentences = array();
                    for ($i = max($number_sentences - 4, 0); $i < $number_sentences; $i++) {
                        $last_n_sentences[] = trim($sentences[0][$i]);
                    }
                    $abstract_conclusion_section_text = implode(" ", $last_n_sentences);
                }
            }

            if ($abstract_text == "")
                continue;

            // Fetch ISO abbreviation of journal title
            $iso_abbreviation = $article->MedlineCitation->Article->Journal->ISOAbbreviation;

            // Fetch publication date and convert it to Solr format (YYYY-MM-DDThh:mm:ssZ)
            $date_created = $article->MedlineCitation->DateCreated->Year . "-" . str_pad($article->MedlineCitation->DateCreated->Month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($article->MedlineCitation->DateCreated->Day, 2, '0', STR_PAD_LEFT) . "T12:00:00Z";
            
            // Fetch PMC release date
            $date_release = $article->PubmedData->History;       
            $date_release_year = $date_release->xpath("//PubMedPubDate[@PubStatus=\"pmc-release\"]/Year");
            $date_release_month = $date_release->xpath("//PubMedPubDate[@PubStatus=\"pmc-release\"]/Month");
            $date_release_day = $date_release->xpath("//PubMedPubDate[@PubStatus=\"pmc-release\"]/Day");
            
//            if (count($date_release_year) !== 0){
//            
//            $date_release_full = $date_release_year[0] . "-" . str_pad($date_release_month[0], 2, '0', STR_PAD_LEFT) . "-" . str_pad($date_release_day[0], 2, '0', STR_PAD_LEFT) . "T12:00:00Z";
//            
//            echo "date: " . $date_release_full . "\n";
//            }                              

            $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><update><add><doc>\n";

            // Add key assertion with expanded abbreviations if a "conclusion" snippet was found.
            if ($abstract_conclusion_section_text != "") {
                // Replace abbreviations with expanded forms
                $abbreviation_expander_output = trim(shell_exec('java ExtractAbbrev "' . addslashes($abstract_text) . '"'));
                $abbreviation_expander_output_lines = explode("\n", $abbreviation_expander_output);
                foreach ($abbreviation_expander_output_lines as $line) {
                    if (trim($line) == "")
                        continue;
                    $abbreviation_mapping = explode("\t", $line);
                    $abstract_conclusion_section_text = str_replace(" " . $abbreviation_mapping [0], " " . $abbreviation_mapping [1], $abstract_conclusion_section_text);
                }
                $output .= "<field name='key_assertion'>" . htmlspecialchars($abstract_conclusion_section_text) . "</field>\n";
            }

            $output .= "<field name='title'>" . htmlspecialchars($article_title) . "</field>\n";
            $output .= "<field name='body'>" . htmlspecialchars($abstract_text) . "</field>\n";
            $output .= "<field name='data_source_name'>PubMed: " . htmlspecialchars($iso_abbreviation) . "</field>\n";
            $output .= "<field name='dateCreated'>" . $date_created . "</field>\n";
            $output .= "<field name='id'>http://www.ncbi.nlm.nih.gov/pubmed/" . $pmid . "</field>\n";
            $output .= "<field name='mimeType'>text/plain</field>\n";
            $output .= "<field name='category'>Pubmed</field>\n";
            $output .= "<field name='dataset_priority'>8</field>\n";
            $output .= "<field name='persid'>" . $doi[0] . "</field>\n";            
//            $output .= "<field name='citedin_count'>" . $citedin_count . "</field>\n";
            foreach ($authors as $author) {
                $output .= "<field name='author'>" . $author . "</field>\n";
            }
            if (count($date_release_year) !== 0){
                $output .= "<field name='dateRelease'>" . $date_release_year[0] . "-" . str_pad($date_release_month[0], 2, '0', STR_PAD_LEFT) . "-" . str_pad($date_release_day[0], 2, '0', STR_PAD_LEFT) . "T12:00:00Z" . "</field>\n";
            }
            
            if (isset($pmc[0])) {
                $output .= "<field name='pmcid'>" . $pmc[0] . "</field>\n";
                $result = $db->querySingle('SELECT id FROM pubmed where id = "' . $pmc[0] . '"');
                if ($result === trim($pmc[0])) {
                    echo $article_title . "\n";
                    $output .= "<field name='oa'>t</field>\n";
                }
            }

            $output .= "</doc></add></update>";

            do_post_request(SOLR_URL . '/update', $output);

            $successfully_processed_entries ++;
            print $successfully_processed_entries . " (" . $pmid . ")\n";
        }
    }
}

print do_post_request ( SOLR_URL . '/update', "<?xml version=\"1.0\" encoding=\"UTF-8\"?><update><commit/><optimize/></update>" );

print "Processed $successfully_processed_entries out of $processed_entries records successfully. ";
$end = (microtime(true) - $start);
print "Completed in {$end} seconds.";

function pubmed_count_citedin($id) {

    $url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi';

    $params = array(
        'dbfrom' => 'pubmed',
        'retmode' => 'xml',
        'id' => $id,
        'cmd' => 'neighbor',
        'linkname' => 'pubmed_pubmed_citedin'
    );
    
    $params_string = "";

    foreach ($params as $key => $value) {
        $params_string .= $key . '=' . $value . '&';
    }
    rtrim($params_string, '&');

    //open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($params));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $xml = simplexml_load_string($response);
        $result = $xml->xpath("/eLinkResult/LinkSet/LinkSetDb[LinkName='pubmed_pubmed_citedin' and DbTo='pubmed']/Link/Id");
        return count($result);
    } else {
        return "";
    }
}