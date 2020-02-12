<?php

namespace BlueRaster\PowerBIAuthProxy;

use BlueRaster\PowerBIAuthProxy\Exceptions\MissingUserProviderException;

use BlueRaster\PowerBIAuthProxy\UserProviders\UserProvider;

use ReflectionClass;

class UserProxy{

	private $user;

	private $user_reflection;

	public function __construct(UserProvider $user){
		$this->user = $user;
		$this->user_reflection = new ReflectionClass($user);
	}

	public static function handle(UserProvider $user){
    	file_put_contents(__DIR__.'/origins.txt', @$_SERVER['HTTP_REFERER'] . PHP_EOL, FILE_APPEND);
    	['host' => $referrer_host] = array_merge(['host' => false], parse_url(@$_SERVER['HTTP_REFERER']));
        $accepted_referrers = array_map('trim', explode(',', Auth::config('accepted_referrers', 'empty')));

    	if(in_array($referrer_host, $accepted_referrers)){
        	return true;
    	}
		$instance = new self($user);

		if( ! $instance->user->logged_in() ){
			self::abort();
		}
	}


	public static function abort(){
	    header("HTTP/1.1 403 Forbidden");
	    echo "Forbidden";
	    exit;
	}
}

