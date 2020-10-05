<?php

namespace BlueRaster\PowerBIAuthProxy\Guards;

use Exception;

abstract class Guard {

/**
* $exception can be a string name of the exception class, an array of the previous, or a closure that returns one of the previous
*
*
*/
	protected $exception;

	final public function __construct(){

		if(!$this->exception){
			throw new Exception("An error occurred.");
		}
		else{
			if(is_callable($this->exception)){
				$e = $this->exception();
			}

			$exceptions = is_callable($this->exception) ? $this->exception() : $this->exception;
			$exceptions = !is_array($exceptions) ? [$exceptions] : $exceptions;
			foreach($exceptions as $e){
				throw new $e;
			}
		}
	}

	abstract public static function challenge() : self;

}




