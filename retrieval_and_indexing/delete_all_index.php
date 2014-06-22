<?php

require_once('./lib/http_post/http_post.php');
include('./config.php');

print do_post_request(SOLR_URL . '/update', '<delete><query>*:*</query></delete>');

print do_post_request(SOLR_URL . '/update', '<commit/>');