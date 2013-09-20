<?php
$q = $_GET ["q"];
if ($q != "" and strlen ( $q ) > 2) {
	
	$response = file_get_contents ( "http://preview.ncbi.nlm.nih.gov/portal/utils/autocomp.fcgi?dict=pm_related_queries_2&callback=?&q=" . urlencode ( $q ) );
	preg_match ( "/Array\(([^\)]+)/", $response, $response );
	$response = $response [1];
	$response = trim ( $response, '"' );
	$response = explode ( '", "', $response );
	$response = array_slice ( $response, 0, 5 );
	
	/*
	 * Generate Mock data which can be used for debugging/testing sleep(1); $response = array("test" . rand(0, 1000), "test". rand(0, 1000), "test" . rand(0, 1000), "test". rand(0, 1000));
	 */
	
	print json_encode ( $response );
}
?>