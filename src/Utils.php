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
		$reports = DB::get('reports', $version);
		if(empty($reports)){
	    	$reports_string = Auth::config('selected_reports');

			$selected_reports = array_map(function($v){
				return Embed::createFromString($v);
			}, clean_array_from_string($reports_string));
		}
		else{
			$selected_reports = $reports->map(function($v){
				return new Embed($v);
			});
		}

		return collect($selected_reports);

	}


	public static function root_path($path = ''){

		return dirname(__DIR__) . Str::start($path, '/');

	}


	public static function base_path($path = ''){

		return __DIR__ . Str::start($path, '/');

	}


	public static function view_path($path = ''){
		return static::base_path('Views' . Str::start($path, '/'));

	}


    public static function get_http($url){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'G3N1US cURL Fn'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }



	public static function data_path($file = null){
		return dirname(static::base_path()) . '/_data' . Str::start($file, '/');

	}

}
