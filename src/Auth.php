<?php

namespace BlueRaster\PowerBIAuthProxy;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;

// use Illuminate\Filesystem\Filesystem;

use BlueRaster\PowerBIAuthProxy\Exceptions\MissingConfigException;

require_once(__DIR__.'/boot.php');

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
		
		foreach(static::config() as $key => $value){
			$this->{$key} = $value;
		}
	}
	
	public static function config(){
	    $config = [
		    'username' => env('USERNAME'),
		    'password' => env('PASSWORD'),
		    'application_id' => env('APPLICATION_ID'),
		    'application_secret' => env('APPLICATION_SECRET'),
		    'group_id' => env('GROUP_ID'),
		    'selected_reports' => env('SELECTED_REPORTS'),
		    'esri_client_id' => env('ESRI_CLIENT_ID'),
		    'esri_client_secret' => env('ESRI_CLIENT_SECRET'),
	    ];

	    if(empty($config['username']) || empty($config['password']) || empty($config['application_id']) || empty($config['application_secret']) || empty($config['group_id']) || empty($config['selected_reports'])){
		    throw new MissingConfigException;
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
			[$id, $name] = explode('|', trim($v));
			return ['id' => $id, 'name' => $name];
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
	
	
	public function getEsriEmbedToken($report_id){
		$guzzle = new GuzzleClient(['base_uri' => 'https://www.arcgis.com/sharing/oauth2/token/']);
		$params = [
			'client_id' => $this->esri_client_id,
			'client_secret' => $this->esri_client_secret,
			'grant_type' => 'client_credentials',
			'f' => 'json'
		];
		
		$path = "https://www.arcgis.com/sharing/oauth2/token/";
		$request = new Request('POST', $path);
		$response = $guzzle->send($request);

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



