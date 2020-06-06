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
		return new BaseUser($this->user, $this);
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




class BaseUser{
	
	protected $user;
	
	public function __construct($user, UserProvider $provider){
		$this->user = $user;
		$this->provider = $provider;
	}
	
	
	public function getEmail(){
		return $this->provider->getEmail();
	}
	
	
	public function getName(){
		return $this->provider->getName();
	}
	
	
	
	public function __get($name){
		return @$this->user->{$name};
	}
	
	
	
	public function __call($name, $args){
		if(method_exists($this->provider, $name)){
			return call_user_func_array([$this->provider, $name], $args);
		}
		return call_user_func_array([$this->user, $name], $args);
	}
	
}
