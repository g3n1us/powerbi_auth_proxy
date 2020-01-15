<?php

namespace BlueRaster\PowerBIAuthProxy;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;


class Installer{

	private $vendorDir;

	private $applicationDir;

	private $controller_contents = 'BlueRaster\\PowerBIAuthProxy\\Routes::route();';

	public static function postAutoloadDump(Event $event){
		new self($event);

	}

	public function __construct(Event $event){
        $this->vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $this->applicationDir = dirname($this->vendorDir);
        require $this->vendorDir . '/autoload.php';
        $steps = $this->isInstalled();
		if($steps !== true){
			Command::say("Installation of the PowerBIAuthProxy is not yet complete.");
			Command::say("You will be guided through a few short steps to complete installation.");
			Command::confirm("Would you like to continue?");
			$required_steps = [
				'installController',
				//
			];

			$total_steps = count($steps);
			$current_step = 1;
			foreach($required_steps as $step){
				if(in_array($step, $steps)){
					Command::say("\n*** Step $current_step of $total_steps:\n\n");
					$current_step++;
					$this->{$step}();
				}
			}
		}
	}

	private function isInstalled(){
		$missing = [];
		// is the controller in place?
		$controller_filepath =  $this->applicationDir . '/core/My_Controller.php';
		if(!file_exists($controller_filepath)){
			$missing[] = 'installController';
		}
		else if(strpos(file_get_contents($controller_filepath), 'PowerBIAuthProxy') === false){
			$missing[] = 'installController';
		}

		// is composer autoloading setting set to true?

		if(empty($missing)) return true;
		return $missing;
	}

	private function installController(){
		$filepath =  $this->applicationDir . '/core/My_Controller.php';
		if(!file_exists($filepath)){
			file_put_contents($filepath, "<?php" . PHP_EOL . $this->controller_contents . PHP_EOL);
			Command::say("The controller file has been created at: \n" . $filepath);
			Command::say("This will get called automatically by CodeIgniter, so no other steps are needed.\n");
		}
		else{
			Command::say("The controller file cannot be created automatically.", 'warning');
			Command::say("Open the file located at: \n\n");
			Command::say("$filepath\n\n", 'muted');

			Command::say("Add the following line of code immediately inside of the '__construct' function:\n\n");
			Command::say($this->controller_contents . "\n\n", 'muted');
			Command::ask("When finished, press enter/return to continue");
		}
	}
}


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
