<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;	
	
class Mock extends Framework{

	public function __construct(){
		
		$this->user = new User;
	}
	
	public static function test(){
		return php_sapi_name() === 'cli-server';
	}
	
}

class User{
	public $logged_in = true;
	public $loggedin = true;
}
