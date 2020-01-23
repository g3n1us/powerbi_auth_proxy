<?php
	
namespace BlueRaster\PowerBIAuthProxy\UserProviders;	
	
class UserProvider{
	
	private $user;
	
	public function __construct($user){
		$this->user = $user;
		$this->user_reflection = new \ReflectionClass($user);
	}
	
	public function logged_in(){
		return false;
	}
	
	public function can($ability = '*'){
		return false;
	}
		
	public static function test(){
		return false;
	}
}
