<?php

namespace G3n1us\AuthProxy;

class Routes{

	private $auth_proxy;

	public function __construct(){

	}

	public function _route(){
		$matched = preg_match('/^\/auth_proxy_routes\/(.*?)$/', $_SERVER['REQUEST_URI'], $matches);
		if($matched){
			$this->auth_proxy = Auth::get_instance();
// 			dd( $this->auth_proxy->getCi()->user );
			$segments = explode('/', $matches[1]);
			$method = array_shift($segments);

			if(method_exists($this, $method)){
				$response = call_user_func_array([$this, $method], $segments);
				if($response !== false){
					self::set_mime(count($segments) ? $segments[0] : null);
					echo $response;
					exit();
				}
			}
		}
	}

    public static function route(){
		(new self)->_route();
    }

	private static function set_mime($filename = null){
		$ok = preg_match('/^.*?\.(js|css)$/', $filename, $match);
		if(!$ok) $mime = 'application/json';
		else{
			$mimes = [
				'js' => 'application/javascript',
				'css' => 'text/css',
			];
			$mime = $mimes[$match[1]];
		}
		header("Content-Type: $mime");
	}


	// responds to the url: /auth_proxy_routes/embed_data
	private function embed_data(){
		return json_encode([
			'reports' => $this->auth_proxy->getReports(),
			'group_id' => $this->auth_proxy->getGroupId(),
			'selected_reports' => $this->auth_proxy->getSelectedReports(),
		]);
	}


	// responds to the url: /auth_proxy_routes/report_embed/{report_id}
	private function report_embed($report_id = false){
		if($report_id === false) return false;
		$embed_token = $this->auth_proxy->getEmbedToken($report_id);
		return json_encode(['embed_token' => $embed_token, 'report_id' => $report_id]);
	}


	// responds to the url: /auth_proxy_routes/asset/{secure_embed.js|secure_embed.css}
	private function asset($filename){
		$ok = preg_match('/^secure_embed\.(js|css)$/', $filename, $match);
		if(!$ok) return false;
		return @file_get_contents(__DIR__."/assets/dist/$filename");
	}
}
