<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

use ReflectionClass;

class Prologin extends UserProvider{

	public static $ci;


	public function getUser() : BaseUser{
		if(! static::$ci =& get_instance() ) {
			new \Ci_Controller;
			static::$ci =& get_instance();
		}

		$this->user = static::$ci->user;

		return new BaseUser($this->user, $this);
	}


	public function logged_in(){
		return !!$this->user->loggedin;
	}

	public function can($ability = '*'){
		return true;
	}

	public function getName(){
		return $this->user->info->first_name . ' ' . $this->user->info->last_name;
	}

	public function getEmail(){

		return $this->user->info->email;
	}


	public static function test(Framework $framework){
		return false;
		$user = $framework->getUser();
		$reflection = new ReflectionClass($user);

		$props = $reflection->getProperties();

		if(preg_match('/^.*?\/application\/libraries\/User.php$/', $reflection->getFileName()) ){
			return true;
		}
		return false;
	}
}
