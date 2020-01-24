<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');


class CodeigniterPowerBIAuthProxyInstaller{

    private $repository_url = "https://auth-proxy-downloader.dev.also-too.com";

    private $install_dir = __DIR__ . '/application/third_party/powerbi_auth_proxy';

    private $output = [];

    private $errors = [];

    public function __construct(){
        $this->check_requirements();

        if($this->should_install()){
            $this->install();
            $this->post_install();
        }
    }

    private function install(){
        @mkdir($this->install_dir);
        copy($this->repository_url . '/current.zip', $this->install_dir . '/current.zip');
        copy($this->repository_url . '/hash.txt', $this->install_dir . '/hash.txt');

        $zip = new ZipArchive;
        if ($zip->open($this->install_dir . '/current.zip') === TRUE) {
            $zip->extractTo($this->install_dir);
            $zip->close();
            $this->output[] = "<div class='alert alert-info'>Update successfully unzipped.</div>";
        } else {
            $this->errors[] = "<div class='alert alert-danger'>An error occurred with unzipping the update/install package</div>";
        }

    }

    private function post_install(){
        unlink($this->install_dir . '/current.zip');
    }


    private function should_install(){
        $proceed = false;
        if(!is_dir($this->install_dir)) $proceed = true;
        else{
            $remotehash = @file_get_contents($this->repository_url . '/hash.txt');
            $localhash = @file_get_contents($this->install_dir . '/hash.txt');
            $proceed = trim($remotehash) != trim($localhash);
        }

        if(!$proceed) $this->output[] = "<div class='alert alert-success'>The application is up-to-date</div>";
        return $proceed;
    }

    private function check_requirements(){
        $proceed = true;

        // are directories writable
        @mkdir($this->install_dir);
        if(!is_writable($this->install_dir)){
            $this->errors[] = "<div class='alert alert-danger'>The installation directory (".$this->install_dir.") is not writable.</div>";
            $proceed = false;
        }
        else{
            $this->output[] = "<div class='alert alert-success'>Installation directory writable</div>";
        }

        // is zip extension installed
        if(!class_exists('ZipArchive')){
            $this->errors[] = "<div class='alert alert-danger'>Zip extension is missing</div>";
            $proceed = false;
        }
        else{
            $this->output[] = "<div class='alert alert-success'>Zip extension installed</div>";
        }

        return $proceed;
    }

    public function __toString(){
        return implode(PHP_EOL, $this->errors) . implode(PHP_EOL, $this->output);
    }

}


$content = new CodeigniterPowerBIAuthProxyInstaller;



$html = '
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">
  </head>
  <body style="padding:25px">
	<script
	  src="https://code.jquery.com/jquery-3.4.1.min.js"
	  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
	  crossorigin="anonymous"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>

	'.$content.'
  </body>
</html>';

echo trim($html);
die();
