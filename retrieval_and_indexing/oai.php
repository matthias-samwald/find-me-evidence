<?php

$count = 0;

$nextpage = download("http://www.pubmedcentral.nih.gov/oai/oai.cgi?verb=ListRecords&metadataPrefix=pmc&set=pmc-open", false);

while ($nextpage != "http://www.pubmedcentral.nih.gov/oai/oai.cgi?verb=ListRecords&resumptionToken=") {
    $nextpage = download($nextpage);
}

function download($nextpage, $append = true) {
    $repsonse = file_get_contents($nextpage);
    $xml = simplexml_load_string($repsonse);
    
    $xml->registerXPathNamespace('o', 'http://www.openarchives.org/OAI/2.0/');
    
    $ids = $xml->xpath("/o:OAI-PMH/o:ListRecords/o:record/o:metadata/*[local-name()='article']/*[local-name()='front']/*[local-name()='article-meta']/*[local-name()='article-id' and @pub-id-type='pmid']");
    
    if ($append) {
        $output_file_content = "\n" . implode($ids, "\n");
        file_put_contents("./pubmed/oai_articles.txt", $output_file_content, FILE_APPEND);
    } else {
        $output_file_content = implode($ids, "\n");
        file_put_contents("./pubmed/oai_articles.txt", $output_file_content);
    }
    global $count;
    $count+= count($ids);
    echo $count . "\n";
    $next = "http://www.pubmedcentral.nih.gov/oai/oai.cgi?verb=ListRecords&resumptionToken=" . $xml->ListRecords->resumptionToken;
    echo $next . "\n";
    return $next;
}
