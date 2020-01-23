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
		$props = $this->user_reflection->getProperties();
		if(preg_match('/^.*?\/application\/libraries\/User.php$/', $this->user_reflection->getFileName()) ){
			return true;
		}
		return false;
	}
}
