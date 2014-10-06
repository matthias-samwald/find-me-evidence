<?php

$db = new SQLite3('./pubmed/oai_db');

$result = $db->querySingle('SELECT count(id) FROM pubmed');

var_dump($result);

$result = $db->querySingle('SELECT id FROM pubmed where id = "11250746"');

var_dump($result);

$result = $db->querySingle('SELECT id FROM pubmed');

var_dump($result);
