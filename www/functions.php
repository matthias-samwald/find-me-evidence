<?php
include_once ('config.php');
include_once ('functions.php');

date_default_timezone_set('Europe/Vienna');

include_once('logger/Logger.php');
Logger::configure('logger_config.xml');
$logger = Logger::getLogger("main");

function query_solr($q, $category, $rows, $offset = 0) {
	global $categories;
        global $logger;
	
	// TODO: Switch to http_build_query for constructing URL parameters
	
	if ($category == "PubMed by date")
		$sort = "by_date";
	else
		$sort = "by_relevance";
	
	$q = str_replace(":", " ", $q); // Remove double-colons in query (could be interpreted as field names)
	
	$request_url = SOLR_URL . "/select?q=" . urlencode ( $q ) ;
	if ($category != "all") {
		$request_url .= "&fq=" . urlencode ( "{!tag=tagA}category:\"$categories[$category]\"" ); // select facet
	}
	
	if ($category == "PubMed by relevance")
		$request_url .= "&bq=" . urlencode ( 'data_source_name:"PubMed: Cochrane Database Syst Rev"^40' ); // if showing PubMed sorted by relevance, we really want Cochrane reviews at the top!
	else 
		$request_url .= "&bq=" . urlencode ( 'data_source_name:"PubMed: Cochrane Database Syst Rev"^2' );
        
//        if ($category == "PubMed by date and relevance")
//        {
//            $request_url .= "&bf=recip(ms(NOW/HOUR,dateCreated),3.16e-11,1,1)+log(add(citedin_count,1))";
//        }
	
	$request_url .=
	"&defType=edismax" . 	// select query parser
        "&mm=100%25" . // all clauses must match
	"&q.op=AND" . 	// default query operator
	//"&bf=ord(dataset_priority)^4" .
	"&boost=dataset_priority" . // boost results by dataset priority (only works with edismax query parser)
	"&qf=title^3%20key_assertion^2%20text_all" . 	// fields to be queried (can include boosts)
	"&pf=title^3%20key_assertion%20body" . 	// enable automated phrase-matching (boosting fields and setting slop per-field would also be possible here) 
        //boost the score of documents in which all of the terms in the q parameter appear in close proximity
                
	"&ps=2" . 	// default slop for automated phrase-matching
	"&fl=id,title,data_source_name,dateCreated,key_assertion,author,suspicious,dateRelease,pmcid,oa" . 	// only these fields will be listed in the response
	"&start=" . $offset . 	// offset for paginated results
	"&rows=" . $rows . 	// select number of results returned
	"&wt=xml" .	// select result format
	"&facet=true" . 	// switch faceting on
	"&facet.query=" . urlencode ( $q ) . 	// query used for generating faceting counts ... maybe this should be prefixed with {!edismax} or whatever query parser is used, to match results shown to the user? See file:///C:/Users/m/Downloads/SimpleFacetParameters%20-%20Solr%20Wiki.htm
	"&facet.field=" . urlencode ( "{!ex=tagA}category" ) . 	// use this field for faceting
	"&hl=true" . 	// switch highlighting on
	"&hl.fl=body" . 	// use this field for highlighting
	// "&hl.snippets=2" . 	// set maximum number of snippets per field generated. Default is 1.
	"&hl.fragsize=180" . 	// set size of a snippet (in characters)
	// "&hl.fragmenter=regex" . "&hl.regex.slop=0.6" .	// specifies the factor by which the regex fragmenter can stray from the ideal fragment size
	// "&hl.regex.pattern=\w[^\.!\?]{100,500}[\.!\?]" . 	// regex for matching sentences ... does not work too well? TODO: Should also be modified not to match commas in numbers, but only commas followed by a whitespace
	"&hl.mergeContiguous=true" . 	// merge the two snippets into one when they are contiguous
	"&spellcheck=true" . 	// switch spellchecking on
	                     // "&spellcheck.maxResultsForSuggest=5" . // provide spelling suggestions if fewer than n results are returned, by default suggestions are made when there are 0 results
	"&spellcheck.collate=true" . 	// construct a new query for multi token corrections
	"&spellcheck.maxCollationTries=8"; // how many collations to try before giving up making a suggestion

	// Debug (enable/disable by uncommenting/commenting)
	// $request_url .= "&debug=true";
	
	
	if ($sort == "by_date") {
		$request_url .= "&sort=dateCreated+desc";
	}
	
	print "<!--- " . $request_url . " -->";
        $logger->info($request_url);
	$response = file_get_contents ( $request_url );
	$xml = simplexml_load_string ( $response );
	return $xml;
}
function get_solr($id) {
	$request_url = SOLR_URL . "/get?id=" . urlencode ( $id ) . "&wt=xml";
	$response = file_get_contents ( $request_url );
	$xml = simplexml_load_string ( $response );
	return $xml;
}
function xpath($xml, $xpath_expression, $return_entire_array = false) {
        $result_array = $xml->xpath($xpath_expression);
        if ($return_entire_array == false) {
            //to avoid an undefined offset
            if (isset($result_array [0])) {
                return $result_array [0];
            } else {
                return "";
            }
        } else {
            return $result_array;
        }
}
function get_facet_count($xml, $category) {
	global $categories;
	return xpath ( $xml, "//lst[@name='facet_counts']/lst[@name='facet_fields']/lst[@name='category']/int[@name='$categories[$category]']" );
}

function getPdfLink($pmid) {

    $idconv_response = @file_get_contents("http://www.pubmedcentral.nih.gov/utils/idconv/v1.0/?ids=" . $pmid);
    $id = simplexml_load_string($idconv_response);

    $pmc_uid = substr($id->record["pmcid"],3);

    if ($pmc_uid !== "") {

        $oai_response = @file_get_contents("http://www.pubmedcentral.nih.gov/oai/oai.cgi?verb=GetRecord&metadataPrefix=pmc&identifier=oai:pubmedcentral.nih.gov:" . $pmc_uid);

        $front = simplexml_load_string($oai_response)->GetRecord->record->metadata->article;

        if (!is_null($front)) {

            $front->registerXPathNamespace('a', 'http://dtd.nlm.nih.gov/2.0/xsd/archivearticle');
            $front->registerXPathNamespace('x', 'http://www.w3.org/1999/xlink');
            $front->registerXPathNamespace('o', 'http://www.openarchives.org/OAI/2.0/');

            $pdf = xpath($front, "/o:OAI-PMH/o:GetRecord/o:record/o:metadata/a:article/a:front/a:article-meta/a:self-uri/@xlink:href");

            if (endsWith($pdf, ".pdf")) {
                $pdf = "http://www.ncbi.nlm.nih.gov/pmc/articles/PMC" . $pmc_uid . "/pdf/" . $pdf;
            } else if (startsWith($pdf, "http://www.biomedcentral.com/")) {
                $pdf = "http://www.biomedcentral.com/content/pdf/" . substr($pdf, 29, 9)
                        . "-" . substr($pdf, 39, 2) . "-" . substr($pdf, 42, 2) . ".pdf";
            }

            return $pdf;
        } else {
            return "";
        }
    } else {
        return "";
    }
}

function endsWith($haystack, $needle) {
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function startsWith($haystack, $needle) {
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function writeRedirect($url, $linktext = "", $prefix = "", $postfix = "") {
    if (REDIRECT) {
        return "<a href=\"redirect.php?url=" . $prefix . urlencode($url) . $postfix . "\" rel=\"external\">" . $linktext;
    } else {
        return "<a href=\"" . $prefix . $url . $postfix . "\">" . $linktext;
    }
}
