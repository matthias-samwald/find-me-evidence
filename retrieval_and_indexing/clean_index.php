<?php
/* Remove unwanted documents from the index. This is a bit of a hack: Ideally those would already 
 * be excluded during crawling/indexing, but sometimes this is not possible for technical reasons or for sake of simplicity.
 */

$url = "localhost:8888/solr/collection1/update";

update_solr_index($url, '<delete><query>(title:"ATTRACT | HOME") OR (title:"wikipedia.org/wiki/ATC_code_")</query></delete>');
update_solr_index($url, '<commit/>');
update_solr_index($url, '<optimize/>');


// simple function for updating Solr index via curl 
function update_solr_index($url, $command) {
	
	$header = array("Content-type:text/xml; charset=utf-8");
	
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $command);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
	
	$data = curl_exec($ch);
	
	if (curl_errno($ch)) {
		print "curl_error:" . curl_error($ch);
	} else {
		curl_close($ch);
		print "curl exited okay\n";
		echo "Data returned...\n";
		echo "------------------------------------\n";
		echo $data;
		echo "------------------------------------\n";
	}
}
	
?>
}