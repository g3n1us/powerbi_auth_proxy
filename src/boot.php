<?php
namespace BlueRaster\PowerBIAuthProxy;

use Whoops\Handler\PrettyPageHandler;
use Illuminate\Support\Str;

require(__DIR__ . '/helpers.php');

if(!session_status()) session_start();

$dotenv_dir = dirname(__DIR__);

if(file_exists("$dotenv_dir/.env")){
	$dotenv = \Dotenv\Dotenv::create($dotenv_dir);
	$dotenv->load();
}

new SafetyNet;

if(env('APP_ENV') == 'dev'){
	$whoops = new \Whoops\Run;
	$whoops->pushHandler(new PrettyPageHandler);
	$whoops->register();
}


// Artificially set the request method based on _method parameter

$method_string = strtoupper(@$_REQUEST['_method']);
if(in_array($method_string, ['GET', 'POST', 'PUT'])){

	$_SERVER['REQUEST_METHOD'] = $method_string;
	unset($_REQUEST['_method']);
	unset($_POST['_method']);
}

require(__DIR__ . '/route_list.php');
