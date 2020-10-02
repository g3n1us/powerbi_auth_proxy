<?php

if(!function_exists('auth_proxy')){
	function auth_proxy(){
    	return Utils::auth_proxy();
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

