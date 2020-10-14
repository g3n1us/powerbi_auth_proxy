<?php

namespace BlueRaster\PowerBIAuthProxy\Installers;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use BlueRaster\PowerBIAuthProxy\Command;
use BlueRaster\PowerBIAuthProxy\Utils;


abstract class Installer{


	protected $steps = ['copy_dotenv_example'];

	protected $framework;

	public $installed_with_composer;

	public static function postAutoloadDump(Event $event){
		(new self())->run($event);
	}


	final public function __construct(){
		$this->framework = Utils::getFramework();

    	$this->installed_with_composer = Utils::installedComposer();

	}


	final public static function install(){
		$framework = Utils::getFramework();
		$installerClass = $framework::getInstaller();
		return (new $installerClass)->run();
	}


	abstract protected function getSteps() : array;


	protected function copy_dotenv_example(){
		if(!file_exists(Utils::root_path('.env'))){
			copy(Utils::root_path('.env.example'), Utils::root_path('.env'));
		}
	}


	final public function run(){
		if($this->isInstalled()){
			Command::say("Installation of the PowerBIAuthProxy is complete.");
			exit();
		}

		Command::say("Installation of the PowerBIAuthProxy is not yet complete.");
		Command::say("You will be guided through a few short steps to complete installation.");
		Command::confirm("Would you like to continue?");
		$required_steps = $this->getSteps();

		$total_steps = count($required_steps);
		$current_step = 1;
		foreach($required_steps as $step){
			Command::say("\n*** Step $current_step of $total_steps:\n\n");
			$current_step++;
			$this->{$step}();
		}

	}


	abstract protected function isInstalled() : bool;

}

