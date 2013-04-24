<?php 

include_once('config.php');
include_once('functions.php');

$xml = get_solr($_GET["id"]);

$title = xpath($xml, "doc/arr[@name='title']/str");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bricoleur search prototype: <?php print $title ?></title>
<link href="js/jquery.mobile-1.3.0.min.css" rel="stylesheet"
	type="text/css" />
<script src="js/jquery-1.8.2.min.js" type="text/javascript"></script>
<script src="js/jquery.mobile-1.3.0.min.js" type="text/javascript"></script>
<link href="bricoleur.css" rel="stylesheet" type="text/css">
</head>
<body>
<div data-role="page" id="main" data-theme="d">
  <div data-role="header">
    <h3>Bricoleur prototype</h3>
    <a href="https://code.google.com/p/bricoleur-fast-medical-search/w/list" data-icon="info" data-iconpos="notext" data-rel="dialog" data-transition="fade">Help</a> </div>
  <div data-role="content">
  <h1><?php print $title ?></h1>
  <p><span class="data_source_name"><?php print xpath($xml, "doc/str[@name='data_source_name']"); ?></span> 
	            - <span><?php print substr(xpath($xml, "doc/date[@name='dateCreated']"), 0, 10) ?></span></p>
    <p class="abstract_text"><?php print xpath($xml, "doc/arr[@name='body']/str"); ?></p>
    <p><a href="<?php $id = xpath($xml, "doc/str[@name='id']"); print $id; ?>"><?php print $id; ?></a></p>
  <p><a href="index.php" data-role="button" data-icon="back" data-rel="back" >Go back</a></p>
  </div>
  <div data-role="footer">
    <h4>This prototype is intended for evaluation use only and should not be used to guide medical treatment.</h4>
  </div>
</div>