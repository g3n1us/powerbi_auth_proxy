<?php
	
namespace BlueRaster\PowerBIAuthProxy;

use BlueRaster\PowerBIAuthProxy\Exceptions\IllegalClassDirectoryListException;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

use Illuminate\Support\Str;

class Filesystem{
	
	protected static $allowed_class_directories = ['Frameworks', 'UserProviders', 'Exceptions'];
	
	public static function files($dir){
		return collect((new IlluminateFilesystem)->files($dir));
	}
	
	public static function allFiles($dir){
		return collect((new IlluminateFilesystem)->allFiles($dir));
	}
	
	public static function list_classes($dir){
		$sanitized_dir = basename($dir);
		if(!in_array($sanitized_dir, self::$allowed_class_directories)){
			throw new IllegalClassDirectoryListException;
		}
		
		return Filesystem::files(__DIR__."/$sanitized_dir")->map(function($splFile) use($sanitized_dir){
			$name = $splFile->getFilenameWithoutExtension();
			return $name !== Str::singular($sanitized_dir) ? new ClassIterable("$sanitized_dir\\$name") : null;
		})->filter();
	}
	
}


class ClassIterable{
	
	public $classname;
	
// 	public $classname;
	
	public function __construct($classname){
		$namespace = (new \ReflectionClass($this))->getNamespaceName();
		$this->classname = "$namespace\\$classname";
	}
	
	public function __toString(){
		return $this->classname;
	}
}
