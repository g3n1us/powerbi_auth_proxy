<?php

namespace BlueRaster\PowerBIAuthProxy;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;

use TheNetworg\OAuth2\Client\Provider\Azure as AzureProvider;

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

	private static $instance = false;

	private static $ci;

	public static function get_instance(){
		if(!self::$instance) self::$instance = new self;
		return self::$instance;
	}

	public function __construct(){
		if(static::$instance){
			throw new \Exception("The AuthProxy Auth class must be provided an instance of Ci_Controller, and the class must not be constructed directly. Use Auth::get_instance(\$ci_app)");
		}
		if(! static::$ci =& get_instance() ) {
			new \Ci_Controller;
			static::$ci =& get_instance();
		}

		UserProxy::handle(static::$ci->user);

	    $this->username = env('USERNAME');
	    $this->password = env('PASSWORD');
	    $this->application_id = env('APPLICATION_ID');
	    $this->application_secret = env('APPLICATION_SECRET');
	    $this->group_id = env('GROUP_ID');
	    $this->selected_reports = env('SELECTED_REPORTS');
	}



	public function getCi(){
		return static::$ci;
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

}



