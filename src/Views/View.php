<?php
namespace BlueRaster\PowerBIAuthProxy\Views;
	
use BlueRaster\PowerBIAuthProxy\Route;
use BlueRaster\PowerBIAuthProxy\Auth;

use BlueRaster\PowerBIAuthProxy\Utils;

use Illuminate\Support\Str;
	
class View{
	
	protected $filepath;
	
	public $context;
	
	public function __construct($filepath, $context = []){
		$this->context = $context;
		
		$this->filepath = $filepath;
		if(!$this->cached()){
			$this->compile();
		}
	}
	
	
	private function hash(){
		return md5_file($this->filepath);
	}
	
	private function cached(){
		if(!file_exists($this->tmp_file())){
			return false;
		}
		
		$lasthash = @file($this->tmp_file())[1];
		$lasthash = trim($lasthash, ' /');

		return $this->hash() == $lasthash;
	}
	
	
	private function tmp_file(){
		return $this->tmp_dir(Str::slug($this->filepath, '_') . '.php');
	}
	
	
	private function tmp_dir($path = ''){
		return Utils::view_path("tmp/$path");
	}
	
	
	
	private function compile(){
		
		$tpl = file_get_contents($this->filepath);
		
		// first bring in imports
		$compiled = preg_replace_callback('/\@import\((.*?)\)/', function($matches){
			$tpl_dir = dirname($this->filepath);
			$path = trim($matches[1], '\'"');
			if($path[0] === '.'){
				if($path[1] === '.'){
					$path = dirname($tpl_dir) . ltrim($path, '.');
				}
				else{
					$path = $tpl_dir . ltrim($path, '.');
				}
			}
			$content = (new \BlueRaster\PowerBIAuthProxy\Views\View($path, $this->context))->show();
			return $content;
		}, $tpl);

		$compiled = preg_replace('/\{\{(.*?)\}\}/', '<?php echo e($1); ?>', $compiled);
		$compiled = preg_replace('/\{\!\!(.*?)\!\!\}/', '<?php echo $1; ?>', $compiled);
		$compiled = preg_replace('/\@foreach\((.*?)\)/', '<?php foreach($1){ ?>', $compiled);
		$compiled = preg_replace('/\@endforeach/', '<?php } ?>', $compiled);
		$compiled = preg_replace('/\@if\((.*?)\)/', '<?php if($1){ ?>', $compiled);
		$compiled = preg_replace('/\@endif/', '<?php } ?>', $compiled);
		$compiled = preg_replace('/\@selected\((.*?)\)/', '<?php if($1){ ?>selected<?php } ?>', $compiled);
		
		$compiled = preg_replace('/\@csrf/', '<?php echo \BlueRaster\PowerBIAuthProxy\Utils::csrf(); ?>', $compiled);
		
		
		$file_contents = implode(PHP_EOL, ['<?php', '// ' . $this->hash(), '?>', $compiled]);
		return file_put_contents($this->tmp_file(), $file_contents);
	}
	
	public function show(){
		foreach($this->context as $key => $val){
			$$key = $val;
		}
		ob_start();
		include $this->tmp_file();
		$rendered = ob_get_clean();
		return $rendered;
	}
	
	public function __toString(){
		return $this->show();
	}
	
}