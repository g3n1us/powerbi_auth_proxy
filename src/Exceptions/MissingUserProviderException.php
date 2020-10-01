<?php

namespace BlueRaster\PowerBIAuthProxy\Exceptions;

class MissingUserProviderException extends \Exception{


	protected $message = "A user model was not properly provided or could not be determined by the application.";

	public function handle(){
		throw new \Exception($this->message, 1098, $this);
	}

}
