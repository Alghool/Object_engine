<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');


class  Integer_attr extends Attr_engine
{

    protected $value = null;
    protected $type = 'integer';
    protected $constraint = 'int';
    protected $required = false;
    protected $default = 0;
    protected $nullable = true;
    protected $unsigned = false;
    private static $intTypes = array(
        'tiny' => array(array('min'=> -128, 'max' =>127),array('min'=> 0, 'max' =>255)),
        'small' => array(array('min'=> -32768	, 'max' =>32767),array('min'=> 0, 'max' =>65535)),
        'medium' => array(array('min'=> -8388608, 'max' =>8388607),array('min'=> 0, 'max' =>16777215)),
        'int' => array(array('min'=> -2147483648, 'max' =>2147483647),array('min'=> 0, 'max' =>4294967295)),
        'big' => array(array('min'=> -2**63, 'max' =>2**63 -1),array('min'=> 0, 'max' =>2**64 -1)));

    function __construct($properties) {
        if(isset($properties['constraint']) && key_exists($properties['constraint'], Integer_attr::$intTypes)){
            $this->constraint = $properties['constraint'];
        }
        if(isset($properties['required']) && is_bool($properties['required'])){
            $this->required = $properties['required'];
        }
        if(isset($properties['default']) && is_numeric($properties['default'])){
            $this->hasDefault = true;
            $this->default = $properties['default'];
        }
        if(isset($properties['nullable']) && is_bool($properties['nullable'])){
            $this->default = $properties['nullable'];
        }
        if(isset($properties['unsigned']) && is_bool($properties['unsigned'])){
            $this->unsigned = $properties['unsigned'];
        }
    }

    public function verify($value)
    {
        return (is_numeric($value)
           && Integer_attr::$intTypes[$this->constraint][(int)$this->unsigned]['max'] >= $value
           && $value >= Integer_attr::$intTypes[$this->constraint][(int)$this->unsigned]['min'])
           && parent::verify($value);
    }
}