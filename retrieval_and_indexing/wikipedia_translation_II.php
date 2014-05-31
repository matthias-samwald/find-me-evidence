<?php

include('./config.php');

$filename = "translated_relevant_articles.txt";

$translations = array();

$count = 0;

if (($handle = fopen('./wikipedia/' . $filename, 'r')) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {

        if ($row[1] == " ") {
//            echo $row[0] . " is missing a translation\n";
            $translation = file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/translate?key=" . YANDEX_KEY . "&lang=en-de&text=" . urlencode($row[0]));
            $translation = json_decode($translation, true);
            $translation = $translation["text"][0];
            array_push($translations, $row[0] . "; " . $translation);
            echo ++$count . " - t\n";
        } else {
            array_push($translations, $row[0] . "; " . $row[1]);
            echo ++$count . "\n";
        }
    }
}

$output_file_content = implode($translations, "\n");
file_put_contents("./wikipedia/II_" . $filename, $output_file_content);