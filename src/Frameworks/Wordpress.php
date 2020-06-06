<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;	

use BlueRaster\PowerBIAuthProxy\Utils\Csrf;

	
class Wordpress extends Framework{
	
	protected $user_providers = ['WordpressUser'];
	
	public function __construct(){
		parent::__construct();
		$user = wp_get_current_user();
		$this->user = $user ? $user : new \WP_User;
	}
	
	public static function test(){
		return function_exists( 'wp_get_current_user' );
	}
	
	public function getCsrf() : Csrf {
		
	}	
}
