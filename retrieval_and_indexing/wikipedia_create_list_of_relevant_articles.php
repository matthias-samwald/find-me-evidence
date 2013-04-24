<?php
/* This script creates a list of Wikipedia articles belonging to the 'Pharmacology' 
 * and 'Medicine' Wikipedia projects. It uses the Wikipedia toolserver.
 * Filtering results according to importance and quality would be possible, 
 * but is not used at the moment.
 * The resulting list of Wikipedia articles can be used in targeted medical informations systems.
 * 
 * See
 * http://en.wikipedia.org/wiki/Wikipedia:WikiProject_Medicine/Assessment
 * http://en.wikipedia.org/wiki/Wikipedia:WikiProject_Pharmacology/Assessment
 * 
 * Matthias Samwald, March 2013, samwald (at) gmx.at
 */

function filter_urls($url) {
	if((substr($url, 0, 4) == "Talk") 
			or (strpos($url, "action=history") !== false) 
			or (strpos($url, "%3A") !== false)
			or (strpos($url, "List%20of") !== false))
		return false;
	else
		return true;
}

/**
 * Get response from Wikipedia toolserver. 
 * It lists up to 1000 articles (starting from a given offset) 
 * for a given project.
 */
function get_toolserver_response($offset, $project) {
	$response = file_get_contents("http://toolserver.org/~enwp10/bin/list2.fcgi?run=yes&projecta=" . $project . 
			"&namespace=&pagename=&quality=&importance=&score=&limit=1000&offset=" . $offset . 
			"&sorta=Importance&sortb=Quality");
	preg_match_all("/\"http\:\/\/en\.wikipedia\.org\/w\/index\.php\?title\=([^\"]+)/", $response, $matches);
	$matches = $matches[1];
	$matches = array_filter($matches, "filter_urls");
	
	$matches_returned = Array();
	foreach ($matches as $match) {
		$matches_returned[] = urldecode(str_replace("%20", "_", $match));
	}
	return $matches_returned;
}

$article_labels = Array();

// Get article labels belonging to 'Pharmacology' Wikipedia project
for ($i = 0; $i < 9000; $i += 1000) {
	$article_labels = array_merge($article_labels, get_toolserver_response($i, "Pharmacology"));
	var_dump($article_labels);
	sleep(2);
}

// Get article labels belonging to 'Medicine' Wikipedia project
for ($i = 0; $i < 31000; $i += 1000) {
	$article_labels = array_merge($article_labels, get_toolserver_response($i, "Medicine"));
	sleep(2);
}

// Remove duplicates
$article_labels_unique = array_unique($article_labels);

// Write article labels to a textfile
$output_file_content = implode($article_labels_unique, "\n");
file_put_contents("./wikipedia/relevant_articles.txt", $output_file_content);

/*
$responses =. get_toolserver_response(1, "Pharmacology");
$responses =. get_toolserver_response(1001, "Pharmacology");
$responses =. get_toolserver_response(2001, "Pharmacology");
$responses =. get_toolserver_response(3001, "Pharmacology");
$responses =. get_toolserver_response(4001, "Pharmacology");
$responses =. get_toolserver_response(5001, "Pharmacology");
$responses =. get_toolserver_response(6001, "Pharmacology");
$responses =. get_toolserver_response(7001, "Pharmacology");
$responses =. get_toolserver_response(8001, "Pharmacology");

*/

?>