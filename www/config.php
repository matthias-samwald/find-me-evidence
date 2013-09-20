<?php

//define(SOLR_URL, "http://localhost:8888/solr/collection1");
define('SOLR_URL', "http://54.228.245.189:8888/solr/collection1");
$max_number_of_results_per_request = 30;

// List of categories, containg a mapping of "Category for display"=>"Category for filtering in Solr"
$categories = Array("Evidence-based summary"=>"Evidence-based summary", 
		"PubMed by date"=>"PubMed", 
		"PubMed by relevance"=>"PubMed", 
		"Drug information"=>"Drug information", 
		"Professional discussions"=>"Professional discussions", 
		"Wikipedia"=>"Wikipedia"); 
?>