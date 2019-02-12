<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');


class  Text_attr extends Attr_engine
{

    protected $value = null;
    protected $type = 'text';
    protected $required = false;
    protected $default = '';

    function __construct($properties) {
        if(isset($properties['required']) && is_bool($properties['required'])){
            $this->required = $properties['required'];
        }
        if(isset($properties['default'])){
            $this->hasDefault = true;
            $this->default = $properties['default'];
        }
        if(isset($properties['nullable']) && is_bool($properties['nullable'])){
            $this->default = $properties['nullable'];
        }
    }
    public function verify($value)
    {
        return parent::verify($value);
    }
}