<?php
$output = [];
$output[] = '
<style>
[type="hidden"] + br{
	display:none;
}
</style>
';
if(!empty($_GET['configure'])){
	$example = array_map('trim', file(__DIR__.'/.env.example'));
	$config = array_filter(array_map(function($line){
		if(preg_match('/^(.*?)=(.*?)$/', $line, $matches)){
			$key = $matches[1];
			$value = $matches[2];
			$v = isset($_POST['env'][$key]) ? $_POST['env'][$key] : null;
			return [$key, $value, $v];
		}
		else return $line;

	}, $example));

	$config_keys = array_values(array_map(function($v){ return $v[0]; }, array_filter($config, 'is_array')));
	$submitted_env = !empty($_POST['env']) ? array_filter(array_map('trim', $_POST['env'])) : [];

	if(empty(array_diff($config_keys, array_keys($submitted_env)))){
		$env_contents = [];
		foreach($config as $line){
			if(is_array($line)){
				[$key, $value, $v] = $line;
				$env_contents[] = "$key=\"$v\"";
			}
		}
		// put .env one directory above the document root for security purposes
		file_put_contents(dirname($_SERVER['DOCUMENT_ROOT'])."/.env", implode(PHP_EOL, $env_contents));
		$output[] =  "Configuration complete, <button class='btn btn-primary' onclick='window.location.assign(window.location.pathname)'>click to continue...</button>";
	}
	else{
		if(!empty($_POST['env'])) $output[] = '<div class="alert alert-danger">One or more values was missing. All fields are required.</div>';
		$output[] = "<h1>Configuration Required</h1>";
		$output[] = "<h4>Please fill in the following fields:</h4>";
		$output[] = '<div class="container">
						<form class="row" method="post" action="?configure=true">
							<div class="col-md-8">';
		foreach($config as $line){
			if(is_array($line)){
				[$key, $value, $v] = $line;
				$output[] = "$key <br /><input type='text' name='env[$key]' class='form-control' placeholder='$value' value='$v' />";
			}
			else $output[] = $line;
		}
		$output[] = '<br /><button class="btn btn-primary" type="submit">Submit</button></form>';
	}
}


else if(!empty($_GET['installing'])){
	$build_dir = __DIR__.'/build';
	$dir = __DIR__;
	file_put_contents("$build_dir/composer.phar", file_get_contents('https://getcomposer.org/composer-stable.phar'));
	$output[] = "Installation complete.\n";
	$output[] = "Errors (if any) will be reported below.\n";
	$output[] = "<pre><small style='line-height:1'>";
	exec("php \"$build_dir/composer.phar\" install --no-suggest --ignore-platform-reqs -d \"$dir\" 2>&1", $output);
	$output[] = "</small></pre>";
	$output[] =  "Install complete, <button class='btn btn-primary' onclick='window.location.assign(window.location.pathname)'>click to continue...</button>";

}
else{
// 	echo "Installation is required. Please be patient while this completes.<br />";
// 	echo "<button class='btn btn-primary' onclick='window.location.assign(window.location.pathname + \"?installing=true\")'>click to continue...</button>";
// 	echo('<script>window.location.assign("/?installing=true")</script>');
}



$output = array_map('trim', $output);
// return "<pre>" . implode("<br />\n", array_filter($output)) . "</pre>";
return implode("<br />\n", array_filter($output));


