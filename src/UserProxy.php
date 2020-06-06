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
		$instance = new self($user);

		if( ! Auth::getTokenFromReferrer() && ! $instance->user->logged_in() ){	
			self::abort();
		}
		
	}


	public static function abort(){
		header('Access-Control-Allow-Origin: *');
	    header("HTTP/1.1 403 Forbidden");
	    echo "Forbidden";
	    exit;
	}
}
