<?php

namespace BlueRaster\PowerBIAuthProxy;


class Route{

    public $pattern;

    public $gates = [];

    public $callback;

    public $router;

    protected $nullable = false;

    public function __construct($pattern = null, $gates = [], $callback = null){
        if(count(func_get_args()) === 2){
            $callback = $gates;
            $gates = [];
        }

		if(!empty($pattern)){
			$this->pattern = $pattern;
		}

        if(is_string($gates) || is_callable($gates)){
            $gates = [$gates];
        }

        $this->gates = array_merge($this->gates, $gates);

		if(!empty($callback)){
			$this->callback = $callback;
		}

        Routes::$routes[] = $this;
    }

    public function __toString(){
        return $this->pattern;
    }

    public function isNullable(){
	    return $this->nullable;
    }

}
