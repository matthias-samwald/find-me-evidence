<?php

//http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?retmode=xml&dbfrom=pubmed&id=19755503&cmd=neighbor&linkname=pubmed_pubmed_citedin

echo pubmed_count_citedin('19755503') . " citations";

function pubmed_count_citedin($id) {

    $url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi';

    $params = array(
        'dbfrom' => 'pubmed',
        'retmode' => 'xml',
        'id' => $id,
        'cmd' => 'neighbor',
        'linkname' => 'pubmed_pubmed_citedin'
    );

    foreach ($params as $key => $value) {
        $params_string .= $key . '=' . $value . '&';
    }
    rtrim($params_string, '&');

    //open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($params));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $xml = simplexml_load_string($response);
        $result = $xml->xpath("/eLinkResult/LinkSet/LinkSetDb[LinkName='pubmed_pubmed_citedin' and DbTo='pubmed']/Link/Id");
        return count($result);
    } else {
        return "";
    }
}
