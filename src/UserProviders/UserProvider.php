<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

use BlueRaster\PowerBIAuthProxy\Auth;

use BlueRaster\PowerBIAuthProxy\DB;

abstract class UserProvider{

	protected $user;

	protected $user_reflection;

	public static $gates = [
		"view" => null,
		"admin" => null,
	];

	final public function __construct(){
		$this->getUser();
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



	final public function can($ability = '*'){
		$ok = true;
		foreach(static::$gates as $gate){
    		if($gate() !== true){
        		$ok = false;
        		return false;
    		}
		}
		return $ok;
	}

	final public static function gate($handle, \Closure $callback){
        static::$gates[$handle] = $callback;
	}

	abstract public static function test(Framework $framework) : bool;
}


