<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;

abstract class Framework{

	protected $user;
	
	protected $user_providers;
	
	public function __construct(Array $config = ['user' => null]){
		[ 'user' => $user ] = $config;
		if($user) $this->user = $user;
	}
	
	public static function test(){
		return false;
	}
	
	public function getUserProvider(){
		return collect($this->user_providers)->map(function($classname){
			$p = "BlueRaster\\PowerBIAuthProxy\\UserProviders\\$classname";
			if($p::test($this)){
				return new $p($this->getUser());
			}
		})->filter()->first();
	}
	
	// Provides the currently used "user" object that the framework is utilizing.
	// 
	public function getUser(){
		return $this->user;
	}
}
