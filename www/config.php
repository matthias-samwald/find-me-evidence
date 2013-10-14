<?php

define('SOLR_URL', "http://localhost:8080/solr-4.4.0/collection1");
// define ( 'SOLR_URL', "http://54.228.245.189:8888/solr/collection1" );
$max_rows = 20;

// List of categories, containg a mapping of "Category for display"=>"Category for filtering in Solr"
$categories = Array (
		"Evidence-based summary" => "Evidence-based summary",
		"PubMed by date" => "Pubmed",
		"PubMed by relevance" => "Pubmed",
		"Drug information" => "Drug information",
		"Professional discussions" => "Professional discussions",
		"Wikipedia" => "Wikipedia" 
);
?>