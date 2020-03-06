<?php

namespace BlueRaster\PowerBIAuthProxy\Exceptions;

class MissingConfigException extends \Exception{
	protected $message = "Required configuration values are missing.";

    public function __construct($message = null){
        parent::__construct();

        $this->message = $message ?? $this->message;
    }

/*
	public function handle(){
		// die($this->message);
	}
*/
}
