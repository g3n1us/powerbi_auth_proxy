<?php

namespace BlueRaster\PowerBIAuthProxy\Guards;

abstract class Guard {

	protected $exception;

	public function __construct(){

		if(!$this->exception){
			throw new \Exception("An error occurred.");
		}
		throw new $this->exception
	}

	abstract public function logic();

}
