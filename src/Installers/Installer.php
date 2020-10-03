<?php

namespace BlueRaster\PowerBIAuthProxy\Installers;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use BlueRaster\PowerBIAuthProxy\Command;
use BlueRaster\PowerBIAuthProxy\Utils;


abstract class Installer{



	public static function postAutoloadDump(Event $event){
		(new self())->run($event);
	}

	final public function __construct(){
    	$this->installed_with_composer = basename(dirname(dirname(__DIR__))) == 'vendor';

	}


	final public static function getInstaller(){
		$framework_name = Utils::getFramework();
		$installerClass = __NAMESPACE__ . '\\' . class_basename($framework_name).'Installer';
		return $installerClass;
	}


	final public static function install(){
		$installerClass = static::getInstaller();
		return (new $installerClass)->run();

	}


	public function run(Event $event = null){
		dd($this);
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
		$controller_filepath =  $this->applicationDir . '/core/MY_Controller.php';

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


	public function web_update_available(){
        $remotehash = @file_get_contents($this->repository_url . '/hash.txt');
        $localhash = @file_get_contents(dirname(__DIR__) . '/hash.txt');
        return trim($remotehash) != trim($localhash);
	}


	private function installController(){
		$filepath =  $this->applicationDir . '/core/MY_Controller.php';
		if(!file_exists($filepath)){
			file_put_contents($filepath, "<?php" . PHP_EOL . PHP_EOL . $this->controller_contents . PHP_EOL);
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

