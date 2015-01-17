<?php

$db = new SQLite3('./pubmed/oa_db');

$db->exec('DROP TABLE pubmed');

$db->exec('CREATE TABLE pubmed (id varchar(25))');

$file = fopen("./pubmed/oa_articles.txt", "r");

$count = 0;

while (!feof($file)) {
    $line = fgets($file);
    if (!empty($line)) {
        $db->exec('INSERT INTO pubmed (id) VALUES ("' . trim($line) . '")');
        echo trim($line) . ": " . ++$count . "\n";
    }
}

fclose($file);

//$result = $db->querySingle('SELECT count(id) FROM pubmed');
//
//var_dump($result);
//
//$result = $db->querySingle('SELECT id FROM pubmed where id = "PMC139956"');
//
//var_dump($result);
//
//$result = $db->querySingle('SELECT id FROM pubmed');
//
//var_dump($result);