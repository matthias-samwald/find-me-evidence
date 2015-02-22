<?php

require_once('./lib/http_post/http_post.php');

include('./config.php');

$count = 0;
$output = "";

index("es_de_translated_relevant_articles_credibility.txt", 2, 3);

index("new_medical_terms.txt", 1, 2);

function index($translationfile, $german_column, $spanish_column) {

    global $output, $count;
    $remainder = -1;

    if (($handle = fopen('./wikipedia/' . $translationfile, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {

            $remainder = ++$count % 1000;
            
            $english = removeBracketsAndContent($row[0]);
            $german = removeBracketsAndContent($row[$german_column]);
            $spanish = removeBracketsAndContent($row[$spanish_column]);

            $output .= "<doc><field name='id'>" . htmlspecialchars($english) . "</field>\n";
            $output .= "<field name='title'>" . htmlspecialchars($english) . "</field>\n";
            $output .= "<field name='german'>" . htmlspecialchars($german) . "</field>\n";
            $output .= "<field name='spanish'>" . htmlspecialchars($spanish) . "</field></doc>\n";

            if ($remainder == 0) {
                update($output);
            }
        }
        if ($remainder > 0) {
            update($output);
        }
        $count = 0;
        fclose($handle);
    }

    print do_post_request(SOLR_URL_DIC . '/update', "<?xml version=\"1.0\" encoding=\"UTF-8\"?><update><commit/><optimize/></update>");
}

function removeBracketsAndContent($term) {
    return preg_replace("/\(.*\)/u", "", $term);
}

function update($docs) {

    global $output, $count;

    $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><update><add>\n" .
            $docs .
            "</add></update>";
    do_post_request(SOLR_URL_DIC . '/update', $output);
    $output = "";
    echo $count . "\n";
}