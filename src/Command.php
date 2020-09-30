<?php

namespace BlueRaster\PowerBIAuthProxy;

class Command{

	private static $instance;

	private static $stdin;

	public function _ask($question, $status = 'WARNING'){
		$this->say($question, $status);
		echo "> ";
		$stdin = $this->getStdin();
		return $this->clean(fgets($stdin));
	}

	public function _say($text, $status = 'NOTE'){
		echo($this->colorize($text, $status));
	}

	public function _clean($input){
		return trim(preg_replace('/[^a-z0-9\-\.]/', '', strtolower(trim($input))), '-');
	}

	public function _error($message = null){
		$this->say($message . PHP_EOL, "FAILURE");
		die($this->say('exiting' . PHP_EOL, "FAILURE"));
	}


	public function _confirm($question = "Are you sure?", $status = 'WARNING'){
		$answer = $this->ask($question . " [y/n]");
		if(!in_array(strtolower($answer), ['y', 'yes'])){
			exit($this->say('exiting' . PHP_EOL));
		}
	}


	public function _getStdin(){
		if(!static::$stdin){
			static::$stdin = fopen('php://stdin', 'r');
		}
		return static::$stdin;
	}


	public function _colorize($text, $status = 'NOTE') {
		$out = "";
		switch(strtoupper($status)) {
			case "SUCCESS":
			$out = "[32m"; //Green
			break;

			case "FAILURE":
			$out = "[31m"; //Red
			break;

			case "ERROR":
			$out = "[31m"; //Red
			break;

			case "WARNING":
			$out = "[33m"; //Yellow
			break;

			case "NOTE":
			$out = "[36m"; //Blue
			break;

			case "INFO":
			$out = "[36m"; //Blue
			break;

			case "MUTED":
			$out = "[37m"; // Grey
			break;

			default:
			throw new \Exception("Invalid status: " . $status);
		}
		return chr(27) . "$out" . "$text" . chr(27) . "[0m" . PHP_EOL;
	}

	public function __construct(){
		if(static::$instance) throw new \Exception("One instance can exist at at time.");
		static::$instance = $this;
	}

	public function __destruct(){
		fclose(static::getStdin());
	}

	public static function __callStatic($name, $arguments){
		if(!static::$instance) new self;
		return call_user_func_array([static::$instance, "_$name"], $arguments);
	}

	public function __call($name, $arguments){
		if(!static::$instance) new self;
		return call_user_func_array([static::$instance, "_$name"], $arguments);
	}

}
