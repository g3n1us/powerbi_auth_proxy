<?php

namespace BlueRaster\PowerBIAuthProxy\UserProviders;

use BlueRaster\PowerBIAuthProxy\Auth;
use BlueRaster\PowerBIAuthProxy\DB;



class BaseUser{

	protected $user;

	public function __construct($user, UserProvider $provider){
		$this->user = $user;
		$this->provider = $provider;
	}


	public function getEmail(){
		return $this->provider->getEmail();
	}


	public function getName(){
		return $this->provider->getName();
	}


	public function isAuthProxyAdmin(){
		$admin_emails = clean_array_from_string(Auth::config('auth_proxy_admins', ''));
		$admin_emails_db = DB::get('users');
		$admin_emails = $admin_emails_db->merge($admin_emails)->toArray();


		return in_array($this->getEmail(), $admin_emails);

	}


	public function __get($name){
		return @$this->user->{$name};
	}



	public function __call($name, $args){
		if(method_exists($this->provider, $name)){
			return call_user_func_array([$this->provider, $name], $args);
		}
		return call_user_func_array([$this->user, $name], $args);
	}

	public function toArray(){
		return [
			'name' => $this->getName(),
			'email' => $this->getEmail(),
			'is_auth_proxy_admin' => $this->isAuthProxyAdmin(),
			'admin_route' => $this->isAuthProxyAdmin() ? '/auth_proxy_routes/auth_proxy_admin.html' : null,
		];
	}

	public function __toString(){
		return json_encode($this->toArray());
	}

}
