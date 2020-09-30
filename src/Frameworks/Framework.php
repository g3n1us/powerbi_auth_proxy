<?php

namespace BlueRaster\PowerBIAuthProxy\Frameworks;

use BlueRaster\PowerBIAuthProxy\Utils\Csrf;

abstract class Framework{

// 	protected $user;

	protected $user_providers;

	protected $user_provider;

	protected $config;

	public function __construct(Array $config = []){
		$this->config = $config;
	}

	public function getConfig(){
    	return $this->config;
	}

	public function installerPath(){
		$classname = class_basename($this);
		return auth_proxy_base_path("installers/$classname/installer.php");
	}

	public static function test(){
		return false;
	}

	public function getUserProvider(){
		if($this->user_provider) return $this->user_provider;
		$this->user_provider = collect($this->user_providers)->map(function($classname){
			$p = "BlueRaster\\PowerBIAuthProxy\\UserProviders\\$classname";

			if($p::test($this)){
				return new $p;
			}
		})->filter()->first();

		return $this->user_provider;
	}

	// Provides the currently used "user" object that the framework is utilizing.
	//
	final public function getUser(){
		return $this->getUserProvider()->getUser();
// 		return $this->user;
	}


	abstract public function getCsrf() : Csrf;
}
