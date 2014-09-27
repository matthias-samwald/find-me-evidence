<?php

$count = 0;

$nextpage = download("http://www.pubmedcentral.nih.gov/utils/oa/oa.fcgi?from=2014-09-21", false);

while ($nextpage != "") {
    $nextpage = download($nextpage);
}

function download($nextpage, $append = true) {
    $repsonse = file_get_contents($nextpage);
    $xml = simplexml_load_string($repsonse);
    $ids = $xml->xpath("/OA/records/record/@id");
    if ($append) {
        $output_file_content = "\n" . implode($ids, "\n");
        file_put_contents("./pubmed/oa_articles.txt", $output_file_content, FILE_APPEND);
    } else {
        $output_file_content = implode($ids, "\n");
        file_put_contents("./pubmed/oa_articles.txt", $output_file_content);
    }
    global $count;
    $count+= count($ids);
    echo $count . "\n";
    $next = $xml->xpath("/OA/records/resumption/link/@href")[0];
    echo $next . "\n";
    return $next;
}
