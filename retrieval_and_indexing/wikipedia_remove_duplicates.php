<?php

$article_ids = array();
if (($handle = fopen('./wikipedia/relevant_articles_credibility.txt', 'r')) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {
        $article_ids[] = $row;
    }
    fclose($handle);
}

$alreadyFoundTitle = array();
$article_ids_unique = array();

for ($i = 0; $i < count($article_ids); $i++) {
    if (!in_array($article_ids[$i][0], $alreadyFoundTitle)) {
        $alreadyFoundTitle[] = $article_ids[$i][0];
        $article_ids_unique[] = $article_ids[$i][0] . ";" . $article_ids[$i][1];
    }
}

$output_file_content = implode($article_ids_unique, "\n");
file_put_contents("./wikipedia/relevant_articles_credibility.txt", $output_file_content);

echo sizeof($article_ids) - sizeof($article_ids_unique) . " duplicates found and removed\n";