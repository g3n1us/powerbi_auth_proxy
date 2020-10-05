<?php
if (php_sapi_name() !== 'cli-server'){
	die('not allowed');
}

$src = __DIR__.'/src';
$autoload = __DIR__.'/vendor/autoload.php';
if(!file_exists($autoload)){
	$content = '<div class="alert alert-danger">You must run <pre class="mt-3">composer install</pre> to install your dependencies. Then restart the local web server and refresh this page.</div>';
}
else{
	require($autoload);

	try{
		require_once(__DIR__.'/src/boot.php');
		BlueRaster\PowerBIAuthProxy\Auth::config();
		BlueRaster\PowerBIAuthProxy\Routes::route();
		$content = '<script type="text/javascript" src="/auth_proxy_routes/asset/secure_embed.js"></script>';
	}
	catch(\Exception $e){
	$content = '<div class="alert alert-danger">You must run complete configuration by running the cli setup script.
	<pre class="mt-3">./bin/powerbi-auth-proxy-installer</pre>
	Then restart the local web server and refresh this page.</div>';

	}
}


$html = '
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<style>
	'. file_get_contents(BlueRaster\PowerBIAuthProxy\Utils::public_path('secure_embed.css')) .'
	</style>

  </head>
  <body style="padding:25px" data-pbi-secure-embed-uses-bootstrap>
	'.$content.'
  </body>
</html>';

echo trim($html);
die();

