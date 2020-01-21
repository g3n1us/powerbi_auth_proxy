<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;	
	
class Prologin extends Framework{
	
	public static $ci;
	
	public function __construct(){
		if(! static::$ci =& get_instance() ) {
			new \Ci_Controller;
			static::$ci =& get_instance();
		}
		
		$this->user = static::$ci->user;
	}
	
	public static function test(){
		return false;
	}
}
