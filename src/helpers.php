<?php

if(!function_exists('dd')){
	function dd($variable, $depth = null){
	    return die(s($variable));
	}
}

if(!function_exists('env')){
	function env($variable, $default = null){
		$found = getenv($variable);
	    return $found ? $found : $default;
	}
}


