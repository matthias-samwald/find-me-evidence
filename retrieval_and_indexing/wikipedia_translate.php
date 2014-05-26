<?php

include('./config.php');

$filename="relevant_articles.txt";
$article_list_file_contents = file_get_contents("./wikipedia/".$filename);
$article_labels = explode("\n", $article_list_file_contents);

$translations = array();

$count = 0;

foreach($article_labels as $label) {
    $title = str_replace("_", " ", $label);
    $translation = file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/translate?key=".YANDEX_KEY."&lang=en-de&text=".  urlencode($title));
                        $translation = json_decode($translation, true);
                        $translation = $translation["text"][0];
    array_push($translations, $title."; ".$translation);
    echo ++$count;
}

$output_file_content = implode($translations, "\n");
file_put_contents("./wikipedia/translated_".$filename, $output_file_content);