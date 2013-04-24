<?php

// Load list of article labels into an array
$article_list_file_contents = file_get_contents("./wikipedia/relevant_articles.txt");
$article_labels = explode("\n", $article_list_file_contents);

// Code below to be replaced by approach based on single POST request to http://en.wikipedia.org/wiki/Special:Export/



/*
// Iterate through article labels, fetch wiki code via Wikipedia API (via URLs such as http://en.wikipedia.org/wiki/Special:Export/Diabetes_insipidus)
foreach($article_labels as $label) {
	$response_xml = file_get_contents("http://en.wikipedia.org/wiki/Special:Export/" . $label);
	file_put_contents("./wikipedia/" . $label . ".xml" . $response_xml);
	sleep(1.5);
}


$url = 'http://server.com/path';
$data = array('key1' => 'value1', 'key2' => 'value2');

// use key 'http' even if you send the request to https://...
$options = array('http' => array('method'  => 'POST','content' => http_build_query($data)));
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

*/

	// Get plain text / HTML rendering
	
	// Expand local abbreviations

	// Create Solr XML for article



?>