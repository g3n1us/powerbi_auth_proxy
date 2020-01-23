<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;

class Framework{

	protected $user;
	
	protected $providers;
	
	public static function test(){
		return false;
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
	
	
	public function getUser(){
		return $this->user;
	}
}
