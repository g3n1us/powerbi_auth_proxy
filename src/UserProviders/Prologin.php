<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

use ReflectionClass;

class Prologin extends UserProvider{

	public function logged_in(){
		return !!$this->user->loggedin;
	}

	public function can($ability = '*'){
		return true;
	}
	
	public function getEmail(){

		return $this->user->info->email;
	}


	public static function test(Framework $framework){
		$user = $framework->getUser();
		$reflection = new ReflectionClass($user);

		$props = $reflection->getProperties();

		if(preg_match('/^.*?\/application\/libraries\/User.php$/', $reflection->getFileName()) ){
			return true;
		}
		return false;
	}
}
