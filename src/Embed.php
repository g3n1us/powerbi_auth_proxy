<?php

namespace BlueRaster\PowerBIAuthProxy;	

use Illuminate\Support\Collection;
use BlueRaster\PowerBIAuthProxy\Urls\EsriEmbedUrl;
use BlueRaster\PowerBIAuthProxy\Urls\PowerBiEmbedUrl;
	
class Embed extends Collection{

	public $id;
	
	public $name = '';
	
	public $type = 'power_bi';
	
	public $url;

	public function __construct($items = []){
		['id' => $id, 'name' => $name, 'type' => $type] = $items;
		$types = [
			'esri' => 'EsriEmbedUrl',
			'power_bi' => 'PowerBiEmbedUrl',
		];
		$type_classname = @$types[$type] ?? 'PowerBiEmbedUrl';
		$type_classname = '\\BlueRaster\\PowerBIAuthProxy\\Urls\\' . $type_classname;
		$this->url = new $type_classname($id);
		$this->id = $this->url->id;
		if($name) $this->name = $name;
		else $this->name = $this->id;
		$this->type = $type ?? $this->url->determine_type();
		
		$new_items = [
			'id' => $this->id,
			'name' => $this->name,
			'type' => $this->type,
			'slug' => preg_replace('/-/', '', $this->id), 
			'handle' => preg_replace('/[^a-z0-9]/', '-', strtolower($this->name)),
			'url' => (string) $this->url,
		];

		parent::__construct($new_items);
	}
	
	
	public static function createFromString($str){
		[$id, $name, $type] = array_merge(clean_array_from_string($str, '|'), [null, null, null]);
		
		$type = $type ?? self::determine_type($id);
		return new self(['id' => $id, 'name' => $name, 'type' => $type]);
	}	

	
	public static function determine_type($id){
		if($parts = spread_url($id)){
			if(preg_match('/^.*?arcgis.*?$/', $parts['host'])) return 'esri';
		}
		
		return 'power_bi';
	}
	
}
