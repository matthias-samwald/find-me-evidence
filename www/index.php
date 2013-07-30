<?php

include_once('config.php');
include_once('functions.php');

if (isset($_GET["q"]) AND q != "") {
	$user_query = $_GET["q"];
		
	$category = $_GET["category"];
	if ($category == "") $category = "all"; // set default value if missing

	if ($category == "PubMed by date")
		$sort = "by_date"; 
	else
		$sort = "by_relevance"; 

	$startpos = $_GET["startpos"];
	if ($startpos == "") $startpos = 0; // set default value if missing

	$max_number_of_results_per_request = $_GET["quantity"];
	if ($max_number_of_results_per_request == "") $max_number_of_results_per_request = 30; // set default value if missing

	$xml = query_solr($user_query, $category, $sort, $startpos + $max_number_of_results_per_request);
	
	if ($xml->result["numFound"] == 0) {
		$corrected_query = xpath($xml, "//str[@name='collation']", false);
		if ($corrected_query != "") {
			print "<!-- Collation: $corrected_query -->";
			$xml = query_solr($corrected_query, $category, $sort, $startpos + $max_number_of_results_per_request); // re-run query with suggested collation
			$query_results_are_based_on_automatic_correction = true;
		}
	}
}

$page_title = "FindMeEvidence";
if ($user_query != "") {
	$page_title .= ": Search results for " . htmlspecialchars(urldecode($user_query)); 
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php print $page_title ?></title>
<link href="js/jquery.mobile-1.3.0.min.css" rel="stylesheet"
	type="text/css" />
<style>
.ui-btn-up-d a.ui-link-inherit:visited { color: red; }
</style>
<script src="js/jquery-1.8.2.min.js" type="text/javascript"></script>
<script src="js/jquery.mobile-1.3.0.min.js" type="text/javascript"></script>
<script>

	// Delay function, used for having a short delay after user typed something before initiating request to autocomplete service
	var delay = (function(){
		  var timer = 0;
		  return function(callback, ms){
		    clearTimeout (timer);
		    timer = setTimeout(callback, ms);
		  };
		})();

	// Escape HTML characters -- TODO: This does not seem to work (input not changed)
	function escapeHtml(text) {
		  return text
		      .replace("&", "&amp;")
		      .replace('"', "&quot;")
		      .replace("'", "&#039;");
		}

	// Update the autocomplete list
	function updateAutocomplete() {
        var $ul = $('#autocomplete'),
            $input = $('#q'),
            value = $input.val(),
            html = "";
        
        if ( value && value.length > 3 ) {
            //$ul.html( "<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>" );
       
            $.ajax({
            	url: "autocomplete.php",
				dataType: "json",
				crossDomain: false,
                data: {
                    q: $input.val()
                }
            })
            .then( function ( response ) {
            	$ul.html( "" );
            	$ul.listview( "refresh" );
                $.each( response, function ( i, val ) {
                	 html += '<li onclick=\'$("#q").val("' + escapeHtml(val) + '"); $("#search_form").submit();\'>' + val + '</li>';
                });
                $ul.html( html );
                $ul.listview( "refresh" );
                $ul.trigger( "updatelayout");
            });
        }	   
	}
</script>
<link href="bricoleur.css" rel="stylesheet" type="text/css">
</head>
<body>
	<div data-role="page" id="main" data-theme="d">
		<div data-role="header">
			<h3>FindMeEvidence</h3>
			<a href="https://code.google.com/p/bricoleur-fast-medical-search/w/list" data-icon="info" data-iconpos="notext"
				data-rel="dialog" data-transition="fade">Help</a>
		</div>
		<div data-role="content">
			<div style="padding-top: 20px; padding-bottom: 20px">
				<?php if ($user_query != "") : // if a query was entered ?>
				
				<!-- BEGIN: Search bar with existing results -->
				<form action="index.php" method="get" id="search_form"
					data-ajax="false">
					<!--<label for="search-input">Search input:</label>-->
					<input type="search" name="q" id="q" data-theme="e"
						autocomplete="off" onkeyup="delay(function(){updateAutocomplete();}, 300 );"
						value="<?php print htmlspecialchars(urldecode($user_query))?>" />
					<ul id="autocomplete" data-role="listview" data-inset="true"></ul>
					<fieldset data-role="controlgroup" data-type="horizontal"
						data-mini="true">
						<select name="category" id="category"
							onchange='$("#search_form").submit();'>
							<option value="all"
							<?php if($_GET["category"] == "all") print(' selected="selected"') ?>>
								Show everything <?php if($_GET["category"] == "all") print('(' . $xml->result["numFound"] . ')')?>
							</option>
							<?php foreach($categories as $category) {
								print("<option value=\"$category\"");
								if($_GET["category"] == $category) {
									print('selected="selected"');
								}
								print(">");
								$facet = $category;
								if ($category == "PubMed by date")
									$facet = "PubMed";
								else if ($category == "PubMed by relevance")
									$facet = "PubMed";
								print($category . " (" . get_facet_count($xml, $facet) . ")</option>");
							}
							?>
						</select>

						<input type="hidden" name="quantity" value="30" onchange='$("#search_form").submit();'>

					</fieldset>
				</form>
				<!-- END: Search bar with existing results -->
				
			</div>
			<?php if ($query_results_are_based_on_automatic_correction == true) {
						print("<div style='padding-bottom:1em'><p>You original query <em>$user_query</em> did not yield any results. Showing results for <em><b>$corrected_query</b></em> instead.</p></div>\n"); 
				   }?> 
			<div>
			
				<!-- BEGIN: List of results -->
				<ul data-role="listview" data-inset="false">
					$count = 0;
					<?php foreach($xml->result->doc as $doc):   // Iterate through documents in result set 
						if ($count >= $startpos)
						{
				        		$id = xpath($doc, "str[@name='id']")
					?>
						<li><a
							href="<?php if(substr($id, 0, 35) == "http://www.ncbi.nlm.nih.gov/pubmed/") print ("show.php?id=" . urlencode($id));
			        				   else print($id); ?>">
								<h3>
									<?php print xpath($doc, "arr[@name='title']/str"); ?>
								</h3>
								<p>
									<span class="data_source_name"><?php print xpath($doc, "str[@name='data_source_name']"); ?>
									</span> <span class="publication_date"><?php $date_created = substr(xpath($doc, "date[@name='dateCreated']"), 0, 10); 
			              									if ($date_created != "") print("&nbsp;|&nbsp;" . $date_created) ?>
									</span>
								</p> <?php if(xpath($doc, "str[@name='key_assertion']")): ?>
								<p class="conclusion">
									<?php print xpath($doc, "str[@name='key_assertion']")?>
								</p> <?php elseif($snippets = xpath($doc, "//lst[@name='highlighting']/lst[@name='${id}']/arr[@name='body']/str", true)): ?>
								<p class="text_snippet">
									<?php print("... " . implode(" ... ", $snippets) . " ..."); ?>
								</p> <?php endif; ?>
						</a></li>
					<?php }
					$count++;
					endforeach; ?>
				</ul>
				<!-- END: List of results -->
				

				<?php if ($xml->result["numFound"] == 0) print("<p>No results found.</p>") // if a query was entered and no results were found?>

<?php
if ($xml->result["numFound"] > $startpos + $max_number_of_results_per_request)
print "<p style=\"text-align:center\"><a href=\"index.php?q=" . $user_query . "&startpos=" . ($startpos + 30) . " data-role=\"button\" data-inline=\"true\">Next " . $max_number_of_results_per_request . " results</a></p>"
?>




				<p style="text-align:center">
					<a href="http://www.google.com/search?q=<?php print htmlspecialchars($user_query)?>" data-role="button" data-inline="true" target="blank">Try this search in Google</a>
					<a href="http://www.ncbi.nlm.nih.gov/pubmed/?term=<?php print htmlspecialchars($user_query)?>" data-role="button" data-inline="true" target="blank">Try this search in PubMed</a>
					<a href="http://www.tripdatabase.com/search?criteria=<?php print htmlspecialchars($user_query)?>" data-role="button" data-inline="true" target="blank">Try this search in Trip</a>
				</p>
			</div>

			<?php else: // if no query was entered, show default startup search bar ?>
			
				<!-- BEGIN: Default startup search bar -->
				<form action="index.php" method="get" id="search_form"
					data-ajax="false">
					<!--<label for="search-input">Search input:</label>-->
					<input type="search" name="q" id="q" data-theme="e"
						autocomplete="off" onkeyup="delay(function(){updateAutocomplete();}, 300 );"
						value="<?php print htmlspecialchars(urldecode($user_query))?>" />
					<ul id="autocomplete" data-role="listview" data-inset="true"></ul>
				</form>
				<p>Welcome to the FindMeEvidence prototype, a medical search engine
					for rapidly reviewing current, openly available medical evidence.
					Please enter a search query.</p>
				<!-- END: Default startup search bar -->
				</div>
			<?php endif; ?>
		</div>
		<div data-role="footer">
			<h4>This prototype is intended for evaluation use only and should not
				be used to guide medical treatment.</h4>
	
	</div>
</body>
</html>
