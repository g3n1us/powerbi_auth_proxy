<?php
	
namespace BlueRaster\PowerBIAuthProxy\UserProviders;	
	
class Prologin extends UserProvider{
	
	public function logged_in(){
		return !!$this->user->loggedin;
	}
	
	public function can($ability = '*'){
		return false;
	}
		
	public static function test(){
		return false;
	}
}
