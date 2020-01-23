<?php
	
namespace BlueRaster\PowerBIAuthProxy\Frameworks;	
	
class Laravel extends Framework{
	
	protected $user_providers = ['Eloquent'];
	
	public function __construct(){
		parent::__construct();
		
		$this->user = auth()->user() ?? new \Illuminate\Foundation\Auth\User;
	}
	
	public static function test(){
		return defined('LARAVEL_START');
	}
}
