<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;
use BlueRaster\PowerBIAuthProxy\Frameworks\Mock as MockFramework;

class MockUserProvider extends UserProvider{

	public function logged_in(){
		return !!$this->getUser()->loggedin;
	}

/*
	public function can($ability = '*'){
		return true;
	}
*/

	public static function test(Framework $framework) : bool{
		return $framework instanceof MockFramework;
	}

	public function getUser() : BaseUser{
		$this->user = new User;
		$this->user->email = 'sbethel@blueraster.com';
		return new BaseUser($this->user, $this);
	}


}

MockUserProvider::gate('view', function(){
	return true;
});

class User{
	public $logged_in = true;
	public $loggedin = true;
}
