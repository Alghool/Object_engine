<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');
include_once ('Integer_attr.php');
include_once ('String_attr.php');
include_once ('Text_attr.php');
include_once ('Timestamp_attr.php');
include_once ('Enum_attr.php');

class Attr_engine
{
    protected $hasValue = false;
    protected $hasDefault = false;

    protected $value = null;
    protected $nullable = false;
    protected $type;
    protected $constraint;
    protected $required;
    protected $default;
    protected $specialValidation = null;

    public function verify($value){
            return (isset($this->specialValidation))? $this->specialValidation->__invoke($value) : true;
    }

    public function setValue($value){
        $this->hasValue = true;
        if($this->verify($value)){
            $this->value = $value;
            return true;
        }else{
            return false;
        }
    }

    public function addSpecialValidation(callable $fun){
        if(isset($fun)){
            $this->specialValidation = $fun;
        }
    }

    public function setValueWithoutValidation($value){
        $this->hasValue = true;
        $this->value = $value;
        return true;
    }

    public function getValue(){
        return $this->value;
    }

    public function isRequired(){
        return $this->required;
    }

    public function hasDefault(){
        return $this->hasDefault;
    }

    public function useDefault(){
        $this->value = $this->default;
    }

    public function hasValue(){
        return $this->hasValue;
    }
}