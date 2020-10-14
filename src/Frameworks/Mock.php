<?php

namespace BlueRaster\PowerBIAuthProxy\Frameworks;

use BlueRaster\PowerBIAuthProxy\Utils\Csrf;

use BlueRaster\PowerBIAuthProxy\Guards\MockGuard;


class Mock extends Framework{

	protected $user_providers = ['MockUserProvider'];


	public static function test(){
		return new MockGuard();

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

