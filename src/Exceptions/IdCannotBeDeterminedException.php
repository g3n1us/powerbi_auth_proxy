<?php
	
namespace BlueRaster\PowerBIAuthProxy\Exceptions;


class IdCannotBeDeterminedException extends \Exception{
	protected $message = "The URL provided to EmbedUrl does not have an identifiable id.";

    public function __construct($message = null){
        parent::__construct();

        $this->message = $message ?? $this->message;
    }
	
}