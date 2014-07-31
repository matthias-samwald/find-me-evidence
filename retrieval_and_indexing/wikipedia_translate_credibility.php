<?php

/*
 * 
 * 2.3. Yandex reserves the right to set any limits and restrictions, including but not limited to those stated below:
 * the number of references to the Service: 10,000 references per day;
 * the volume of the text translated: 1,000,000 characters per day.
 */

include('./config.php');

$filename = "translated_relevant_articles_credibility.txt";
$translations = array();
$count = 0;
$count_translations = 0;
$yandex_limit_reached = false;

if (($handle = fopen('./wikipedia/' . $filename, 'r')) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {

        if ($row[2] == "") {

            if ($yandex_limit_reached) {
                //empty translation
                array_push($translations, $row[0] . ";" . $row[1] . ";");
                echo ++$count . " Yandex limit reached\n";
            } else {
                $translation = @file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/translate?key=" . YANDEX_KEY . "&lang=en-de&text=" . urlencode($row[0]));
                if ($translation) {
                    $translation = json_decode($translation, true);
                    $translation = $translation["text"][0];
                    //yandex translation
                    array_push($translations, $row[0] . ";" . $row[1] . ";" . $translation);
                    echo ++$count . " # of Yandex translations: " . ++$count_translations . "\n";
                } else {
                    $yandex_limit_reached = true;
                    //empty translation
                    array_push($translations, $row[0] . ";" . $row[1] . ";");
                    echo ++$count . " Yandex limit reached\n";
                }
            }
        } else {
            //translation already exists
            array_push($translations, $row[0] . ";" . $row[1] . ";" . $row[2]);
            echo ++$count . " translation already exists\n";
        }
    }
    fclose($handle);
}

echo $countTranslations . " already translated\n";

$output_file_content = implode($translations, "\n");
//overwrite input file
file_put_contents("./wikipedia/" . $filename, $output_file_content);