<?php

include('./config.php');

$filename = "translated_relevant_articles.txt";
$translations = array();
$count = 0;
$count_translations = 0;
$max_yandex_translations = $argv[1];

if (($handle = fopen('./wikipedia/' . $filename, 'r')) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {


        if ($row[1] == "") {
            if ($count_translations < $max_yandex_translations) {
                $translation = file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/translate?key=" . YANDEX_KEY . "&lang=en-de&text=" . urlencode($row[0]));
                $translation = json_decode($translation, true);
                $translation = $translation["text"][0];
                //yandex translation
                array_push($translations, $row[0] . ";" . $translation);
                echo ++$count . " yandex (" . ++$count_translations . ")\n";
            } else {
                //empty translation
                array_push($translations, $row[0] . ";");
                echo ++$count . " no yandex \n";
            }
        } else {
            //translation already exists
            array_push($translations, $row[0] . ";" . $row[1]);
            echo ++$count . " translation already exists\n";
        }
    }
}

$output_file_content = implode($translations, "\n");
//overwrite input file
file_put_contents("./wikipedia/" . $filename, $output_file_content);
