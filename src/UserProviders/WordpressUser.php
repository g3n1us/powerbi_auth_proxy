<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Frameworks\Framework;

class WordpressUser extends UserProvider{

	public function logged_in(){
		return is_user_logged_in();
	}

	public function can($ability = '*'){
		return false;
	}

	public static function test(Framework $framework) : bool{
		$user = $framework->getUser();
		if($user) return $user instanceof \WP_User;
		return function_exists( 'wp_get_current_user' );
	}
}
