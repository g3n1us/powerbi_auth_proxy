<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

class MockUserProvider extends UserProvider{

	public function __construct(){
// 		dd($this);
	}

	public function logged_in(){
		return !!$this->getUser()->loggedin;
	}

	public function can($ability = '*'){
		return true;
	}

	public static function test(Framework $framework){
		return true;
	}

	public function getUser() : BaseUser{
		$this->user = new User;
		$this->user->email = 'sbethel@blueraster.com';
		return new BaseUser($this->user, $this);
	}


}


class User{
	public $logged_in = true;
	public $loggedin = true;
}
