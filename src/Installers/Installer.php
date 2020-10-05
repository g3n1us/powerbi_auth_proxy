<?php

namespace BlueRaster\PowerBIAuthProxy\Installers;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use BlueRaster\PowerBIAuthProxy\Command;
use BlueRaster\PowerBIAuthProxy\Utils;


abstract class Installer{


	protected $steps = [];

	protected $framework;

	public static function postAutoloadDump(Event $event){
		(new self())->run($event);
	}


	final public function __construct(){
		$this->framework = Utils::getFramework();

    	$this->installed_with_composer = basename(dirname(dirname(__DIR__))) == 'vendor';

	}


/*
	final public static function getInstaller(){
		$this->framework = Utils::getFramework();
		$installerClass = __NAMESPACE__ . '\\' . class_basename($framework_name).'Installer';
		return $installerClass;
	}
*/


	final public static function install(){
// 		$installer = new $installerClass;
		$framework = Utils::getFramework();
		$installerClass = $framework::getInstaller();
		dd($installerClass);
// 		return ()->run();
	}


	public function run(){
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


	abstract protected function isInstalled() : boolean;

}

