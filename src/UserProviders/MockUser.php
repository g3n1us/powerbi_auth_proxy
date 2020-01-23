<?php
	
namespace BlueRaster\PowerBIAuthProxy\UserProviders;	
	
use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;	
	
class MockUser extends UserProvider{
	
	public function logged_in(){
		return !!$this->user->loggedin;
	}
	
	public function can($ability = '*'){
		return false;
	}
		
	public static function test(Framework $framework){
		return false;
	}
}
