<?php


function doCurl($request, $url, $headers, $userAgent, $cookieFile, $params)
{
    const USER_AGENT = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6";
    
    // Initialize cURL and set form URL to request.
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_USERAGENT,  USER_AGENT);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    // Turn OFF ssl.
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    
    // Turn ON cookies.
    curl_setopt($curl, CURLOPT_COOKIEJAR,  $cookieFile);
    curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
    
    // Turn ON post request.
    switch (strtolower($request)) {
        case 'post' :
            curl_setopt($curl, CURLOPT_POST, count($params));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;
        case $request :
            curl_setopt($curl, CURLOPT_POST, false);
            break;
        default :
            curl_setopt($curl, CURLOPT_POST, false);
    }

    // Execute the post.
    $result = curl_exec($curl);

    // Collect the errors.
    $errors = curl_error($curl);

    // Close the connection.
    curl_close($curl);

    return array('status' => 'success', 'data' => $result, 'msg'=>'', 'errors' => $errors);
}


function getRequestHeaders($params, $referer)
{
    $headers = array();
    if (isset($params['host']))            { $headers[] = 'Host: ' . $params['host']; }
    if (isset($params['accept']))          { $headers[] = 'Accept: ' . $params['accept']; }
    if (isset($params['accept-encoding'])) { $headers[] = 'Accept-Encoding: ' . $params['accept-encoding']; }
    if (isset($params['accept-language'])) { $headers[] = 'Accept-Language: ' . $params['accept-language']; }
    if (isset($params['origin']))          { $headers[] = 'Origin: ' . $params['origin']; }
    if (isset($referer))                   { $headers[] = 'Referer: ' . $referer; }
    
    return $headers;
}


function getInnerText($source, $tokenStart, $tokenEnd, $debug = false)
{
    // Find the start and end tokens for the text.
    $result = array();
    $value = null;
    $posStart = 0;
    $posEnd   = 0;
    $posToken = 0;
    
    // Check ONLY IF we have a source to search.
    if (!is_null($source) && $source != '') {
        if ($debug == true) {
            echo "<br/>";
            echo "We have a source to search.<br/>";
            echo "Source length == ".strlen($source)."<br/>";
        }
        $posStart = strpos($source, $tokenStart);
        if ($debug == true) { echo "tokenStart == ".$tokenStart."<br/>"; echo "posStart == ".$posStart."<br/>"; }
        if ($posStart > 0) {
            $posToken = $posStart + strlen($tokenStart);
            if ($tokenEnd != '' && !is_null($tokenEnd)) {
                $posEnd = strpos($source, $tokenEnd, $posToken);
                
                if ($debug == true) { echo "tokenEnd == ".$tokenEnd."<br/>"; echo "posEnd == ".$posEnd."<br/>"; }
                
                if ($posEnd > 0) {
                    $value = substr($source, $posToken, $posEnd - $posToken);
                } else {
                    $value = '';
                }
            } else {
                $value = substr($source, $posToken);
            }
        } else {
            $value = '';
        }
    }
    
    if (!is_null($value)) {
        $result = array('status'=>'success', 'data'=>$value, 'msg'=>'Found inner text.');
    } else {
        $result = array('status'=>'error', 'data'=>'', 'msg'=>'Inner text not found.');
    }
    
    return $result;
}


function getInnerTextMulti($source, $tokenStart, $tokenEnd, $debug = false)
{
    $result = array();
    $values = array();
    $posStart = 0;
    $posEnd   = 0;
    $posToken = 0;
    
    $debug = false;
    // Check ONLY IF we have a source.
    if (!is_null($source) && $source != '') {
        if ($debug == true) {
            echo "<br/>";
            echo "We have a multi-source to search.<br/>";
            echo "Source length == ".strlen($source)."<br/>";
        }
        $posStart = strpos($source, $tokenStart);
        do {
            if ($posStart > 0) {
                $posToken = $posStart + strlen($tokenStart);
                $posEnd = strpos($source, $tokenEnd, $posToken);
                if ($posEnd > 0) {
                    $values[] = substr($source, $posToken, $posEnd - $posToken);
                    $posStart = strpos($source, $tokenStart, $posEnd + strlen($tokenEnd));
                } else {
                    $values[] = substr($source, $posToken);
                    $posEnd = strlen($source);
                    $posStart = -1;
                }
            }
        } while ($posStart > 0);
        
        if (count($values) > 0) {
            $result = array('status'=>'success', 'data'=>$values, 'msg'=>'Found inner text.');
        } else {
            $result = array('status'=>'success', 'data'=>$values, 'msg'=>'No inner text found.');
        }
    } else {
        $result = array('status'=>'error', 'data'=>'', 'msg'=>'No source to search.');
    }
    
    return $result;
}
