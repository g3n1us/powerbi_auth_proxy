<?php
use Illuminate\Support\Str;

if(!function_exists('auth_proxy')){
	function auth_proxy(){
    	return \BlueRaster\PowerBIAuthProxy\Auth::get_instance();

	}
}

if(!function_exists('auth_proxy_base_path')){
	function auth_proxy_base_path($subpath = null){
		$subpath = $subpath ? Str::start($subpath, '/') : '';
    	return dirname(__DIR__) . $subpath;

	}
}

Kint::$aliases[] = 'ddd';
if(!function_exists('ddd')){
	function ddd($variable, $depth = null){
    	foreach(func_get_args() as $v) d($v);
	    die();
	}
}

Kint::$aliases[] = 'dd';
if(!function_exists('dd')){
	function dd($variable, $depth = null){
    	foreach(func_get_args() as $v) s($v);
	    die();
	}
}

Kint::$aliases[] = 'dump';
if(!function_exists('dump')){
	function dump($variable, $depth = null){
    	foreach(func_get_args() as $v) s($v);
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
		$res = $client->get($url);

		$body = $res->getBody();
		$content = '';
		while (!$body->eof()) {
		    $content .= $body->read(1024);
		}
        return $content;
    }
}


if(!function_exists('guzzle_post_contents')){
    function guzzle_post_contents($url){
        $client = new GuzzleHttp\Client;
		$res = $client->post($url);

		$body = $res->getBody();
		$content = '';
		while (!$body->eof()) {
		    $content .= $body->read(1024);
		}
        return $content;
    }
}


if(!function_exists('spread_url')){
	function spread_url($str){
		$parts = array_merge(['scheme' => null, 'query' => null, 'host' => null, 'path' => null, 'fragment' => null], parse_url($str));
		$q = parse_str($parts['query'], $qq);
		$parts['query'] = $qq;
		if(array_keys(array_filter($parts)) == ['path']){
			return false;
		}

		return $parts;
	}
}


if(!function_exists('parse_keyless_query')){
	function parse_keyless_query($str){
		$arr = $str;
		if(is_string($str)){
			parse_str($str, $arr);
		}
		if([1, 0] == [count(array_filter(array_keys($arr))), count(array_filter($arr))]){
			return array_keys($arr)[0];
		}
		return false;
	}
}

if(!function_exists('clean_array_from_string')){
	function clean_array_from_string($str, $delimiter = ","){
		$parts = explode($delimiter, trim(trim($str), $delimiter));
		$parts = array_map('trim', $parts);
		return array_filter($parts);
	}
}

// make this join url!
if(!function_exists('spread_url')){
	function spread_url($str){
		$parts = array_merge(['scheme' => null, 'query' => null, 'host' => null, 'path' => null, 'fragment' => null], parse_url($str));
		$q = parse_str($parts['query'], $qq);
		$parts['query'] = $qq;

		if(array_keys(array_filter($parts)) == ['path']){
			return false;
		}

		return $parts;
	}
}

