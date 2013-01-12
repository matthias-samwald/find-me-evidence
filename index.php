<?php

define(SOLR_URL, "http://localhost:8888/solr/collection2000/select?");
define(NUMBER_OF_RESULTS, 20);

function query_solr($q = "diabetes", $dataset = "all", $sort = "by_relevance", $rows = NUMBER_OF_RESULTS, $offset = 0) {
	
	$request_url = SOLR_URL . "q=" . urlencode($q) . "&rows=" . urlencode($rows) . 
		"&wt=xml" . 
		"&facet=true" . // switch faceting on
		"&facet.field=data_source_name" . // use this field for faceting TODO: change data_source_name to category
		"&hl=true" . // switch highlighting on
		"&hl.fl=body" . // use this field for highlighting
		"&hl.snippets=2" . // set maximum number of snippets per field generated 
		"&hl.fragsize=200" . // set size of a snippet (in characters)
		"&hl.mergeContiguous=true";  // merge the two snippets into one when they are contiguous
	if ($sort == "by_date") {
		$request_url .= "&sort=dateCreated+desc";
	}
	
	// print $request_url;

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

if (isset($_GET["q"]) AND q != "") {

}

$xml = query_solr($_GET["q"], $_GET["dataset"], $_GET["sort"]);

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
      <form action="index.php" method="get">
	      <!--<label for="search-input">Search input:</label>-->
	      <input type="search" name="q" id="q" data-theme="e" value="<?php print htmlspecialchars(urldecode($_GET["q"]))?>" />
	      <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
	        <select name="dataset" id="dataset">
	          <option value="all">Show everything (<?php print($xml->result["numFound"])?>)</option>
	          <option value="b">Evidence-based summaries (12)</option>
	          <option value="c">Scientific articles (1234)</option>
	          <option value="d">Wikipedia articles (1)</option>
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
	             <span><?php print substr(xpath($doc, "date[@name='dateCreated']"), 0, 10) ?></span></p>
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
  </div>
  <div data-role="footer">
    <h4>Show more results (1234 remaining)</h4>
  </div>
</div>
</body>
</html>