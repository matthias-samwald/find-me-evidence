<?php

require_once './simple_html_dom.php';

function filter_urls($url) {
    if ((substr($url, 0, 4) == "Talk")
            or ( strpos($url, "action=history") !== false)
            or ( strpos($url, "%3A") !== false)
            or ( strpos($url, "List%20of") !== false)) {
        return false;
    } else {
        return true;
    }
}

$project = "Medicine";
$offset = 2;
$url = "http://tools.wmflabs.org/enwp10/cgi-bin/list2.fcgi?run=yes&projecta=" . $project .
        "&namespace=&pagename=&quality=&importance=&score=&limit=2&offset=" . $offset .
        "&sorta=Importance&sortb=Quality";
echo $url . "\n";
$response = file_get_contents($url);
echo $response."\n";

$html = file_get_html($url);
foreach ($html->find('tr') as $element) {
    //echo $element . "--------------------------\n";
    preg_match("/\"http\:\/\/en\.wikipedia\.org\/w\/index\.php\?title\=([^\"]+)/", $element, $matches);
    if (strcmp("", $matches[1]) != 0) {
        $match = urldecode(str_replace("%20", "_", $matches[1]));
        echo $match . ", ";
        //.$element."\n";
        echo "Importance: " . $element->find('b', 0)->plaintext . ", ";
        echo "Quality: " . $element->find('b', 1)->plaintext . ", ";
        echo "Review Release: " . $element->find('b', 2)->plaintext . "\n";
    }
}

preg_match_all("/\"http\:\/\/en\.wikipedia\.org\/w\/index\.php\?title\=([^\"]+)/", $response, $matches);
$matches = $matches[1];
$matches = array_filter($matches, "filter_urls");

$matches_returned = Array();
foreach ($matches as $match) {
    $matches_returned[] = urldecode(str_replace("%20", "_", $match));
    foreach ($matches_returned as $match_returned) {
        echo $match_returned . "\n";
    }
}
?>
