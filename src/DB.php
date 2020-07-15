<?php
	
namespace BlueRaster\PowerBIAuthProxy;

use Illuminate\Support\Str;
use Carbon\Carbon;


class DB{
	
	public $dir;
	
	public function __construct(){
		$this->dir = Utils::data_path();

	}
	
	public function where(){
		
	}
		
	// saving and retrieving stored data
	public static function save($key, $value){
		return static::saveOrGet($key, $value);
	}
	
	public static function get($key, $version = null){
		if($version){
			return collect(static::saveOrGet([$key, $version]));	
		}
		return collect(static::saveOrGet($key));
	}
	
	
	public static function get_versions($key){
		$data_file = Utils::data_path($key);
		if(!file_exists($data_file)) return collect([]);
		
		$versions = collect(file($data_file));
		$versions->transform(function($v){
			[$timestamp, $data] = array_map('trim', explode('|', $v));
			return ['version' => $timestamp, 'timestamp' => new Carbon((int) $timestamp), 'data' => json_decode($data, true)];
		});
		
		return $versions->sortByDesc('timestamp')->values();
	
	}
	
	private static function saveOrGet(...$args){
		if(is_array($args[0])){
			$key = $args[0][0];
			$version = $args[0][1];
		}
		else{
			$key = $args[0];
			$version = null;
		}
		$data_file = Utils::data_path($key);
		if(count($args) < 2){
			// get a value
			if(!file_exists($data_file)) return null;
			
			$versions = static::get_versions($key);
;
			if(empty($version)){

				return $versions->first()['data'];
			}
			else{
				$return_version = $versions->firstWhere('version', $version);
/*
				if(!empty($return_version) && (int) $version <= $versions->count()){
					$return_version = $versions->get($version);
					
				}
*/
				return @$return_version['data'];
			}
		}
		else{
			// set a value
			$value = $args[1];
			if(is_object($value) && method_exists($value, 'toJSON')){
				$value = $value->toJSON();
			}
			else if(is_array($value)){
				$value = json_encode($value);
			}
			else{
				$value = json_encode(['_simple_value' => $value]);
			}
			$data = time() . '|' . $value . PHP_EOL;
			return file_put_contents($data_file, $data, FILE_APPEND);
		}
	}
	
	
}