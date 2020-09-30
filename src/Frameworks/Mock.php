<?php

namespace BlueRaster\PowerBIAuthProxy\Frameworks;

use BlueRaster\PowerBIAuthProxy\Utils\Csrf;


class Mock extends Framework{

	protected $user_providers = ['MockUser'];


	public static function test(){
		return php_sapi_name() === 'cli-server';
	}

	public function getCsrf() : Csrf {

	}

	public function getUser(){
		$this->user = new User;
		return $this->user;
	}


}

class User{
	public $logged_in = true;
	public $loggedin = true;
}
