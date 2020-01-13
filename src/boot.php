<?php
	namespace BlueRaster\PowerBIAuthProxy;

	require(__DIR__ . '/helpers.php');

	if(!session_status()) session_start();
	$dotenv = \Dotenv\Dotenv::createImmutable(dirname(\Composer\Factory::getComposerFile()));
	$dotenv->load();

	if(env('APP_ENV') == 'dev'){
		$whoops = new \Whoops\Run;
		$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		$whoops->register();
	}
