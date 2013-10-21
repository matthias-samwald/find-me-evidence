<?php
include_once ('config.php');
function query_solr($q, $category, $rows, $offset = 0) {
	global $categories;
	
	// TODO: Switch to http_build_query for constructing URL parameters
	
	if ($category == "PubMed by date")
		$sort = "by_date";
	else
		$sort = "by_relevance";
	
	$q = str_replace(":", " ", $q); // Remove double-colons in query (could be interpreted as field names)
	
	$request_url = SOLR_URL . "/select?q=" . urlencode ( $q );
	if ($category != "all") {
		$request_url .= "&fq=" . urlencode ( "{!tag=tagA}category:\"$categories[$category]\"" ); // select facet
	}
	$request_url .= 
	"&bq=" . urlencode ( 'data_source_name:"PubMed: Cochrane Database Syst Rev"' ) . 
	"&defType=edismax" . 	// select query parser
	"&q.op=AND" . 	// default query operator
	//"&bf=ord(dataset_priority)^4" .
	"&boost=dataset_priority" . // boost results by dataset priority (only works with edismax query parser)
	"&qf=title^2%20key_assertion^2%20text_all" . 	// fields to be queried (can include boosts)
	"&pf=title^2%20key_assertion%20body" . 	// enable automated phrase-matching (boosting fields and setting slop per-field would also be possible here)
	"&ps=2" . 	// default slop for automated phrase-matching
	"&fl=id,title,data_source_name,dateCreated,key_assertion" . 	// only these fields will be listed in the response
	"&start=" . $offset . 	// offset for paginated results
	"&rows=" . $rows . 	// select number of results returned
	"&wt=xml" .	// select result format
	"&facet=true" . 	// switch faceting on
	"&facet.query=" . urlencode ( $q ) . 	// query used for generating faceting counts ... maybe this should be prefixed with {!edismax} or whatever query parser is used, to match results shown to the user? See file:///C:/Users/m/Downloads/SimpleFacetParameters%20-%20Solr%20Wiki.htm
	"&facet.field=" . urlencode ( "{!ex=tagA}category" ) . 	// use this field for faceting
	"&hl=true" . 	// switch highlighting on
	"&hl.fl=body" . 	// use this field for highlighting
	// "&hl.snippets=2" . 	// set maximum number of snippets per field generated. Default is 1.
	"&hl.fragsize=380" . 	// set size of a snippet (in characters)
	// "&hl.fragmenter=regex" . "&hl.regex.slop=0.6" .	// specifies the factor by which the regex fragmenter can stray from the ideal fragment size
	// "&hl.regex.pattern=\w[^\.!\?]{100,500}[\.!\?]" . 	// regex for matching sentences ... does not work too well? TODO: Should also be modified not to match commas in numbers, but only commas followed by a whitespace
	"&hl.mergeContiguous=true" . 	// merge the two snippets into one when they are contiguous
	"&spellcheck=true" . 	// switch spellchecking on
	                     // "&spellcheck.maxResultsForSuggest=5" . // provide spelling suggestions if fewer than n results are returned, by default suggestions are made when there are 0 results
	"&spellcheck.collate=true" . 	// construct a new query for multi token corrections
	"&spellcheck.maxCollationTries=8"; // how many collations to try before giving up making a suggestion

	// Debug (enable/disable by uncommenting/commenting)
	$request_url .= "&debug=true";
	
	
	if ($sort == "by_date") {
		$request_url .= "&sort=dateCreated+desc";
	}
	
	print "<!--- " . $request_url . " -->";
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
	$result_array = $xml->xpath ( $xpath_expression );
	if ($return_entire_array == false) {
		return $result_array [0];
	} else {
		return $result_array;
	}
}
function get_facet_count($xml, $category) {
	global $categories;
	return xpath ( $xml, "//lst[@name='facet_counts']/lst[@name='facet_fields']/lst[@name='category']/int[@name='$categories[$category]']" );
}
?>