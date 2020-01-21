<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;	
	
class Mock extends Framework{

	public function __construct(){
		
		$this->user = new User;
	}
	
	public static function test(){
		return true;
	}
	
}

class User{
	public $logged_in = true;
	public $loggedin = true;
}
