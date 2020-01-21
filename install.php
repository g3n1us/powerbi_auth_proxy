<?php
echo "Installation required!!\n\n";
echo "Please be patient for a few moments...";
$build_dir = __DIR__.'/build';
$dir = __DIR__;
file_put_contents("$build_dir/composer.phar", file_get_contents('https://getcomposer.org/composer-stable.phar')); 
exec("php $build_dir/composer.phar install -d $dir 2>&1", $output);
echo(implode("<br />\n", $output));
// echo "<script>window.location.reload()</script>";

