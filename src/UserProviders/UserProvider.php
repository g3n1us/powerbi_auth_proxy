<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

class UserProvider{

	protected $user;

	protected $user_reflection;

	public static $gates = [];

	public function __construct($user){
		$this->user = $user;
		$this->user_reflection = new \ReflectionClass($user);
	}

	public function getUser(){
		return $this->user;
	}

	public function logged_in(){
		return false;
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
