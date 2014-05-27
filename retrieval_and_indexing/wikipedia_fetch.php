<?php

// Load list of article labels into an array
$article_list_file_contents = file_get_contents("./wikipedia/relevant_articles.txt");
$article_labels = explode("\n", $article_list_file_contents);

$count = count($article_labels);
print "$count articles found\n";

$retmax = 1000; // Maximum number of entries returned per request

for ($retstart = 0; $retstart < $count; $retstart += $retmax) {
    $pages = "";
    if ($retstart + $retmax < $count) {
        for ($number = $retstart; $number < $retstart + $retmax; $number++) {
            $pages.= $article_labels[$number] . "%0D%0A";
        }
    //last part
    } else {
        for ($number = $retstart; $number < $count; $number++) {
            $pages.= $article_labels[$number] . "%0D%0A";
        }
    }
    
    rtrim($pages, '%0D%0A');

    $url = 'http://en.wikipedia.org/w/index.php?title=Special:Export&action=submit';

    $options = array('http' => array('method' => 'POST', 'content' => 'pages=' . $pages . '&curonly=1'));
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    file_put_contents("./wikipedia/".$retstart.".xml", $result);
    
    echo $retstart."\n";
}