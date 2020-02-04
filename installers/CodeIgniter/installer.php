<?php

class CodeigniterPowerBIAuthProxyInstaller{

    private $repository_url = "https://auth-proxy-downloader.dev.also-too.com";

    private $install_dir = '/application/third_party/powerbi_auth_proxy';

    private $installer_parent_dirname = 'powerbi_auth_proxy_updater';

    private $output = [];

    private $errors = [];

    private $results = ['<hr style="margin: 40px auto" />'];

    private $title = '';

    private $config = [
        'subclass_prefix' => null,
    ];

    public function __construct(){
        define('BASEPATH', '');
        try{
            require(dirname(__DIR__) . '/application/config/config.php');
        }
        catch(\Exception $e){
            die(var_dump($e));
        }

        $this->ci_config($config);

        $this->install_dir = dirname(__DIR__) . $this->install_dir;

        if(!$this->preflight()) return;

        $requirements_ok = $this->check_requirements();
        if(!empty($_POST['continue'])){
            $this->title = "";
            if($requirements_ok && $this->should_install(true)){
                $this->install();
            }

        }
        else if(empty($this->errors)){
            if($this->should_install()){
                $this->title = "Review the information below and click 'Continue' to proceed with the installation or update";
                $this->results[] = '<button type="submit" name="continue" value="true" class="btn btn-primary">Continue?</button>';
            }
        }
        else{
            $this->title = "<span class='text-danger'>Please correct the issues listed below</span>";
        }

        $this->post_install();

    }


    private function set_config(){
        if(!file_exists($this->install_dir . '/vendor/autoload.php')) return false;
        require $this->install_dir . '/vendor/autoload.php';

        try{
            BlueRaster\PowerBIAuthProxy\Auth::config();
        }
        catch(Exception $e){
            $_GET['configure'] = true;
            $this->results[] = require $this->install_dir . '/install.php';
            return false;
        }

        return true;
    }


    private function ci_config(Array $config){
        $this->config['subclass_prefix'] = $config['subclass_prefix'];


    }

    private function install(){
        copy($this->repository_url . '/current.zip', $this->install_dir . '/current.zip');
        copy($this->repository_url . '/hash.txt', $this->install_dir . '/hash.txt');

        $zip = new ZipArchive;
        if ($zip->open($this->install_dir . '/current.zip') === TRUE) {
            $zip->extractTo($this->install_dir);
            $zip->close();
            $this->results[] = "<div class='alert alert-info'>Update successfully unzipped into the installation directory.</div>";
        } else {
            $this->errors[] = "<div class='alert alert-danger'>An error occurred with unzipping the update/install package</div>";
        }


        $app_path = dirname(dirname($this->install_dir));

        // create controller file
        $controller_content = 'require_once APPPATH . \'/third_party/powerbi_auth_proxy/vendor/autoload.php\';' .PHP_EOL . PHP_EOL;
        $controller_content .= 'BlueRaster\\PowerBIAuthProxy\\Routes::route();';
        $controllerpath = $app_path . '/core/' . $this->config['subclass_prefix'] . 'Controller.php';
		file_put_contents($controllerpath, "<?php" . PHP_EOL . $controller_content . PHP_EOL);


        if(empty($this->errors)){
            $this->results[] = '<h3 class="text-success">Installation/Update Complete.</h3>';
        }
    }

    private function post_install(){
        @unlink($this->install_dir . '/current.zip');


        $config_complete = $this->set_config();


        if($config_complete){
//             file_put_contents(__DIR__.'/.htaccess', 'deny from all' . PHP_EOL);
            // test that installer is no longer available via the web
            $uri = @$_SERVER['HTTP_REFERER'];
            $contents = !!@file_get_contents($uri);
            if($contents){
                $this->results[] = '<div class="text-danger">ERROR - This script should no longer be accessible from the web for security purposes.</div>';
            }
        }

    }


    private function preflight(){
        $folder = $this->installer_parent_dirname;
        $thisfilename = basename(__FILE__);
        $scriptdirname = basename(dirname(__FILE__));

        if($scriptdirname != $this->installer_parent_dirname){
            $this->results[] = '<div class="text-danger">ERROR</div>';
            $this->results[] = "This installer script (<code>$thisfilename</code>) must be placed inside a folder named: <code>$folder</code> in the root folder of your website.";
            return false;
        }



        return true;
    }


    private function check_requirements(){
        $proceed = true;

        // are directories writable
        if(!empty($_POST['create_install_dir']))
            @mkdir($this->install_dir);

        if(!is_writable($this->install_dir)){
            $me = function_exists('shell_exec') ? shell_exec('whoami') : "web user cannot be determined automatically";
            $this->errors[] = "<div class='alert alert-danger'>The installation directory (".$this->install_dir.") does not exist or is not writable.</div>
            <div class='well'>Create a folder at <code>".$this->install_dir."</code> and ensure that the user: <code>$me</code>can write to it.
            Click <button type='submit' name='create_install_dir' value='true' class='btn btn-primary'>HERE</button> to attempt to do this automatically.
            </div>
            ";
            $proceed = false;
        }
        else{
            $this->output[] = "<div class='alert alert-success'><i class='glyphicon glyphicon-ok'></i> Installation directory (".$this->install_dir.") exists and is writable</div>";
        }

        // is zip extension installed
        if(!class_exists('ZipArchive')){
            $this->errors[] = "<div class='alert alert-danger'><i class='glyphicon glyphicon-remove'></i> Zip extension is missing</div>";
            $proceed = false;
        }
        else{
            $this->output[] = "<div class='alert alert-success'><i class='glyphicon glyphicon-ok'></i> Zip extension installed</div>";
        }

        if(!$proceed){
            $this->results[] = "<div class='alert alert-danger'><i class='glyphicon glyphicon-remove'></i> There are ". count($this->errors) ." issue(s) that must be fixed before proceeding.</div>";
        }
        return $proceed;
    }

    private function should_install($quiet = false){
        $proceed = false;
        $step = false;
        if(!is_dir($this->install_dir . '/vendor')) {
            $proceed = true;
            $step = 'Application not found, so it will be installed';
        }
        else{
            $remotehash = @file_get_contents($this->repository_url . '/hash.txt');
            $localhash = @file_get_contents($this->install_dir . '/hash.txt');
            $compared = trim($remotehash) != trim($localhash);
            if($compared) $step = 'Application exists but an update is available, so it will be updated';
            $proceed = $compared;
        }

        if(!$quiet){
            if(!$proceed) {
                $this->results[] = "<div class='alert alert-success'>The application is already installed and is up-to-date</div>";
            }
            else{
                $this->results[] = "<div class='alert alert-warning'>$step</div>";
            }
        }
        return $proceed;
    }


    public function __toString(){
        $title = '<h4 class="text-muted">' .$this->title. '</h4>';
        return $title . implode(PHP_EOL, $this->errors) . implode(PHP_EOL, $this->output) . implode(PHP_EOL, $this->results);
    }

}

if(!function_exists('dd')){

    function dd($val){
        die(var_dump($val));
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
        <a href="/">
          <img src="https://docs.microsoft.com/bs-latn-ba/azure/power-bi-embedded/media/index/power-bi-logo.svg" width="200" height="150">
        </a>
        <h1>PowerBI Auth Proxy Installer/Updater</h1>
        <form method="post">
        '.$content.'
        </form>
    </body>
</html>';

echo trim($html);
die();
