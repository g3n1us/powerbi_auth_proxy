<?php
namespace BlueRaster\PowerBIAuthProxy;


use BlueRaster\PowerBIAuthProxy\Installers\Installer;

use BlueRaster\PowerBIAuthProxy\Utils;

class DefaultRoute extends Route{


    public $report_id;

    public $method_name;

    protected $nullable_methods = ["asset"];


    public function __construct(){
        parent::__construct('^\/auth_proxy_routes\/(.*?)$', ['auth_proxy_gate'], null);
        $path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $matched = preg_match('/^\/auth_proxy_routes\/(.*?)\/(.*?)$/i', $path, $matches);
        if($matched){
            $this->method_name = @$matches[1];
            $this->report_id = @$matches[2];
        }

        $this->nullable = in_array($this->method_name, $this->nullable_methods);
    }

	// responds to the url: /auth_proxy_routes/embed_data
	public function embed_data(){
		$reports = DB::get('reports');
		if(empty($reports)){
	    	$reports_string = Auth::config('selected_reports');

			$selected_reports = array_map(function($v){
				return Embed::createFromString($v);
			}, Utils::clean_array_from_string($reports_string));
		}
		else{
			$selected_reports = $reports->map(function($v){
				return new Embed($v);
			});
		}

		return collect($selected_reports);
	}

	// responds to the url: /auth_proxy_routes/current_user
	public function current_user(Routes $routes){
		return Auth::getCurrentUser();
	}



	// responds to the url: /auth_proxy_routes/report_embed/{report_id}
	public function report_embed($report_id = false, $router = null){
		if($report_id === false) return false;
		$auth_proxy = Auth::get_instance();
		$embed_token = $auth_proxy->getEmbedToken($report_id);
		return ['embed_token' => $embed_token, 'report_id' => $report_id, 'group_id' => Auth::config('group_id')];
	}


	// responds to the url: /auth_proxy_routes/esri_embed/{report_id}
	// deprecated: will not allow access without login
	public function esri_embed($report_id = false){
		if($report_id === false) return false;
		$auth_proxy = Auth::get_instance();
		$embed_token = $auth_proxy->getEsriEmbedToken($report_id);
		return ['access_token' => $embed_token, 'report_id' => $report_id];
	}



	// responds to the url: /auth_proxy_routes/asset/{secure_embed.js|secure_embed.css}
	public function asset($filename){
		$this->nullable = true;

		$ok = preg_match('/^secure_embed.*?\.(js|css|js\.map|css\.map)$/', $filename, $match);
		if(!$ok) return false;
		return @file_get_contents(Utils::public_path($filename));
	}


	// responds to the url: /auth_proxy_routes/app_update
	public function app_update(){
        return [
            'update_available' => (new Installer)->web_update_available(),
        ];
	}

}

