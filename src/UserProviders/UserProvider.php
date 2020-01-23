<?php
	
namespace BlueRaster\PowerBIAuthProxy\UserProviders;	
	
use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

class UserProvider{
	
	protected $user;
	
	protected $user_reflection;
	
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
		return false;
	}
		
	public static function test(Framework $framework){
		return false;
	}
}
