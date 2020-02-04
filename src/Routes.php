<?php

namespace BlueRaster\PowerBIAuthProxy;

use BlueRaster\PowerBIAuthProxy\Exceptions\MissingConfigException;

require_once(__DIR__.'/boot.php');


class Routes{

	private $auth_proxy;

	public $segments;

	public $argument_string;

	public $query_string;

	public $path;

	private static $mime_set = false;

	public $patterns= [
    	'^\/auth_proxy_routes\/(.*?)$',
    	'proxy' => '.*?arcgis\\/rest.*?$',
        'proxy_other' => '.*?ESRI.*?',
	];

	public function _route($patterns = []){
    	$this->path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    	$this->query_string = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

        $this->patterns = array_merge($this->patterns, $patterns, $this->get_patterns());

        $matched = false;
        $method = null;
        foreach($this->patterns as $possible_method => $pattern){
            $matched = preg_match('/'.$pattern.'/i', $this->path, $matches);
            if($matched) {
                if(is_string($possible_method)) $method = $possible_method;
                break;
            }
        }

		if($matched){
			$this->auth_proxy = Auth::get_instance();

			$this->segments = explode('/', @$matches[1]);

			$method = $method ?? array_shift($this->segments);

			$this->argument_string = empty($this->segments) ? null : implode('/', $this->segments);

			if(method_exists($this, $method)){
				$response = call_user_func_array([$this, $method], $this->segments);
				if($response !== false){
					self::set_mime(count($this->segments) ? $this->segments[0] : null);
					if(is_array($response)) $response = json_encode($response);
					echo $response;
					exit();
				}
			}
		}
	}


	private function get_patterns(){
    	$patterns = [];
    	$i = 0;

        $continuing = true;
        while($continuing){
            $test = env("PATTERNS_$i");
            if($test){
                $patterns[] = $test;
            }
            else{
                $continuing = false;
            }
            $i++;
        }
        return $patterns;
	}

    public static function route(){
		try{
			(new self)->_route();
		}
		catch(\Exception $e){
			if(method_exists($e, 'handle')){
				$e->handle();
			}
			else{
				throw $e;
			}
		}
    }


	private static function set_mime($filename = null){
    	if(self::$mime_set) return;
    	self::$mime_set = true;
		$ok = preg_match('/^.*?\.(js|css|html)$/', $filename, $match);
		if(!$ok) $mime = 'application/json';
		else{
			$mimes = [
				'js' => 'application/javascript',
				'css' => 'text/css',
				'html' => 'text/html',
			];
			$mime = $mimes[$match[1]];
		}
		header("Content-Type: $mime");
	}


    private function proxy(){
        $esri_endpoint = env('ESRI_ENDPOINT', 'https://services7.arcgis.com');

        $query = $_GET;

        $query['token'] = $this->auth_proxy->getEsriEmbedToken();

        ksort($query);

        $url = $esri_endpoint . $this->path . '?' . http_build_query($query);
    	file_put_contents(__DIR__.'/origins.txt', 'url = '.$url . PHP_EOL, FILE_APPEND);

        self::set_mime('.' . @$query['f'] ?? 'html');

        header('Access-Control-Allow-Origin: *');

        return file_get_contents($url);
    }


    private function proxy_other(){
        $esri_endpoint = env('ESRI_ENDPOINT', 'https://services7.arcgis.com');
        $url = $esri_endpoint . $this->path;
        self::set_mime($this->path);
        return file_get_contents("$url");

    }



	// responds to the url: /auth_proxy_routes/embed_data
	private function embed_data(){
		return [
			'reports' => $this->auth_proxy->getReports(),
			'group_id' => $this->auth_proxy->getGroupId(),
			'selected_reports' => $this->auth_proxy->getSelectedReports(),
		];
	}


	// responds to the url: /auth_proxy_routes/report_embed/{report_id}
	private function report_embed($report_id = false){
		if($report_id === false) return false;
		$embed_token = $this->auth_proxy->getEmbedToken($report_id);
		return ['embed_token' => $embed_token, 'report_id' => $report_id];
	}


	// responds to the url: /auth_proxy_routes/esri_embed/{report_id}
	private function esri_embed($report_id = false){
		if($report_id === false) return false;
		$embed_token = $this->auth_proxy->getEsriEmbedToken($report_id);
		return ['access_token' => $embed_token, 'report_id' => $report_id];
	}



	// responds to the url: /auth_proxy_routes/asset/{secure_embed.js|secure_embed.css}
	private function asset($filename){
		$ok = preg_match('/^secure_embed\.(js|css)$/', $filename, $match);
		if(!$ok) return false;
		return @file_get_contents(__DIR__."/assets/dist/$filename");
	}


	// responds to the url: /auth_proxy_routes/app_update
	private function app_update(){
        return [
            'update_available' => (new Installer)->web_update_available(),
        ];
	}



}
