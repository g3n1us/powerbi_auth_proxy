<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;	
	
class CodeIgniter extends Framework{
	
	protected $providers = ['Prologin'];
	
	public static $ci;
	
	public function __construct(){
		if(! static::$ci =& get_instance() ) {
			new \Ci_Controller;
			static::$ci =& get_instance();
		}
		
		$this->user = static::$ci->user;
	}
	
	public static function test(){
		return class_exists('Ci_Controller');
	}
}
