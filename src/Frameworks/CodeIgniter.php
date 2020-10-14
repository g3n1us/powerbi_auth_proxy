<?php

namespace BlueRaster\PowerBIAuthProxy\Frameworks;

use BlueRaster\PowerBIAuthProxy\Utils\Csrf;


class CodeIgniter extends Framework{

	protected $user_providers = ['Prologin'];

	public static $ci;

	public function __construct(){
		parent::__construct();
		if(! static::$ci =& get_instance() ) {
			new \Ci_Controller;
			static::$ci =& get_instance();
		}

	}

	public function getConfig(){
    	return @static::$ci->config->config['powerbi_auth_proxy'] ?? [];
	}

	public static function test(){
		return class_exists('Ci_Controller');
	}

	public function getCsrf() : Csrf {
		return new Csrf(static::$ci->security->get_csrf_token_name(), static::$ci->security->get_csrf_hash());
	}
}
