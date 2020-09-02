<?php

namespace BlueRaster\PowerBIAuthProxy;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;

use BlueRaster\PowerBIAuthProxy\Exceptions\MissingConfigException;
use BlueRaster\PowerBIAuthProxy\Filesystem;
use BlueRaster\PowerBIAuthProxy\UserProxy;


class Auth{

	private $embed_tokens = [];

	private $oauth_token;

	private $username;

	private $password;

	private $application_id;

	private $application_secret;

	private $service_principal_id;

	private $group_id;

	private $selected_reports;

	private $esri_client_id;

	private $esri_client_secret;

	private static $instance = false;

	private static $framework;

	public static $token_from_referrer;

	public static function get_instance(){
		if(!self::$instance) self::$instance = new self;
		return self::$instance;
	}

	public function __construct(){
		if(static::$instance){
			throw new \Exception("Double Instantiation error");
		}

		static::getFramework();

		// UserProxy handles middleware functions.
		// Aborts the request if authorization is not valid
		UserProxy::handle(static::$framework->getUserProvider());


		foreach(self::getDefaultConfig() as $key => $value){
			$this->{$key} = static::config($key);
		}
	}

	public static function getCurrentUser(){
    	static::get_instance();
    	return static::$framework->getUserProvider()->getUser();
	}

    private static function getDefaultConfig(){
	    return [
		    "username" => env('USERNAME'),
		    "password" => env('PASSWORD'),
		    "application_id" => env('APPLICATION_ID'),
		    "application_secret" => env('APPLICATION_SECRET'),
		    "group_id" => env('GROUP_ID'),
		    "selected_reports" => env('SELECTED_REPORTS'),
		    "esri_client_id" => env('ESRI_CLIENT_ID'),
		    "esri_client_secret" => env('ESRI_CLIENT_SECRET'),
		    "esri_endpoint" => env('ESRI_ENDPOINT'),
		    "esri_dashboard_endpoint" => env('ESRI_DASHBOARD_ENDPOINT'),
		    "accepted_referrers" => env('ACCEPTED_REFERRERS'),
		    "auth_proxy_admins" => env('AUTH_PROXY_ADMINS'),
		    "service_principal_id" => env('SERVICE_PRINCIPAL_ID'),
	    ];
    }

	public static function config($key = null, $default = null){

    	$framework = static::getFramework();

        $config = array_merge(self::getDefaultConfig(), $framework->getConfig());

	    if(!defined('PBI_AUTH_PROXY_RUNNING_INSTALL') && !static::check_config($config)){
		    throw new MissingConfigException;
	    }

        if($key){
            return @$config[$key] ?? $default;
        }
	    return $config;
	}

	// return boolean
	public static function check_config(Array $config){
	    if(empty($config["username"]) || empty($config["password"]) || empty($config["application_id"]) || empty($config["application_secret"]) || empty($config["group_id"]) || empty($config["selected_reports"])){
		    return false;
	    }
	    return true;
	}

	public static function getFramework(){
		if(static::$framework) return static::$framework;

		foreach(Filesystem::list_classes('Frameworks') as $class){
			$class = "$class";
			if($class::test()){
    			static::$framework = new $class;
				return static::$framework;
			}
		}

		static::$framework = new \BlueRaster\PowerBIAuthProxy\Frameworks\Mock;
		return static::$framework;
	}

	public function framework(){
		return static::getFramework();
	}


	public function getAuthToken(){
		if(!$this->oauth_token){
			$client = new GuzzleClient;
			try{
    			// service_principal_id
// 				$res = $client->post('https://login.microsoftonline.com/common/oauth2/token', [
                $tenant_id = 'e96e5e10-2458-4a91-885e-a6adfb1ce562';
				$res = $client->post("https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token", [
					'headers' => [
						'Accept'     => 'application/json',
					],
					'form_params' => [
					    'client_id'          => $this->application_id,
					    'client_secret'      => $this->application_secret,
// 					    'resource' => 'https://analysis.windows.net/powerbi/api',
					    'grant_type' => 'client_credentials',
					    // 'response_type' => 'code',

					    //'requested_token_use' => 'on_behalf_of',
					    //'grant_type' => 'password',
// 					    'scope' => 'openid',
					    'scope' => 'https://graph.microsoft.com/.default',
					    //'assertion' => $this->service_principal_id,
					    //'username' => $this->username,
					    //'password' => $this->password,
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
dd($this->oauth_token);

			}
			catch(\Exception $e){
				dd($e->getMessage());
			}

		}

		return $this->oauth_token;
	}

	public function getGroupId(){
		return $this->group_id;
	}


/*
	public function getSelectedReports(){
		$selected_reports = array_map(function($v){
			[$id, $name, $type] = array_merge(clean_array_from_string($v, '|'), [null, null, null]);

			$embed = new Embed(['id' => $id, 'name' => $name, 'type' => $type]);

			return $embed;
		}, clean_array_from_string($this->selected_reports));

		return $selected_reports;
	}
*/

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


	public static function getTokenFromReferrer(){
		if(!empty(static::$token_from_referrer)){
			return static::$token_from_referrer;
		}

		if($referrer = @$_SERVER['HTTP_REFERER']){
	    	['host' => $referrer_host] = array_merge(['host' => false], parse_url($referrer));
	        $accepted_referrers = array_map('trim', explode(',', Auth::config('accepted_referrers', 'empty')));

	    	foreach($accepted_referrers as $accepted_referrer){
		    	if(\Illuminate\Support\Str::is($accepted_referrer, $referrer_host)){
			    	parse_str(parse_url($referrer, PHP_URL_QUERY), $output);
			    	if($token = @$output['token']){
				    	static::$token_from_referrer = $token;
				    	return $token;
			    	}
		    	}
	    	}
		}

    	return null;
	}



	public function getEmbedToken($report_id){
        try{
    		if(!isset($this->embed_tokens[$report_id])){
    			$guzzle = new GuzzleClient(['base_uri' => 'https://api.powerbi.com']);
    			$token_string = $this->getAuthToken();
    // 			$url = "https://api.powerbi.com/v1.0/myorg/GenerateToken";

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
        catch(\Exception $e){
            dd($e->getMessage());
        }

	}


	public function getEsriEmbedToken(){
		if($referrer_token = static::getTokenFromReferrer()){
			return $referrer_token;
		}
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



