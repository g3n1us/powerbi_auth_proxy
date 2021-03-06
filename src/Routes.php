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

	private $method;

	private static $mime_set = false;

	public static $routes = [];

	public $current_route;

	public $patterns= [
    	'proxy' => '.*?arcgis\\/rest.*?$',
    	'proxy_sharing' => '.*?sharing\\/rest.*?$',
    	'proxy_dashboard' => '^\\/apps\\/opsdashboard\\/.*?$',
        'proxy_other' => '.*?ESRI.*?',
	];

	public function _route($patterns = []){
    	$this->path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    	$this->query_string = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        $this->patterns = array_merge($this->get_patterns(), $this->patterns, $patterns);
        $matched = false;
        $method = null;
        $current_route = null;
        foreach($this->patterns as $possible_method => $pattern){
            $matched = !!preg_match('/'.$pattern.'/i', $this->path, $matches);
            if($matched) {

                if($pattern instanceof Route){
                    $current_route = $pattern;
                    $current_route->router = $this;
                    $this->current_route = $current_route;
                    $method = $current_route->callback;
                }
                else if(is_string($possible_method)) {
                    $this->current_route = new Route($pattern);
                    $this->current_route->router = $this;
                    $method = $possible_method;
                    $current_route = $this->current_route;
                }
                break;
            }
        }

		if($matched){
			$this->auth_proxy = Auth::get_instance();

			$this->segments = array_filter(explode('/', @$matches[1]));

			$method = $this->method = $method ?? array_shift($this->segments);

			$this->argument_string = empty($this->segments) ? null : implode('/', $this->segments);

            $response = false;

            $this->call_gates();

			if(is_callable($method)){
    			$args = array_filter(array_merge($this->segments, [$this]));
    			$response = call_user_func_array($method, $args);
			}

			else if(method_exists($current_route, $method)){
    			$args = array_filter(array_merge($this->segments, [$this]));
				$response = call_user_func_array([$current_route, $method], $args);
			}

			else if(method_exists($this, $method)){
				$response = call_user_func_array([$this, $method], $this->segments);
			}

			else if(preg_replace('/[^a-z]/', '', $method) === 'proxy'){
				$response = call_user_func_array([$this, 'proxy'], $this->segments);
			}

			if($response !== false){
				self::set_mime(count($this->segments) ? $this->segments[0] : $this->path);

				if(is_array($response)) echo collect($response);
				else echo $response;
				exit();
			}

		}
	}


	public function call_gates(){
    	$gates = array_map(function($v){
        	if(is_callable($v)){

            	return $v($this);
        	}
        	else if(method_exists($this->current_route, $v)){

            	return $this->current_route->{$v}($this);
        	}
        	else if(is_callable(Auth::config($v))){
            	return Auth::config($v)($this);
        	}

    	}, $this->current_route->gates);

    	$passes = count($gates) === count(array_filter($gates));
    	if(!$passes){
            if(!Auth::config('auth_proxy_gate')){
                throw new MissingConfigException("The default gate: 'auth_proxy_gate' is missing. This should be set in the configuration for your framework as a Closure.");
            }
        	return UserProxy::abort();
    	}
	}


	private function get_patterns(){

        $patterns = static::$routes;
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
			$router = new self;
			$router->_route();
		}
		catch(\Exception $e){
			if($router->current_route->isNullable()){
				self::set_mime($router->path);
				die('');
			}
			else if(method_exists($e, 'handle')){
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
		$ok = preg_match('/^.*?\.(js|css|html|pbf)$/', $filename, $match);

		if(!$ok) $mime = 'application/json';
		else{
			$mimes = [
				'js' => 'application/javascript',
				'css' => 'text/css',
				'html' => 'text/html',
				'pbf' => 'application/octet-stream'
			];
			$mime = $mimes[$match[1]];
		}
		header("Content-Type: $mime");
	}


    private function proxy($endpoint = null){
	    if($endpoint)
	        $esri_endpoint = $endpoint;
        else
	        $esri_endpoint = Auth::config('esri_endpoint', 'https://services7.arcgis.com');

        $query = $_GET;

        $query['token'] = $this->auth_proxy->getEsriEmbedToken();

        ksort($query);

        $url = $esri_endpoint . $this->path . '?' . http_build_query($query);

        self::set_mime('.' . @$query['f'] ?? 'html');

        header('Access-Control-Allow-Origin: *');

        return guzzle_get_contents($url);
    }


    private function proxy_sharing(){
	    return $this->proxy(Auth::config('esri_dashboard_endpoint', 'https://www.arcgis.com'));
    }

    private function proxy_dashboard(){
	    self::set_mime($this->path);
	    $content = $this->proxy(Auth::config('esri_dashboard_endpoint', 'https://www.arcgis.com'));

	    return $content;
    }

    private function proxy_other(){
        $esri_endpoint = env('ESRI_ENDPOINT', 'https://services7.arcgis.com');
        $url = $esri_endpoint . $this->path;
        self::set_mime($this->path);
        return guzzle_get_contents("$url");
    }

}
