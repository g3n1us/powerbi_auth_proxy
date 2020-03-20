<?php

if(!function_exists('dd')){
	function dd($variable, $depth = null){
    	foreach(func_get_args() as $v) s($v);
	    die();
	}
}

if(!function_exists('env')){
	function env($variable, $default = null){
		$found = getenv($variable);
	    return $found ? $found : $default;
	}
}

if(!function_exists('guzzle_get_contents')){
    function guzzle_get_contents($url){
        $client = new GuzzleHttp\Client;
        // 'https://pbi-auth.dev.also-too.com' . 
		$res = $client->get($url);

		$body = $res->getBody();
		$content = '';
		while (!$body->eof()) {
		    $content .= $body->read(1024);
		}
        return $content;
    }
}
