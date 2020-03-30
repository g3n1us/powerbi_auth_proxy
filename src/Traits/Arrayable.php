<?php
	
namespace BlueRaster\PowerBIAuthProxy\Traits;	
	
trait Arrayable{
	
	private $container = [];

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->{$offset} = $value;
        }
    }

    public function offsetExists($offset) {
	    if(isset($this->container[$offset])) return true;
        return isset($this->{$offset});
    }

    public function offsetUnset($offset) {
	    if(isset($this->container[$offset])) unset($this->container[$offset]);
	    
        unset($this->{$offset});
    }

    public function offsetGet($offset) {
	    if(isset($this->container[$offset])) return isset($this->container[$offset]);
        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

	
}