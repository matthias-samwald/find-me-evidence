<?php

$oa_articles_duplicates = explode("\n", file_get_contents("./pubmed/oa_articles.txt"));

$oa_articles_unique = array_unique($oa_articles_duplicates);
sort($oa_articles_unique);

$output_file_content = implode($oa_articles_unique, "\n");
file_put_contents("./pubmed/oa_articles.txt", $output_file_content);

echo sizeof($oa_articles_duplicates) - sizeof($oa_articles_unique) . " duplicates found and removed\n";