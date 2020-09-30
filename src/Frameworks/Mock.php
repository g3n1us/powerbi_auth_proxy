<?php

namespace BlueRaster\PowerBIAuthProxy\Frameworks;

use BlueRaster\PowerBIAuthProxy\Utils\Csrf;


class Mock extends Framework{

	protected $user_providers = ['MockUserProvider'];


	public static function test(){
		return php_sapi_name() === 'cli-server';
	}

	public function getCsrf() : Csrf {

	}

/*
	public function getUser(){
		$this->user = new User;
		return $this->user;
	}
*/


}

