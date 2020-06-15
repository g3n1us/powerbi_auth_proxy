<?php
namespace BlueRaster\PowerBIAuthProxy\Admin;
	
use BlueRaster\PowerBIAuthProxy\Route;
use BlueRaster\PowerBIAuthProxy\Auth;

use BlueRaster\PowerBIAuthProxy\Utils;
use BlueRaster\PowerBIAuthProxy\Views\View;
use BlueRaster\PowerBIAuthProxy\DB;

use ZipArchive;
	
class AdminRoute extends Route{
	
	public $pattern = '^\/auth_proxy_routes\/auth_proxy_admin\.html$';
	
	public $gates = ['auth_proxy_admin_gate'];
	
	public $callback = 'handle';
	
    private $repository_url = "https://powerbi-auth-proxy-downloads.s3.us-east-1.amazonaws.com";
	
	
	
	public function handle(){
		
		if(strtolower(@$_SERVER['REQUEST_METHOD']) === 'post'){
			return @$_POST['application_update'] ? $this->application_update() : $this->update_reports();
		}
		
		return $this->display();
	}
	
	public function display(){
		return new View(__DIR__.'/template.php', [
			'data' => collect(['reports' => Utils::getReports()->merge([['id' => null, 'type' => null, 'name' => null]])]),
			
		]);
	}
	
	
	public function update_reports(){
		$reports = collect($_POST['reports'])->values()->map('collect')->map(function($report){
			return $report->map('head');
		});
		Utils::save('reports', $reports);

		return '<script>
		localStorage._auth_proxy_message = "Saved";
		window.location.assign("/auth_proxy_routes/auth_proxy_admin.html?v='.time().'");
		</script>';
	}
	
	
	public function application_update(){
		$results = null;
		$errors = null;
        file_put_contents(Utils::root_path('/current.zip'), Utils::get_http($this->repository_url . '/current.zip'));
        file_put_contents(Utils::root_path('/hash.txt'), Utils::get_http($this->repository_url . '/hash.txt'));

        $zip = new ZipArchive;
        if ($zip->open(Utils::root_path('/current.zip')) === TRUE) {
            $zip->extractTo(Utils::root_path());
            $zip->close();
            $results = "Update completed successfully.";
        } else {
            $errors = "An error occurred with unzipping the update/install package";
        }
		return '<script>
		localStorage._auth_proxy_message = "'.$results.'";
		localStorage._auth_proxy_error = "'.$errors.'";
		window.location.assign("/auth_proxy_routes/auth_proxy_admin.html?v='.time().'#available-updates");
		</script>';
	}
	
	
	public function auth_proxy_admin_gate($app){
		$current_user = Auth::getCurrentUser();
		$admin_emails = clean_array_from_string(Auth::config('auth_proxy_admins', ''));
		
		return in_array($current_user->getEmail(), $admin_emails);
	}

}	


