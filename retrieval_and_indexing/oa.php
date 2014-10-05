<?php

$count = 0;

$nextpage = download("http://www.pubmedcentral.nih.gov/utils/oa/oa.fcgi?from=2004-10-01", false);

while ($nextpage != "") {
    $nextpage = download($nextpage);
    sleep(1);
}
//http://www.pubmedcentral.nih.gov/utils/oa/oa.fcgi?resumptionToken=2727940!20041001000000!!!39cfbe5a400aa4f5
//1712494
//PHP Notice:  Undefined offset: 0 in /home/georg/Projects/uni/find-me-evidence/retrieval_and_indexing/oa.php on line 27
//PHP Stack trace:
//PHP   1. {main}() /home/georg/Projects/uni/find-me-evidence/retrieval_and_indexing/oa.php:0
//PHP   2. download() /home/georg/Projects/uni/find-me-evidence/retrieval_and_indexing/oa.php:8


function download($nextpage, $append = true) {
    $response = file_get_contents($nextpage);
    if ($response !== FALSE) {
        $xml = simplexml_load_string($response);
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
    } else {
        return download($nextpage, $append);
    }
}
