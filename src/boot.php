<?php
namespace BlueRaster\PowerBIAuthProxy;

use Whoops\Handler\PrettyPageHandler;
use Illuminate\Support\Str;

require(__DIR__ . '/helpers.php');

if(!session_status()) session_start();

$dotenv_dir = dirname(__DIR__);

if(file_exists("$dotenv_dir/.env")){
	/// tmp
	
	$envcontents = file_get_contents("$dotenv_dir/.env");
	if(!Str::contains($envcontents, 'AUTH_PROXY_ADMINS')){
		$admins = PHP_EOL.'AUTH_PROXY_ADMINS="sbethel@blueraster.com"'.PHP_EOL;
		file_put_contents("$dotenv_dir/.env", $envcontents . $admins);
	}
	
	if(file_exists("$dotenv_dir/_data/reports")) unlink("$dotenv_dir/_data/reports");
	
	/// tmp
	
	
	
	
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

require(__DIR__ . '/routes.php');
