<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

use BlueRaster\PowerBIAuthProxy\Auth;

use BlueRaster\PowerBIAuthProxy\DB;

abstract class UserProvider{

	protected $user;

	protected $user_reflection;

	public static $gates = [];

	public function __construct(){

	}

	abstract public function getUser() : BaseUser;


	public function logged_in(){
		return false;
	}

	public function getName(){

		return @$this->user->name;
	}

	public function getEmail(){

		return @$this->user->email;
	}



	public function can($ability = '*'){
		$ok = true;
		foreach(static::$gates as $gate){
    		if($gate() !== true){
        		$ok = false;
        		return false;
    		}
		}
		return $ok;
	}

	public static function gate($handle, Closure $callback){
        static::$gates[$handle] = $callback;
	}

	public static function test(Framework $framework){
		return false;
	}
}


