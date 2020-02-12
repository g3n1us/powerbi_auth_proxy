<?php

namespace BlueRaster\PowerBIAuthProxy;


class Route{

    public $pattern;

    public $gates = [];

    public $callback;

    public $router;

    public function __construct($pattern, $gates = [], $callback = null){
        if(count(func_get_args()) === 2){
            $callback = $gates;
            $gates = [];
        }

        $this->pattern = $pattern;
        if(is_string($gates) || is_callable($gates)){
            $gates = [$gates];
        }

        $this->gates = array_merge($this->gates, $gates);

        $this->callback = $callback;

        Routes::$routes[] = $this;
    }

    public function __toString(){
        return $this->pattern;
    }
}
