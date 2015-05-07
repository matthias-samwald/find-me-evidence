<?php

require_once('./lib/http_post/http_post.php');
include('./config.php');

print do_post_request(SOLR_URL . '/update', '<delete><query>category:Pubmed</query></delete>');

print do_post_request(SOLR_URL . '/update', '<commit/>');

print do_post_request(SOLR_URL . '/update', '<optimize/>');