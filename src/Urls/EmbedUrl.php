<?php

namespace BlueRaster\PowerBIAuthProxy\Urls;	

use BlueRaster\PowerBIAuthProxy\Exceptions\IdCannotBeDeterminedException;
	
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
	
	
	public static function spread_url($str){
		$parts = array_merge(['scheme' => null, 'query' => null, 'host' => null, 'path' => null, 'fragment' => null], parse_url($str));
		$q = parse_str($parts['query'], $qq);
		$parts['query'] = $qq;

		if(array_keys(array_filter($parts)) == ['path']){
			return false;
		}
		
		return $parts;
	}
	
	
	public static function parse_keyless_query($str){
		$arr = $str;
		if(is_string($str)){
			parse_str($str, $arr);
		}
		if([1, 0] == [ count(array_filter(array_keys($arr))), count(array_filter($arr)) ]){
			return array_keys($arr)[0];	
		}
		return false;
	}
	
	
	public static function determine_id($parts){
		$from_query = null;
		$from_fragment = null;
		if(!empty($parts['query'])){
			if( $id = static::parse_keyless_query($parts['query']) ){
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
		[$id, $name, $type] = array_merge(clean_array_from_string($str, '|'), [null, null, null]);
		
		return new self($id, $name, $type);
	}	


	public function __toString(){
		$url = $this->scheme . '://' . $this->host . $this->path;
		if($this->query) $url .= '?'.http_build_query($this->query);
		if($this->fragment) $url .= '#'.$this->fragment;
		return $url;
	}
	
}	
