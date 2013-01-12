<?php 

define(SOLR_URL, "http://localhost:8888/solr/collection2000/get?");

function get_solr ($id) {
	$request_url = SOLR_URL . "id=" . urlencode($id);
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

$xml = get_solr($_GET["id"]);

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
    <h3>Bricoleur prototype</h3>
    <a href="../nav.html" data-icon="info" data-iconpos="notext" data-rel="dialog" data-transition="fade">Help</a> </div>
  <div data-role="content">
  <h1><?php print xpath($xml, "doc/arr[@name='title']/str"); ?></h1>
  <p><span class="data_source_name"><?php print xpath($xml, "doc/str[@name='data_source_name']"); ?></span> 
	            - <span><?php print substr(xpath($xml, "doc/date[@name='dateCreated']"), 0, 10) ?></span></p>
    <p><?php print xpath($xml, "doc/arr[@name='body']/str"); ?></p>
    <p><a href="<?php $id = xpath($xml, "doc/str[@name='id']"); print $id; ?>"><?php print $id; ?></a></p>
  <p><a href="index.php" data-role="button" data-icon="back" data-rel="back" >Go back</a> 
  </div>
  <div data-role="footer">
    <h4>Go back</h4>
  </div>
</div>