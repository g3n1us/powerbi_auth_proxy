<?php
	
namespace BlueRaster\PowerBIAuthProxy;

use Illuminate\Support\Str;
use Carbon\Carbon;

// convenience class for accessing utility classes found in Utils

class Utils{
	
	public static function csrf(){
		return new Utils\Csrf;
	}
	
	
	public static function method_field($method){
		return '<input type="hidden" name="_method" value="'.trim(strtoupper($method)).'" />';
	}
	
	
	public static function getReports($version = null){
		$reports = static::get(['reports', $version]);
		
		if(empty($reports)){
	    	$reports_string = Auth::config('selected_reports');
	
			$selected_reports = array_map(function($v){
				return Embed::createFromString($v);
			}, clean_array_from_string($reports_string));			
		}
		else{
			$selected_reports = array_map(function($v){
				return new Embed($v);
			}, $reports);			
		}
		
		return collect($selected_reports);
		
	}
	
	
	public static function base_path($path = ''){
		
		return __DIR__ . Str::start($path, '/');
		
	}
	
	public static function view_path($path = ''){
		return static::base_path('Views' . Str::start($path, '/'));
		
	}
	
	public static function data_path($file = null){
		return dirname(static::base_path()) . '/_data' . Str::start($file, '/');
		
	}
	
	// saving and retrieving stored data
	public static function save($key, $value){
		return static::saveOrGet($key, $value);
	}
	
	public static function get($key, $version = null){
		if($version){
			return static::saveOrGet([$key, $version]);	
		}
		return static::saveOrGet($key);
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

			if(is_null($version)){

				return $versions->first()['data'];
			}
			else{
				$return_version = $versions->where('version', $version);
				if($return_version->isEmpty() && (int) $version <= $versions->count()){
					$return_version = $versions->get($version);
				}
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
