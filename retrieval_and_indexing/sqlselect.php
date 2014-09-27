<?php

$db = new SQLite3('./pubmed/pmc_db');

$result = $db->querySingle('SELECT count(id) FROM pmc');

var_dump($result);

$result = $db->querySingle('SELECT id FROM pmc where id = "PMC4169279"');

var_dump($result);

$result = $db->querySingle('SELECT id FROM pmc');

var_dump($result);