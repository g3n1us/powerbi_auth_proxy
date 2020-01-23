<?php

namespace BlueRaster\PowerBIAuthProxy;

use BlueRaster\PowerBIAuthProxy\Exceptions\MissingUserProviderException;

use BlueRaster\PowerBIAuthProxy\UserProviders\UserProvider;

class UserProxy{

	private $user_model;

	private $user_model_reflection;

	public function __construct(UserProvider $user_model){
		$this->user_model = $user_model;
		$this->user_model_reflection = new \ReflectionClass($user_model);
		dd($this);
	}


	public static function handle($user_model){
		(new self($user_model))->getProvider();
	}


	private function getProvider(){
		$tests = [
			'ci_ehris' => function(){
				$props = $this->user_model_reflection->getProperties();
				if(preg_match('/^.*?\/application\/libraries\/User.php$/', $this->user_model_reflection->getFileName()) ){
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
				if(isset($this->user_model->loggedin)){
					return $this->user_model->loggedin;
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


	private function abort(){
	    header("HTTP/1.1 403 Forbidden");
	    echo "Forbidden";
	    exit;
	}

}
