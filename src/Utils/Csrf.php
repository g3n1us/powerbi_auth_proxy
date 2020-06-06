<?php
	
namespace BlueRaster\PowerBIAuthProxy\Utils;	

use BlueRaster\PowerBIAuthProxy\Auth;


class Csrf{
	
	public $name;
	
	public $value;
	
	public $html;
	
	public function __construct(...$args){
		if(empty($args)){
			 $instance = Auth::getFramework()->getCsrf();
			 ['name' => $this->name, 'value' => $this->value, 'html' => $this->html] = $instance->toArray();
		}
		else if(count($args) === 1){
			// this means that an html representation was passed to the constructor.
			// ToDo - ensure the input is html
			[ $this->html ] = $args;
		}
		else{
			[$this->name, $this->value] = $args;
			
			$this->html = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'" />';
			
		}
	}
	
	public function toArray(){
		return [
			'name' => $this->name,
			'value' => $this->value,
			'html' => $this->html,
		];
	}
	
	public function __toString(){
		return $this->html;
	}
}	
