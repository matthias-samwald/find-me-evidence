<?php

$db = new SQLite3('./pubmed/oa_db');

$result = $db->querySingle('SELECT count(id) FROM pubmed');

var_dump($result);

$result = $db->querySingle('SELECT id FROM pubmed where id = "PMC139956"');

var_dump($result);

$result = $db->querySingle('SELECT id FROM pubmed');

var_dump($result);
