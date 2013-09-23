<?php
require_once('./lib/chunk/class.chunk.php');
require_once('./lib/wiky_wikipedia_parser/wiky.inc.php');

$start = microtime(true);
$processed_entries = 0;
$successfully_processed_entries = 0;

// Iterate through XML files in the directory
$handle = opendir('./pubmed');
while (false !== ($file = readdir($handle))){
  $extension = strtolower(substr(strrchr($file, '.'), 1));
  if($extension == "xml"){

	$file = new Chunk($file, array('element' => 'PubmedArticle', 'path' => './pubmed/'));

	while ($xml_string = $file->read()) {
		$processed_entries++;
		// print mb_detect_encoding($xml);

		$article = new SimpleXMLElement($xml_string);

		// Fetch PMID
		$pmid = $article->MedlineCitation->PMID;
		//print("Processing entry with PMID " . $pmid . "\n");

		// Fetch article title
		$article_title = $article->MedlineCitation->Article->ArticleTitle;

		// Fetch abstract text
		$abstract_text = "";
		foreach ($article->MedlineCitation->Article->Abstract->AbstractText as $abstract_text_xml_element) {
			if (isset($abstract_text_xml_element["Label"])) {
				$abstract_text .= " " . $abstract_text_xml_element["Label"] . ": ";
			}
			$abstract_text .= $abstract_text_xml_element;
		}
		$abstract_text = trim($abstract_text);
		
		
		if ($abstract_text == "") continue;

		// Fetch ISO abbreviation of journal title
		$iso_abbreviation = $article->MedlineCitation->Article->Journal->ISOAbbreviation;

		// Fetch publication date and convert it to Solr format (YYYY-MM-DDThh:mm:ssZ)
		$date_created = 	$article->MedlineCitation->DateCreated->Year . "-" .
							str_pad($article->MedlineCitation->DateCreated->Month, 2, '0', STR_PAD_LEFT) . "-" .
							str_pad($article->MedlineCitation->DateCreated->Day, 2, '0', STR_PAD_LEFT) . "T12:00:00Z";

		// Extract conclusions section
		$abstract_conclusion_section_text = "";

		//print($abstract_text . "\n\n");
		preg_match("/conclusion[s]?[.]?[:]? ([^©^Â]+)/i", $abstract_text, $abstract_conclusion_section_text);
		$abstract_conclusion_section_text = trim($abstract_conclusion_section_text[1]);

		$output_file = fopen("pubmed_solr_xml_output/pubmed_" . $pmid . ".xml", "w");
		fputs($output_file, "<add><doc>\n");
				
		// Add key assertion with expanded abbreviations if a "conclusion" snippet was found. 
		if ($abstract_conclusion_section_text != "") {
			// Replace abbreviations with expanded forms
			$abbreviation_expander_output = trim(shell_exec('java ExtractAbbrev "' . addslashes($abstract_text) . '"'));
			$abbreviation_expander_output_lines = explode("\n", $abbreviation_expander_output);
			foreach ($abbreviation_expander_output_lines as $line) {
				if (trim($line) == "") continue;
				$abbreviation_mapping = explode("\t", $line);
				$abstract_conclusion_section_text = str_replace(" " . $abbreviation_mapping[0], " " . $abbreviation_mapping[1], $abstract_conclusion_section_text);
			}
			fputs($output_file, "<field name='key_assertion'>" . htmlspecialchars($abstract_conclusion_section_text) . "</field>\n");
		}
		
		fputs($output_file, "<field name='title'>" . htmlspecialchars($article_title) . "</field>\n");
		fputs($output_file, "<field name='body'>" . htmlspecialchars($abstract_text) . "</field>\n");
		fputs($output_file, "<field name='data_source_name'>" . htmlspecialchars($iso_abbreviation) . "</field>\n");
		fputs($output_file, "<field name='dateCreated'>" . $date_created . "</field>\n");
		fputs($output_file, "<field name='id'>http://www.ncbi.nlm.nih.gov/pubmed/" . $pmid . "</field>\n");
		fputs($output_file, "<field name='mimeType'>text/plain</field>\n");

		fputs($output_file, "</doc></add>");
		fclose($output_file);

		$successfully_processed_entries++;
		print("\n" . $successfully_processed_entries . " ");
		if ($successfully_processed_entries == 10) break;
	}

  }
}

print "Processed $successfully_processed_entries out of $processed_entries records successfully. ";
$end = (microtime(true) - $start) ;
print "Completed in {$end} seconds.";

?>