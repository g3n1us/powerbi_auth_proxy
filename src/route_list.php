<?php
namespace BlueRaster\PowerBIAuthProxy;

use BlueRaster\PowerBIAuthProxy\Admin\AdminRoute;

use BlueRaster\PowerBIAuthProxy\Installers\Installer;

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
		return Utils::getReports();

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
dd($this->nullable, 'd');
		$ok = preg_match('/^secure_embed.*?\.(js|css|js\.map|css\.map)$/', $filename, $match);
		if(!$ok) return false;
		return @file_get_contents(__DIR__."/assets/dist/$filename");
	}


	// responds to the url: /auth_proxy_routes/app_update
	public function app_update(){
        return [
            'update_available' => (new Installer)->web_update_available(),
        ];
	}

}

// responds to the url: /auth_proxy_routes/auth_proxy_admin.html
new AdminRoute;


new DefaultRoute();

