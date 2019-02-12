<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');


class  String_attr extends Attr_engine
{

    protected $value = null;
    protected $type = 'string';
    protected $constraint = 250;
    protected $required = false;
    protected $default = '';

    function __construct($properties) {
        if(isset($properties['constraint']) && is_numeric($properties['constraint'])){
            $this->constraint = $properties['constraint'];
        }
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
        return (isset($value) && strlen($value) <= $this->constraint)&& parent::verify($value);
    }
}