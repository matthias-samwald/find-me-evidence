<?php
/* Remove unwanted documents from the index. This is a bit of a hack: Ideally those would already 
 * be excluded during crawling/indexing, but sometimes this is not possible for technical reasons or for sake of simplicity.
 */
require_once('./lib/http_post/http_post.php');
include('./config.php');

print do_post_request(SOLR_URL . '/update', '<delete><query>(title:"ATTRACT | HOME") OR (title:"Page Not Found") (title:wikipedia.org/wiki/ATC_code_*) OR (id:web*) </query></delete>');
print do_post_request(SOLR_URL . '/update', '<commit/>');
print do_post_request(SOLR_URL . '/update', '<optimize/>');
	
?>
}