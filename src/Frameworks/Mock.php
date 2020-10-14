<?php

namespace BlueRaster\PowerBIAuthProxy\Frameworks;

use BlueRaster\PowerBIAuthProxy\Utils\Csrf;

use BlueRaster\PowerBIAuthProxy\Guards\MockGuard;


class Mock extends Framework{

	protected $user_providers = ['MockUserProvider'];


	public static function test(){
		return new MockGuard();

	}

	public function getCsrf() : Csrf {

	}

	public function getConfig(){
		return [
			'selected_reports' => [
				[
					'id' => '',
					'type' => 'powerbi',
					'name' => 'Demonstration',
				],
			],
		    'auth_proxy_gate' => function($router){
		        // dd($router);
		        return true;
		    },

		];
	}


}

