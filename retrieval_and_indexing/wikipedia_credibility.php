<?php

require_once './simple_html_dom.php';

$article_labels = Array();

$limit = 300;

// Get article labels belonging to 'Medicine' Wikipedia project
for ($i = 1; $i < 12000; $i += $limit) {
    $article_labels = array_merge($article_labels, get_toolserver_response($i, "Medicine"));
    sleep(2);
}

//// Get article labels belonging to 'Pharmacology' Wikipedia project
//for ($i = 1; $i < 10000; $i += $limit) {
//	$article_labels = array_merge($article_labels, get_toolserver_response($i, "Pharmacology"));
//	sleep(2);
//}

// Remove duplicates
$article_labels_unique = array_unique($article_labels);

echo count($article_labels) - count($article_labels_unique) . " dulplicate(s)\n";
echo count($article_labels_unique) . " total\n";

function get_toolserver_response($offset, $project) {
    $url = "http://tools.wmflabs.org/enwp10/cgi-bin/list2.fcgi?run=yes&projecta=" . $project .
            "&namespace=&pagename=&quality=&importance=&score=&limit=" . $GLOBALS["limit"] . "&offset=" . $offset .
            "&sorta=Importance&sortb=Quality";
    echo $url . "\n";

    $matches_returned = Array();

    $html = file_get_html($url);

    foreach ($html->find('tr') as $element) {
        //echo $element . "--------------------------\n";
        preg_match("/\"http\:\/\/en\.wikipedia\.org\/w\/index\.php\?title\=([^\"]+)/", $element, $matches);
        if (strcmp("", $matches[1]) != 0) {
            $match = urldecode(str_replace("%20", "_", $matches[1]));
//            echo $match . ", ";
            
            if (strpos($match, ';') !== false){
                echo $match . "\n";
            }

            $quality = $element->find('b', 1)->plaintext;

            //.$element."\n";
//            echo "Importance: " . $element->find('b', 0)->plaintext . ", ";
//            echo $quality . "\n";
//            echo "Review Release: " . $element->find('b', 2)->plaintext . "\n";

            $matches_returned[] = $match . ";" . $quality;
        }
    }

    return $matches_returned;
}

?>
