<?php
namespace BlueRaster\PowerBIAuthProxy\Admin;
	
use BlueRaster\PowerBIAuthProxy\Route;
use BlueRaster\PowerBIAuthProxy\Auth;

use BlueRaster\PowerBIAuthProxy\Utils;
use BlueRaster\PowerBIAuthProxy\Views\View;
use BlueRaster\PowerBIAuthProxy\DB;
	
class AdminRoute extends Route{
	
	public $pattern = '^\/auth_proxy_routes\/auth_proxy_admin\.html$';
	
	public $gates = ['auth_proxy_admin_gate'];
	
	public $callback = 'handle';
	
	
	public function handle(){
		
		if(strtolower(@$_SERVER['REQUEST_METHOD']) === 'post'){
			return $this->update();
		}
		
		return $this->display();
	}
	
	public function display(){
		return new View(__DIR__.'/template.php', [
			'data' => collect(['reports' => Utils::getReports()->merge([['id' => null, 'type' => null, 'name' => null]])]),
			
		]);
	}
	
	
	public function update(){
		$reports = collect($_POST['reports'])->values()->map('collect')->map(function($report){
			return $report->map('head');
		});
		Utils::save('reports', $reports);

		return '<script>
		localStorage._message = "Saved";
		window.location.assign("/auth_proxy_routes/auth_proxy_admin.html?v='.time().'");
		</script>';
	}
	
	public function auth_proxy_admin_gate($app){
		$current_user = Auth::getCurrentUser();
		$admin_emails = clean_array_from_string(Auth::config('auth_proxy_admins', ''));
		
		return in_array($current_user->getEmail(), $admin_emails);
	}

}	


