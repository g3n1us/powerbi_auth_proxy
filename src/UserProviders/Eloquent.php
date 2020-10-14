<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

class Eloquent extends UserProvider{


	public function getUser() : BaseUser{
		$this->user = auth()->user() ?? new \Illuminate\Foundation\Auth\User;

		return $this->user;

	}

	public function logged_in(){
		return !!$this->user;
	}

	public function can($ability = '*'){
		return false;
	}

	public static function test(Framework $framework) : bool{
		$user = $this->getUser();
		if($user) return $user instanceof Illuminate\Foundation\Auth\User;
		return false;
	}
}
