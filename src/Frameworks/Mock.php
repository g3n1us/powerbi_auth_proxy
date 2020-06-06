<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;	

use BlueRaster\PowerBIAuthProxy\Utils\Csrf;

	
class Mock extends Framework{

	public function __construct(){
		
		$this->user = new User;
	}
	
	public static function test(){
		return php_sapi_name() === 'cli-server';
	}
	
	public function getCsrf() : Csrf {
		
	}	
	
}

class User{
	public $logged_in = true;
	public $loggedin = true;
}
