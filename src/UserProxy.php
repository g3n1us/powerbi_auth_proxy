<?php

namespace BlueRaster\PowerBIAuthProxy;

use BlueRaster\PowerBIAuthProxy\Exceptions\MissingUserProviderException;

use BlueRaster\PowerBIAuthProxy\UserProviders\UserProvider;

class UserProxy{

	private $user;

	private $user_reflection;

	public function __construct(UserProvider $user){
		$this->user = $user;
		$this->user_reflection = new \ReflectionClass($user);
	}


	public static function handle(UserProvider $user){
		$instance = new self($user);
		if( ! $instance->user->logged_in() ){
			$instance->abort();
		}
	}


	public function abort(){
	    header("HTTP/1.1 403 Forbidden");
	    echo "Forbidden";
	    exit;
	}
}

/*
	private function getProvider(){
		$tests = [
			'ci_ehris' => function(){
				$props = $this->user_reflection->getProperties();
				if(preg_match('/^.*?\/application\/libraries\/User.php$/', $this->user_reflection->getFileName()) ){
					return true;
				}
				return false;
			}
		];
		
		$found = array_filter($tests, 'call_user_func');
		if(empty($found)){
			throw new MissingUserProviderException;
		}

		foreach($tests as $key => $test_fn){
			if($test_fn() === true){
				$this->getHandlerForProvider($key);
			}
		}
	}
	

	private function getHandlerForProvider($key){
		$handlers = [
			'ci_ehris' => function(){
				if(isset($this->user->loggedin)){
					return $this->user->loggedin;
				}
				return false;
			}
		];

		if(!isset($handlers[$key])){
			$this->abort();
		}
		$result = $handlers[$key]();

		if($result !== true){
			$this->abort();
		}

	}
*/


