<?php

namespace BlueRaster\PowerBIAuthProxy;

use Illuminate\Support\Str;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use BlueRaster\PowerBIAuthProxy\Filesystem;
use BlueRaster\PowerBIAuthProxy\Frameworks\Mock as MockFramework;


// convenience class for accessing utility classes found in Utils

class Utils{

	public static function csrf(){
		return new Utils\Csrf;
	}


	public static function getFramework(){
		foreach(Filesystem::list_classes('Frameworks') as $class){
			$class = "$class";
			if($class::test()){
				return $class;
			}
		}

		// return MockFramework::class;
	}


	public static function installationType(){
		$test = basename(dirname(__DIR__)) === 'vendor';
		return $test ? "composer" : "standalone";
	}


	public static function installedComposer(){
		return Utils::installationType() === "composer";
	}


	public static function installedStandalone(){
		return Utils::installationType() === "standalone" && env('APP_IS_STANDALONE') === true;
	}


	public static function method_field($method){
		return '<input type="hidden" name="_method" value="'.trim(strtoupper($method)).'" />';
	}


	public static function tidy_path($path = ''){
		// remove any duplicated slashes
		$path = preg_replace('/\/+/', '/', $path);
		// ensure it starts with a slash
		$path = Str::start($path, '/');
		// ensure id does not end in a slash
		$path = rtrim($path, '/');
		return $path;
	}


	public static function root_path($path = ''){

		return dirname(__DIR__) . Utils::tidy_path($path);

	}


	public static function base_path($path = ''){

		return __DIR__ . Utils::tidy_path($path);

	}


	public static function public_path($path = ''){

		return Utils::root_path("public/$path");

	}


	public static function view_path($path = ''){

		return Utils::base_path("Views/$path");

	}


	public static function data_path($file = ''){

		return Utils::root_path("_data/$file");

	}


	public static function namespace(){
		return __NAMESPACE__;
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




	public static function auth_proxy(){
    	return \BlueRaster\PowerBIAuthProxy\Auth::get_instance();
	}



    public static function guzzle_get_contents($url){
        $client = new GuzzleClient;
		$res = $client->get($url);

		$body = $res->getBody();
		$content = '';
		while (!$body->eof()) {
		    $content .= $body->read(1024);
		}
        return $content;
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
		if([1, 0] == [count(array_filter(array_keys($arr))), count(array_filter($arr))]){
			return array_keys($arr)[0];
		}
		return false;
	}



	public static function clean_array_from_string($str, $delimiter = ","){
		$parts = explode($delimiter, trim(trim($str), $delimiter));
		$parts = array_map('trim', $parts);
		return array_filter($parts);
	}


}
