<?php

define(SOLR_URL, "http://localhost:8888/solr/collection2000/select?");
//define(SOLR_URL, "http://54.228.245.189:8888/solr/collection1/select?");
define(MAX_NUMBER_OF_RESULTS_PER_REQUEST, 50);
$categories = Array("Evidence-based summary", "Scientific articles", "Drug information", "Professional discussions", "Wikipedia");


function query_solr($q, $category, $sort, $rows = MAX_NUMBER_OF_RESULTS_PER_REQUEST, $offset = 0) {
	$request_url = SOLR_URL . "q=" . urlencode($q);
	if ($category != "all") {
		$request_url .= urlencode(" category:\"$category\""); // select facet
	}
	$request_url .=
		"&bq=" . urlencode("data_source_name:\"Cochrane Database Syst Rev PubMed\"^0.5") .
		"&defType=edismax" . // select query parser
		//"&bf=ord(dataset_priority)^0.5" . 
		//"&boost=dataset_priority" . // boost results by dataset priority (only works with edismax query parser)
		"&rows=" . urlencode($rows) . // select number of results returned
		"&wt=xml" . // select result format
		"&facet=true" . // switch faceting on
		"&facet.field=category" . // use this field for faceting
		"&hl=true" . // switch highlighting on
		"&hl.fl=body" . // use this field for highlighting
		"&hl.snippets=2" . // set maximum number of snippets per field generated 
		"&hl.fragsize=200" . // set size of a snippet (in characters)
		"&hl.mergeContiguous=true";  // merge the two snippets into one when they are contiguous
	if ($sort == "by_date") {
		$request_url .= "&sort=dateCreated+desc";
	}
	
	 print "<!-- Solr query: $request_url -->";

	$response = file_get_contents($request_url);
	$xml = simplexml_load_string($response);
	return $xml;
}

function xpath($xml, $xpath_expression, $return_entire_array = false) {
	$result_array = $xml->xpath($xpath_expression); 
	if ($return_entire_array == false) {
		return $result_array[0];
	}
	else {
		return $result_array;
	}
}

function get_facet_count($xml, $facet_name) {
	return xpath($xml, "//lst[@name='facet_counts']/lst[@name='facet_fields']/lst[@name='category']/int[@name='$facet_name']");
}
 
if (isset($_GET["q"]) AND q != "") {
	$category = $_GET["category"];
	if ($category == "") $category = "all"; // set default value if missing

	$sort = $_GET["sort"];
	if ($sort == "") $sort = "by_relevance"; // set default value if missing
	
	$xml = query_solr($_GET["q"], $category, $sort);
}


?>


<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Bricoleur search prototype</title>
<link href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" rel="stylesheet" type="text/css"/>
<script src="http://code.jquery.com/jquery-1.8.2.min.js" type="text/javascript"></script>
<script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js" type="text/javascript"></script>
<link href="bricoleur.css" rel="stylesheet" type="text/css">
</head>
<body>
<div data-role="page" id="main" data-theme="d">
  <div data-role="header">
    <h1>Bricoleur prototype</h1>
    <a href="../nav.html" data-icon="info" data-iconpos="notext" data-rel="dialog" data-transition="fade">Help</a> </div>
  <div data-role="content">
    <div style="margin: 20px; padding: 10px" > 
      <?php if (isset($_GET["q"]) AND q != "" AND ($xml->result["numFound"] > 0)) : // if a query was entered and results were found ?>
	      <form action="index.php" method="get">
		      <!--<label for="search-input">Search input:</label>-->
		      <input type="search" name="q" id="q" data-theme="e" value="<?php print htmlspecialchars(urldecode($_GET["q"]))?>" />
		      <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
		        <select name="category" id="category">
		          <option value="all" <?php if($_GET["category"] == "all") print('selected="selected"') ?>>Show everything (<?php print($xml->result["numFound"])?>)</option>
		          <?php foreach($categories as $category) {
		          			print("<option value=\"$category\"");
		          			if($_GET["category"] == $category) {
		          				print('selected="selected"');
		          			}
		          			print(">");
		          			print($category . " (" . get_facet_count($xml, $category) . ")</option>");
		          		}
		          ?>
		        </select>
		        <select name="sort" id="sort">
		          <option value="by_relevance" <?php if($_GET["sort"] == "by_relevance") print('selected="selected"') ?>>by relevance</option>
		          <option value="by_date" <?php if($_GET["sort"] == "by_date") print('selected="selected"') ?>>by date</option>
		        </select>
		      </fieldset>
		    </form>
	    </div>
	    <div>
	      <ul data-role="listview" data-inset="false">
	      
	        <!-- Iterate through documents in result set -->
	        <?php foreach($xml->result->doc as $doc): 
	        	$id = xpath($doc, "str[@name='id']")?>
		        <li><a href="<?php if(substr($id, 0, 35) == "http://www.ncbi.nlm.nih.gov/pubmed/") print ("show.php?id=" . urlencode($id));
		        				   else print($id); ?>">
		          <h3><?php print xpath($doc, "arr[@name='title']/str"); ?></h3>
		          <p><span class="data_source_name"><?php print xpath($doc, "str[@name='data_source_name']"); ?></span> 
		              <span class="publication_date"><?php $date_created = substr(xpath($doc, "date[@name='dateCreated']"), 0, 10); 
		              									if ($date_created != "") print("&nbsp;|&nbsp;" . $date_created) ?>
		              </span></p>
		          <?php if(xpath($doc, "arr[@name='key_assertion']/str")): ?>
		             <p class="conclusion"><?php print xpath($doc, "arr[@name='key_assertion']/str")?></p>
		          <?php elseif($snippets = xpath($doc, "//lst[@name='highlighting']/lst[@name='${id}']/arr[@name='body']/str", true)): ?>
		             <p class="text_snippet"><?php print("... " . implode(" ... ", $snippets) . " ..."); ?>
		             </p>
		          <?php endif; ?>
		        </a></li>
	        <?php endforeach; ?>
	      </ul>
	    </div>
	    
	 <?php elseif (isset($_GET["q"]) AND q != "" AND ($xml->result["numFound"] == 0)) : // if a query was entered and no results were found?>
	 	<form action="index.php" method="get">
		      <!--<label for="search-input">Search input:</label>-->
		      <input type="search" name="q" id="q" data-theme="e" value="<?php print htmlspecialchars(urldecode($_GET["q"]))?>" /> 
		</form>
		<p>No results found, please refine your query.</p>
		
	<?php elseif (isset($_GET["q"]) == false OR q == "") : // if no query was entered, default startup search bar ?>
		 	<form action="index.php" method="get">
		      <!--<label for="search-input">Search input:</label>-->
		      <input type="search" name="q" id="q" data-theme="e" value="<?php print htmlspecialchars(urldecode($_GET["q"]))?>" /> 
		</form>
		<p>Welcome to the Bricoleur search prototype, a medical search engine for rapidly reviewing current medical evidence. Please enter a search query.</p>
	<?php endif; ?>
	
  <?php if ($xml->result["numFound"] > MAX_NUMBER_OF_RESULTS_PER_REQUEST) print "<p>Only the first " . MAX_NUMBER_OF_RESULTS_PER_REQUEST . " results are shown.</p>" ?>	
  </div>
  <div data-role="footer"><h4>This prototype is intended for research use only and should not be used to guide medical treatment.</h4></div>
</div>
</body>
</html>