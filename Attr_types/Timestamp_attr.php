<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');


class  Timestamp_attr extends Attr_engine
{

    protected $value = null;
    protected $type = 'timestamp';
    protected $required = false;
    protected $default = 0;
    protected $nullable = true;

    function __construct($properties) {
        if(isset($properties['required']) && is_bool($properties['required'])){
            $this->required = $properties['required'];
        }
        if(isset($properties['default'])){
            //todo: check if valid mySQL time stamp
            $this->hasDefault = true;
            $this->default = $properties['default'];
        }
        if(isset($properties['nullable']) && is_bool($properties['nullable'])){
            $this->default = $properties['nullable'];
        }
    }

    public function verify($value)
    {
        //todo: add timestamp validation
        return parent::verify($value);
    }

}