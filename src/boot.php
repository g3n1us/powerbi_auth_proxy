<?php
	namespace BlueRaster\PowerBIAuthProxy;

	require(__DIR__ . '/helpers.php');

	if(!session_status()) session_start();
// 	$dotenv_dir = dirname(getcwd());
	$dotenv_dir = dirname($_SERVER['DOCUMENT_ROOT']);

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
