<?php
	namespace BlueRaster\PowerBIAuthProxy;

	require(__DIR__ . '/helpers.php');

	if(!session_status()) session_start();
// 	$dotenv_dir = dirname(dirname(\Composer\Factory::getComposerFile()));
	$dotenv_dir = dirname(getcwd());
	if(file_exists("$dotenv_dir/.env")){
		$dotenv = \Dotenv\Dotenv::createImmutable($dotenv_dir);
		$dotenv->load();
	}

	new SafetyNet;

	if(env('APP_ENV') == 'dev'){
		$whoops = new \Whoops\Run;
		$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		$whoops->register();
	}
