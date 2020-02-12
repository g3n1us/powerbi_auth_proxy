<?php

namespace BlueRaster\PowerBIAuthProxy;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;

use BlueRaster\PowerBIAuthProxy\Exceptions\MissingConfigException;

// require_once(__DIR__.'/boot.php');

class Auth{

	private $embed_tokens = [];

	private $oauth_token;

	private $username;

	private $password;

	private $application_id;

	private $application_secret;

	private $group_id;

	private $selected_reports;

	private $esri_client_id;

	private $esri_client_secret;

	private static $instance = false;

	private static $framework;

	public static function get_instance(){
		if(!self::$instance) self::$instance = new self;
		return self::$instance;
	}

	public function __construct(){
		if(static::$instance){
			throw new \Exception("Double Instantiation error");
		}
		static::$framework = $this->register_framework();

		// UserProxy handles middleware functions.
		// Aborts the request if authorization is not valid
		UserProxy::handle(static::$framework->getUserProvider());


        $prefix = static::$framework::getConfigPrefix();
		foreach(self::getDefaultConfig() as $key => $value){
    		$key = str_replace($prefix, '', $key);
			$this->{$key} = static::config($key);
		}
	}

	public static function getCurrentUser(){
    	static::get_instance();
    	return static::$framework->getUserProvider()->getUser();
	}

    private static function getDefaultConfig(){
        $prefix = static::$framework::getConfigPrefix();
	    return [
		    "{$prefix}username" => env('USERNAME'),
		    "{$prefix}password" => env('PASSWORD'),
		    "{$prefix}application_id" => env('APPLICATION_ID'),
		    "{$prefix}application_secret" => env('APPLICATION_SECRET'),
		    "{$prefix}group_id" => env('GROUP_ID'),
		    "{$prefix}selected_reports" => env('SELECTED_REPORTS'),
		    "{$prefix}esri_client_id" => env('ESRI_CLIENT_ID'),
		    "{$prefix}esri_client_secret" => env('ESRI_CLIENT_SECRET'),
		    "{$prefix}accepted_referrers" => env('ACCEPTED_REFERRERS'),
	    ];
    }

	public static function config($key = null, $default = null){
    	$prefix = static::$framework::getConfigPrefix();

	    $default_config = self::getDefaultConfig();

    	if(method_exists(static::$framework, 'getConfig')){
        	$config = array_merge($default_config, static::$framework->getConfig());
    	}
    	else{
    	    $config = $default_config;
    	}

	    if(empty($config["{$prefix}username"]) || empty($config["{$prefix}password"]) || empty($config["{$prefix}application_id"]) || empty($config["{$prefix}application_secret"]) || empty($config["{$prefix}group_id"]) || empty($config["{$prefix}selected_reports"])){
		    throw new MissingConfigException;
	    }

        if($key){
            return @$config[$prefix.$key] ?? @$config[$key] ?? $default;
        }
	    return $config;
	}

	private function register_framework(){
		foreach(Filesystem::list_classes('Frameworks') as $class){
			$class = "$class";
			if($class::test()){
				return new $class;
			}
		}
	}


	public function getAuthToken(){
		if(!$this->oauth_token){
			$client = new GuzzleClient;
			try{
				$res = $client->post('https://login.microsoftonline.com/common/oauth2/token', [
					'headers' => [
						'Accept'     => 'application/json',
					],
					'form_params' => [
					    'client_id'          => $this->application_id,
					    'client_secret'      => $this->application_secret,
					    'resource' => 'https://analysis.windows.net/powerbi/api',
					    'grant_type' => 'password',
					    'scope' => 'openid',
					    'username' => $this->username,
					    'password' => $this->password,
					],
					'stream' => false,
					'expect' => 'json',
					'synchronous' => true,
				]);

				$body = $res->getBody();
				$json = '';
				while (!$body->eof()) {
				    $json .= $body->read(1024);
				}

				$token_data = json_decode($json, true);
				$this->oauth_token = $token_data['access_token'];


			}
			catch(\Exception $e){
				// dd($e->getMessage());
			}

		}

		return $this->oauth_token;
	}

	public function getGroupId(){
		return $this->group_id;
	}

	public function getSelectedReports(){
		$selected_reports = array_map(function($v){
			[$id, $name, $type] = array_merge(explode('|', trim($v)), [null, null, null]);

			return ['id' => $id, 'name' => $name, 'type' => $type ?? 'power_bi'];
		}, explode(',', $this->selected_reports));

		return $selected_reports;
	}

	public function getReports(){
		$guzzle = new GuzzleClient(['base_uri' => 'https://api.powerbi.com']);
		$token_string = $this->getAuthToken();
		$config =   [
						'headers' => [
							'Authorization' => "Bearer $token_string",
							'Content-Type' => 'application/json; charset=utf-8',
							'Accept' => 'application/json',
						],
						'body' => '{"accessLevel": "View", "allowSaveAs": "false"}',
						'debug'   => true,
					];

		$group_id = $this->group_id;
		$path = "https://api.powerbi.com/v1.0/myorg/groups/$group_id/reports";
		$request = new Request('GET', $path, $config['headers'], $config['body']);
		$response = $guzzle->send($request);

		$body = $response->getBody();
		$json = '';
		while (!$body->eof()) {
		    $json .= $body->read(1024);
		}

		$reports = json_decode($json, true);
		return $reports['value'];
	}

	public function getEmbedToken($report_id){
		if(!isset($this->embed_tokens[$report_id])){
			$guzzle = new GuzzleClient(['base_uri' => 'https://api.powerbi.com']);
			$token_string = $this->getAuthToken();

			$config =   [
							'headers' => [
								'Authorization' => "Bearer $token_string",
								'Content-Type' => 'application/json; charset=utf-8',
								'Accept' => 'application/json',
							],
							'body' => '{"accessLevel": "View", "allowSaveAs": "false"}',
							'debug'   => true,
						];
			$group_id = $this->group_id;
			$path = "https://api.powerbi.com/v1.0/myorg/groups/$group_id/reports/$report_id/GenerateToken";
			$request = new Request('POST', $path, $config['headers'], $config['body']);
			$response = $guzzle->send($request);

			$body = $response->getBody();
			$json = '';
			while (!$body->eof()) {
			    $json .= $body->read(1024);
			}

			$token_data2 = json_decode($json, true);

			$this->embed_tokens[$report_id] = $token_data2['token'];
		}


		return $this->embed_tokens[$report_id];
	}


	public function getEsriEmbedToken($report_id = null){
		$guzzle = new GuzzleClient();
		$params = [
			'client_id' => $this->esri_client_id,
			'client_secret' => $this->esri_client_secret,
			'grant_type' => 'client_credentials',
			'f' => 'json'
		];

		$path = "https://www.arcgis.com/sharing/oauth2/token/";
		$response = $guzzle->post($path, [
            'form_params' => $params
        ]);

		$body = $response->getBody();
		$json = '';
		while (!$body->eof()) {
		    $json .= $body->read(1024);
		}

		$token_data = json_decode($json, true);

		if(isset($token_data['error'])) return $token_data['error'];

		return $token_data['access_token'];
	}

}



