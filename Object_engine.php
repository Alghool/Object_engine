<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');
include_once ('/Attr_types/Attr_engine.php');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
///                                               HELP                                                     //
///
///  attr array options
///
///      'value' => null,
///      'type' => 'TIMESTAMP', 'INT', 'VARCHAR', 'TEXT', 'FLAG', 'ENUM'
///      'constraint' => var length limit , int Type(tiny, small, medium, int, big), enum values, flag options
///      'unsigned' => TRUE,FALSE fron int
///      'required' => TRUE, FALSE validate when save
///      'default' => '', default value for this attr
///      'unique' => TRUE,FALSE if this value shoud bu uniqe
///
///
///
///
///
/// /////////////////////////////////////////////////////////////////////////////////////////////////////////

class Object_engine{
    // settings /////////////////////
    public $strict = true;

    protected static $CI ;
    protected $table;
    protected $id = 0;
    protected $attr = array();
    protected $query = null;

    protected $errorMsg = '';
    private $modelName;
    private $hasObject = false;


    function __construct($modelName = null)
    {
        self::$CI = get_instance();
        if($modelName){
            $this->query = self::$CI->db;
            $this->modelName = $modelName;
            $this->attr['created_at'] = new Timestamp_attr(array(
                'default' => date("Y-m-d H:i:s")
            ));
            $this->attr['modified_at'] = new Timestamp_attr(array(
                'default' => date("Y-m-d H:i:s")
            ));


        }
    }

    public static function modelFactory($model){
        self::$CI->load->model($model);
        return new $model;
    }

    public function or_where($attr, $value){
        $this->attr[$attr] = $value;
        $this->orArr[] = $attr;
    }

    public function get($ID = 0){
        if($ID != 0){
            $this->query->reset_query();
            $this->query->where($this->table.'_id', $ID);
        }

        $query = $this->query->get($this->table);
        $result = $query->row_array();
        if($result){
            $this->buildResult($result);
            $this->afterQuery();
            return true;
        }else{
            if($this->strict){
                $this->errorMsg = "no result found ID: {$ID} in module: {$this->modelName}";
                log_message('debug', $this->errorMsg);
                show_error( $this->errorMsg, 404, $heading = 'object engine encountered error');
            }
            return false;
        }
    }

    public function save(){
        //register the modified date
        $this->attr['modified_at']->setValueWithoutValidation( date("Y-m-d H:i:s"));

        //set object data
        $data = array();
        foreach ($this->attr as $attr => $value){
            if($value->hasValue()){
                $data[$attr] = $value->getValue();
            }elseif($value->isRequired()){
                $this->errorMsg = "required attribute {$attr} is missing module: {$this->modelName}";
                log_message('debug', $this->errorMsg);
                return false;
            }elseif($value->hasDefault()){
                $value->useDefault();
                $data[$attr] = $value->getValue();
            }elseif ($this->strict){
                $this->errorMsg = " attribute {$attr} is missing in module: {$this->modelName} - strict mode";
                log_message('debug', $this->errorMsg);
                return false;
            }
        }

        if($this->hasObject && $this->id != 0){
            //update
            $this->query->where($this->table.'_id', $this->id);
            if($this->query->update($this->table, $data)){
                $this->afterQuery();
                return true;
            }else{
                return false;
            };
        }else{
            //add new
            if($this->query->insert($this->table, $data)){
                $this->id = $this->query->insert_id();
                $this->afterQuery();
                return true;
            }else{
                return false;
            };
        }
    }

    private function buildResult($result){
        $this->id = $result[$this->table.'_id'];
        unset($result[$this->table.'_id']);
        foreach ($result as $attr => $value){
            if(isset($this->attr[$attr])){
                $this->attr[$attr]->setValueWithoutValidation($value);
            }
        }
    }

    private function afterQuery(){
        $this->query->reset_query();
        $this->hasObject = true;
    }

    public function distroy(){
        //to destroy this object and start over
    }


    // /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //                                                                                                            //
    //                                         setters and getters functions                                              //
    //                                                                                                            //
    // /////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function lastError(){
        if($this->errorMsg){
            return $this->errorMsg;
        }else{
            return 'no Errors';
        }
    }

    public function setAttrs(array $attrs){
        //todo: implement this to set multible attrs in the same time
        $successCounter = 0;
        foreach ($attrs as $key => $value){
            if($this->setAttr($key, $value)){
                $successCounter++;
            }
        }
        if( $successCounter == count($attrs)){
            return true;
        }else{
            $this->errorMsg = "not all values are set in module: {$this->modelName}";
            log_message('debug', $this->errorMsg);
            if($this->strict){
                show_error( $this->errorMsg, 500, $heading = 'object engine encountered error');
            }
            return false;
        }
    }

    public function setAttr($name, $value){
        $done = true;
        if($this->strict){
            //do not allow non attribute inputs
            if(!array_key_exists($name,$this->attr) && !((strpos ($name, 'or_') === 0 )&&(array_key_exists(substr($name,3),$this->attr)) )){
                $this->errorMsg = "try to set non attribute property : {$name} in module: {$this->modelName}";
                log_message('debug', $this->errorMsg);
                show_error( $this->errorMsg, 500, $heading = 'object engine encountered error');
                $done = false;
            }
        }
        if( array_key_exists($name,$this->attr) && !is_array($value))
        {
            if($this->attr[$name]->setvalue($value)) {
                $done = true;
            }
            else{
                $this->errorMsg = "try to set non valid value '{$value}' for property : {$name} in module: {$this->modelName}";
                log_message('debug', $this->errorMsg);
                if($this->strict){
                    show_error( $this->errorMsg, 500, $heading = 'object engine encountered error');
                }
                $done = false;
            }
        }
        if(!$this->hasObject){
            $where = 'where';
            if(strpos ($name, 'or_') === 0){
                $where = 'or_'.$where;
                $name = substr($name,3);
            }
            if(is_array($value)){
                $where = $where . '_in';
            }
            if(array_key_exists($name,$this->attr)){
                $this->query->$where($name, $value);
            }
        }
        return $done;
    }

    public function __set($name, $value)
    {
        $this->setAttr($name,$value);
    }

    public function __get($name)
    {
        if(array_key_exists($name, $this->attr)){
            return $this->attr[$name]->getValue();
        }elseif(isset($this->$name)){
            return $this->$name;
        }elseif($this->strict){
            $this->errorMsg = "request non exist attr: {$name} in module: {$this->modelName}";
            log_message('debug', $this->errorMsg);
            show_error( $this->errorMsg, 500, $heading = 'object engine encountered error');
        }else{
            return false;
        }
    }

}