<?php

include_once ('config.php');
include_once ('functions.php');

date_default_timezone_set('Europe/Vienna');

include_once('logger/Logger.php');
Logger::configure('config.xml');
$logger = Logger::getLogger("main");

$q = $_GET ["q"];
$l = $_GET ["l"];
$p = $_GET ["p"];
if ($q != "" and strlen($q) > 2) {

    $translation_info = "";

    if ($l == "ger") {
        $extracted_word = extractWord($q, $p);
        $request_url = SOLR_URL . "/select?q=";
        $request_url .= "" . urlencode($extracted_word)
                . "&sort=norm(german)+desc&wt=xml&df=german";
        //use the norm value to find the shortest field 
        //http://wiki.apache.org/solr/FunctionQuery#norm
        $response = @file_get_contents($request_url);
        if ($response !== FALSE) {
            $xml = simplexml_load_string($response);

            $title = xpath($xml, "/response/result/doc/arr[@name='title']/str/text()");
            $german = xpath($xml, "/response/result/doc/str[@name='german']/text()");

            $translation_info = str_replace($extracted_word, strtolower($title), $q);

            $logger->info("'" . $extracted_word . "' translated to '" . $title
                    . "' via '" . $german . "' (" . $translation_info . ") p:" . $p);
        } else {
            $logger->info("solr not available");
        }
    }

    $response = file_get_contents("http://preview.ncbi.nlm.nih.gov/portal/utils/autocomp.fcgi?dict=pm_related_queries_2&callback=?&q=" . urlencode($q));
    preg_match("/Array\(([^\)]+)/", $response, $response);
    $response = $response [1];
    $response = trim($response, '"');
    $response = explode('", "', $response);
    $response = array_slice($response, 0, 5);

    array_unshift($response, $translation_info);

    /*
     * Generate Mock data which can be used for debugging/testing sleep(1); $response = array("test" . rand(0, 1000), "test". rand(0, 1000), "test" . rand(0, 1000), "test". rand(0, 1000));
     */

    print json_encode($response);
}

function extractWord($text, $position) {
    $words = explode(' ', $text);
    $characters = -1;
    foreach ($words as $word) {
        $characters += strlen($word) + 1;
        if ($characters >= $position) {
            return $word;
        }
    }
    return '';
}

?>