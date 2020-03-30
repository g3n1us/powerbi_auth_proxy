<?php

namespace BlueRaster\PowerBIAuthProxy\Urls;	

use BlueRaster\PowerBIAuthProxy\Auth;

use BlueRaster\PowerBIAuthProxy\Exceptions\IdCannotBeDeterminedException;
	
class PowerBiEmbedUrl extends EmbedUrl{
	
	public $host = 'app.powerbi.com';
	
	public $path = '/reportEmbed';
		
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
		
	}
}
