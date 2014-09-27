<?php

$db = new SQLite3('./pubmed/pmc_db');

$db->exec('DROP TABLE pmc');

$db->exec('CREATE TABLE pmc (id varchar(25))');

$file = fopen("./pubmed/oa_articles.txt", "r");

while(!feof($file)){
    $line = fgets($file);
    $db->exec('INSERT INTO pmc (id) VALUES ("' . trim($line) . '")');
}

fclose($file);