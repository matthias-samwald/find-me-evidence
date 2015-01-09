<?php

require_once('./lib/http_post/http_post.php');

include('./config.php');

$count = 0;

index("es_de_translated_relevant_articles_credibility.txt", 2, 3);

index("new_medical_terms.txt", 1, 2);

function index($translationfile, $german_column, $spanish_column) {

    if (($handle = fopen('./wikipedia/' . $translationfile, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {

            $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><update><add><doc>\n";
            $output .= "<field name='id'>" . htmlspecialchars($row[0]) . "</field>\n";
            $output .= "<field name='title'>" . htmlspecialchars($row[0]) . "</field>\n";
            $output .= "<field name='german'>" . htmlspecialchars($row[$german_column]) . "</field>\n";
            $output .= "<field name='spanish'>" . htmlspecialchars($row[$spanish_column]) . "</field>\n";
            $output .= "</doc></add></update>";

            echo ++ $GLOBALS["count"]. "\n";

            do_post_request(SOLR_URL_DIC . '/update', $output);
            
        }
        fclose($handle);
    }

    print do_post_request(SOLR_URL_DIC . '/update', "<?xml version=\"1.0\" encoding=\"UTF-8\"?><update><commit/><optimize/></update>");
}
