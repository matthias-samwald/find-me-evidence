<?php

require_once('./lib/http_post/http_post.php');

include('./config.php');

$count = 0;

if (($handle = fopen('./wikipedia/es_translated_relevant_articles_credibility.txt', 'r')) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {

        $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><update><add><doc>\n";
        $output .= "<field name='id'>" . $row[0] . "</field>\n";
        $output .= "<field name='title'>" . $row[0] . "</field>\n";
        $output .= "<field name='german'>" . $row[2] . "</field>\n";
        $output .= "<field name='spanish'>" . $row[3] . "</field>\n";
        $output .= "</doc></add></update>";

        do_post_request(SOLR_URL_DIC . '/update', $output);
        
        echo $count++ . "\n";
        
    }
    fclose($handle);
}

print do_post_request(SOLR_URL_DIC . '/update', "<?xml version=\"1.0\" encoding=\"UTF-8\"?><update><commit/><optimize/></update>");
