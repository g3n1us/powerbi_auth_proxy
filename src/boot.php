<?php
namespace BlueRaster\PowerBIAuthProxy;

require(__DIR__ . '/helpers.php');

if(!session_status()) session_start();

// $dotenv_dir = dirname($_SERVER['DOCUMENT_ROOT']);
$dotenv_dir = dirname(__DIR__);

if(file_exists("$dotenv_dir/.env")){
	$dotenv = \Dotenv\Dotenv::create($dotenv_dir);
	$dotenv->load();
}

new SafetyNet;

if(env('APP_ENV') == 'dev'){
	$whoops = new \Whoops\Run;
	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
	$whoops->register();
}

require(__DIR__ . '/routes.php');
