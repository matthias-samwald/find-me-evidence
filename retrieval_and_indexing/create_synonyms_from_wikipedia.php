<?php

/* Creates a directed synonym mapping based on Wikipedia page redirects
*  Output is a file in Solr synonym syntax, according to http://wiki.apache.org/solr/AnalyzersTokenizersTokenFilters#solr.SynonymFilterFactory
*  Content should be used for replacing the synonyms.txt file found in the $LWE_HOME/conf/solr/cores/collection/conf directory (where collection 
*  is the collection name the synonyms should be used for).
*/  

// Load list of article labels into an array
$article_list_file_contents = file_get_contents("./wikipedia/relevant_articles.txt");
$article_ids = explode( "\n", $article_list_file_contents);

$output_file = "./synonyms/synonyms_from_wikipedia.txt";
file_put_contents($output_file, ""); // Flush file

function label_should_not_be_used($label) {
	// Return true if label contains colons, brackets, the string "ATC" et cetera (examples: "Atypical medications (antipsychotics)", "ATCvet code QD08AK06")
	if (strpos($label,',') !== false) return true;
	if (strpos($label,'(') !== false) return true;
	if (strpos($label,')') !== false) return true;
	if (strpos($label,':') !== false) return true;
	elseif (strpos($label,'ATC') !== false) return true;
	else return false;
}

function mapping_should_not_be_used($label_1, $label_2) {
	// Return true if mapping is mereley between singular-plural or different use of uppercase/lowercase
	$label_1 = strtolower($label_1);
	$label_2 = strtolower($label_2);
	if ($label_1 == $label_2) return true;
	elseif ($label_1 . 's' == $label_2) return true;
	elseif ($label_1 == $label_2 . 's') return true;
	else return false;
}

$i = 0;
foreach ($article_ids as $article_id) {
	if (label_should_not_be_used($article_id)) continue;
	
	$query = "
	PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
	PREFIX resource: <http://dbpedia.org/resource/>
	SELECT ?synonym_label WHERE {
		?synonym <http://dbpedia.org/ontology/wikiPageRedirects> resource:$article_id .
		?synonym rdfs:label ?synonym_label .
	}
	LIMIT 100
	";
	
	$response = file_get_contents("http://dbpedia.org/sparql?query=" . urlencode($query) . "&format=" . urlencode("text/csv"));
	$synonyms = explode("\n", trim($response));
	array_shift($synonyms); // Remove first element (the column title)
	
	foreach ($synonyms as $synonym) {
		if (label_should_not_be_used($synonym)) continue;
		if (mapping_should_not_be_used($article_id, $synonym)) continue;
		
		$output = trim($synonym, '",') . " => " . trim($synonym, '",') .", " . urldecode(str_replace("_", " ", $article_id)) . "\n\n";  // TODO: this was changed after the last run, make sure it works correctly!
		file_put_contents($output_file, $output, FILE_APPEND);
	}
	sleep(0.8);
	print($i++ . "\n");
}
?>