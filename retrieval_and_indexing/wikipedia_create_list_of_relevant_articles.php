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

require_once './simple_html_dom.php';

$article_labels = Array();
$limit = 200;
$tr_count = 0;

// Get article labels belonging to 'Pharmacology' Wikipedia project
$i = 1;
do {
    $articles = get_toolserver_response($i, "Pharmacology");
    $article_labels = array_merge($article_labels, $articles);
    $i += $limit;
} while ($tr_count != 1);

// Get article labels belonging to 'Medicine' Wikipedia project
$i = 1;
do {
    $articles = get_toolserver_response($i, "Medicine");
    $article_labels = array_merge($article_labels, $articles);
    $i += $limit;
} while ($tr_count != 1);

// Remove duplicates
$article_labels_unique = array_unique($article_labels);

$output_file_content = implode($article_labels_unique, "\n");
file_put_contents("./wikipedia/relevant_articles_credibility.txt", $output_file_content);

echo count($article_labels) - count($article_labels_unique) . " dulplicate(s)\n";
echo count($article_labels_unique) . " total\n";

/**
 * returns list of articles (array)
 * @param type $offset results per page
 * @param type $project project name e.g. Pharmacology
 * @return string
 */
function get_toolserver_response($offset, $project) {
    $url = "http://tools.wmflabs.org/enwp10/cgi-bin/list2.fcgi?run=yes&projecta=" . $project .
            "&namespace=&pagename=&quality=&importance=&score=&limit=" . $GLOBALS["limit"] . "&offset=" . $offset .
            "&sorta=Importance&sortb=Quality";

    $matches_returned = Array();

    $html = file_get_html($url);

    $GLOBALS["tr_count"] = 0;

    foreach ($html->find('tr') as $element) {

        $GLOBALS["tr_count"] ++;

        //echo $element . "--------------------------\n";
        preg_match("/\"http\:\/\/en\.wikipedia\.org\/w\/index\.php\?title\=([^\"]+)/", $element, $matches);
        if (isset($matches[1]) && filter_articles($matches[1])) {

            $match = urldecode(str_replace("%20", "_", $matches[1]));
//            echo $match . ", ";


            if (strpos($match, ';') !== false) {
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
    echo count($matches_returned) . ": " . $url . "\n";
    return $matches_returned;
}

/**
 * returns false for article with ':' or 'List_of' in its name or if it is an 
 * empty string or if it starts with "ATC_code_" or "ATCvet_code_", returns false, 
 * otherwise returns true
 * @param type $article name of article
 * @return boolean
 */
function filter_articles($article) {
    //contains
    if ((strpos($article, "%3A") !== false)
            or ( strpos($article, "List%20of") !== false)
            or ( strcmp("", $article) === 0)
            //startswith
            or ( strpos($article, "ATC%20code%20") === 0)
            or ( strpos($article, "ATCvet%20code%20") === 0)) {
        return false;
    } else {
        return true;
    }
}
