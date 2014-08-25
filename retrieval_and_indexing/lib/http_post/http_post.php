<?php
function do_post_request($url, $post_string, $optional_headers = null)
{
        $header = array("Content-type:text/xml; charset=utf-8");

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
           return "curl request error: " . curl_error($ch);
        } else {
           curl_close($ch);
           return "curl request succesful, response: " . $data;
        }
        
        curl_close($ch);
}
?>