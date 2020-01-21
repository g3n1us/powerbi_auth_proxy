<?php

$src = __DIR__.'/src';
$autoload = __DIR__.'/vendor/autoload.php';
if(!file_exists($autoload)){
	require('install.php');
}
else{
	require($autoload);

	echo BlueRaster\PowerBIAuthProxy\Routes::route();
		
		$html = '
	<!DOCTYPE html>
	<html lang="en">
	  <head>
	    <meta charset="utf-8">
	  </head>
	  <body>	
	  '.getcwd().'
		<script type="text/javascript" src="/auth_proxy_routes/asset/secure_embed.js"></script>	
	  </body>
	</html>
		';
	
		echo trim($html);	
}

$instance = '';
function get_instance() use(&$instance){
	return $instance;
}

class Ci_Controller{
	
}
