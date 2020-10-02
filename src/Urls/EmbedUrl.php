<?php

namespace BlueRaster\PowerBIAuthProxy\Urls;

use BlueRaster\PowerBIAuthProxy\Exceptions\IdCannotBeDeterminedException;
use BlueRaster\PowerBIAuthProxy\Utils;

class EmbedUrl{

	public $str;

	public $id;

	public $scheme = 'https';

	public $query = [];

	public $host = 'www.arcgis.com';

	public $path = '/apps/opsdashboard/index.html';

	public $fragment = null;


	public function determine_type(){
		if(preg_match('/^.*?arcgis.*?$/', $this->host)) return 'esri';


		return 'power_bi';
	}

	public function setToken($token){
		$this->query['token'] = $token;
	}



	public static function determine_id($parts){
		$from_query = null;
		$from_fragment = null;
		if(!empty($parts['query'])){
			if( $id = Utils::parse_keyless_query($parts['query']) ){
				return $id;
			}

			$possible_keys = array_filter(array_keys($parts['query']), function($k){
				return preg_match('/^.*?id$/i', $k);
			});
			if(!empty($possible_keys)){
				$from_query = $parts['query'][$possible_keys[0]];;
			}
		}
		if(!empty($parts['fragment'])){
			if($f = trim(trim($parts['fragment'], '/'))){
				$from_fragment = $f;
			}
		}

		$id = $from_query ?? $from_fragment ?? false;
		if($id === false){
			throw new IdCannotBeDeterminedException;
		}

		return $id;
	}




	public static function createFromString($str){
		[$id, $name, $type] = array_merge(Utils::clean_array_from_string($str, '|'), [null, null, null]);

		return new self($id, $name, $type);
	}


	public function __toString(){
		$url = $this->scheme . '://' . $this->host . $this->path;
		if($this->query) $url .= '?'.http_build_query($this->query);
		if($this->fragment) $url .= '#'.$this->fragment;
		return $url;
	}

}
