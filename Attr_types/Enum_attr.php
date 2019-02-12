<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');


class  Enum_attr extends Attr_engine
{

    protected $value = null;
    protected $type = 'enum';
    protected $constraint = array();
    protected $required = false;
    protected $default = 0;

    function __construct($properties) {
        if(isset($properties['constraint']) && is_array($properties['constraint'])){
            $this->constraint = $properties['constraint'];
        }
        if(isset($properties['required']) && is_bool($properties['required'])){
            $this->required = $properties['required'];
        }
        if(isset($properties['default']) && in_array($properties['default'], $this->constraint)){
            $this->hasDefault = true;
            $this->default = $properties['default'];
        }
        if(isset($properties['nullable']) && is_bool($properties['nullable'])){
            $this->default = $properties['nullable'];
        }
    }
    public function verify($value)
    {
        return ((is_numeric($value) && $value > 0 && $value < count($this->constraint) )||(in_array($value, $this->constraint))) && parent::verify($value);
    }
}