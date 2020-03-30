<?php

namespace BlueRaster\PowerBIAuthProxy\Urls;	

use BlueRaster\PowerBIAuthProxy\Auth;

use BlueRaster\PowerBIAuthProxy\Exceptions\IdCannotBeDeterminedException;
	
class EsriEmbedUrl extends EmbedUrl{
	
	public $host = 'www.arcgis.com';
	
	public $path = '/apps/opsdashboard/index.html';
		
	public function __construct($id){
		$this->str = $id;
		$parts = static::spread_url($id);

		if($parts === false){
			$this->id = $this->str;
		}
		else{
			$this->scheme = $parts['scheme'];
// 			$this->host = $parts['host'];
			$this->path = $parts['path'];
			$this->query = $parts['query'];
			$this->fragment = $parts['fragment'];
			$this->id = static::determine_id($parts);
		}
		if(empty($_SESSION['esri_token'])){
			$_SESSION['esri_token'] = (Auth::get_instance())->getEsriEmbedToken();
		}

		$this->setToken($_SESSION['esri_token']);
		
	}
}
