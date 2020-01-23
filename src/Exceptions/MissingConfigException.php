<?php
	
namespace BlueRaster\PowerBIAuthProxy\Exceptions;

class MissingConfigException extends \Exception{
	protected $message = "Required configuration values are missing.";
	
	public function handle(){
		die('missing config');
	}
}
