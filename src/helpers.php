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


