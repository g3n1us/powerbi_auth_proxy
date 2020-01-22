<?php
	
echo "<pre>";

if(empty($_GET['installing'])){
	echo "Installation is required. Please be patient while this completes.<br />";
	echo('<script>window.location.assign("/?installing=true")</script>');
}
else{
	$build_dir = __DIR__.'/build';
	$dir = __DIR__;
	file_put_contents("$build_dir/composer.phar", file_get_contents('https://getcomposer.org/composer-stable.phar')); 
	$output = [
		"Installation complete.\n",
		"Errors (if any) will be reported below.\n",
		"<small style='line-height:1'>",
	];
	exec("php \"$build_dir/composer.phar\" install --no-suggest -d \"$dir\" 2>&1", $output);
	$output[] = "</small>";
	$output[] =  "Install complete, <button onclick='window.location.assign(\"/\")'>click to continue...</button>";
	
	$output = array_map('trim', $output);
	echo(implode("<br />\n", array_filter($output)));	
}

echo "</pre>";
