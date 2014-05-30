<?php

$filename="relevant_articles.txt";
$article_list_file_contents = file_get_contents("./wikipedia/" . $filename);
$article_labels = explode("\n", $article_list_file_contents);

$translations = array();

$count = 0;

foreach ($article_labels as $label) {
    $title = str_replace("_", " ", $label);
    $langlinks = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&titles=" . urlencode($title) . "&prop=langlinks&format=xml");
    $xml = simplexml_load_string($langlinks);
    $de = xpath($xml, "/api/query/pages/page/langlinks/ll[@lang='de']/text()");
    
    array_push($translations, $title . "; " . $de);
    echo ++$count . ": " . $title . "; " . utf8_decode($de) . "\n";
}

$output_file_content = implode($translations, "\n");
file_put_contents("./wikipedia/translated_direct_".$filename, $output_file_content);

function xpath($xml, $xpath_expression, $return_entire_array = false) {
    $result_array = $xml->xpath($xpath_expression);
    if ($return_entire_array == false) {
        return $result_array [0];
    } else {
        return $result_array;
    }
}
