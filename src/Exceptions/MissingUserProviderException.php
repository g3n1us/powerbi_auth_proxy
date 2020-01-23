<?php
	
namespace BlueRaster\PowerBIAuthProxy\Exceptions;

class MissingUserProviderException extends \Exception{
	protected $message = "A user model was not properly provided to the application.";
	
/*
	public function handle(){
		dd($this);
	}
*/
	
}
