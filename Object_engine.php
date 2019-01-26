<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: EMA_Alghool
 * Date: 1/26/2019
 * Time: 5:24 AM
 */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
///                                               HELP                                                     //
///
///  attr array options
///
///      'value' => null,
///      'type' => 'TIMESTAMP', 'INT', 'VARCHAR', 'TEXT', 'FLAG', 'ENUM'
///      'constraint' => var length limit , int length limit, enum values, flag options
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
    public $strict = false;

    protected static $CI ;
    protected $table;
    protected $id = 0;
    protected $attr = array(
        'title' =>
            array(
                'value' => null,
                'type' => 'VARCHAR',
                'constraint' => 250,
                'required' => TRUE,
                'default' => 'title'
            ),
        'body' =>
            array(
                'value' => null,
                'type' => 'TEXT',
                'required' => false,
                'default' => ''
            ),
        'created_at' =>
            array(
                'value' => null,
                'type' => 'TIMESTAMP'
            ),
        'modified_at' =>
            array(
                'value' => null,
                'type' => 'TIMESTAMP'
            )
    );
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
            $this->strict = false;
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
            return true;
        }else{
            if($this->strict){
                $this->errorMsg = "request non exist ID: {$ID} in module: {$this->modelName}";
                log_message('debug', $this->errorMsg);
                show_404();
            }
            return false;
        }
    }

    public function save(){
        if($this->hasObject && $this->id != 0){
            //update

        }else{
            //add new
            $data = array();
            foreach ($this->attr as $attr => $value){
                echo "<pre>$attr</pre>";
                var_dump($value);
                if(isset($value['value'])){
                    $data[$attr] = $value['value'];
                }elseif(isset($value['required']) && $value['required']){
                    $this->errorMsg = "required attribute {$attr} not exist in module: {$this->modelName}";
                    log_message('debug', $this->errorMsg);
                    return false;
                }elseif(isset($value['default']) && $value['default']){
                    $this->attr[$attr]['value'] = $value['default'];
                    $data[$attr] = $value['default'];
                }
            }
            if($this->query->insert($this->table, $data)){
                $this->id = $this->query->insert_id();
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
            $this->attr[$attr]['value'] = $value;
        }
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

    public function __set($name, $value)
    {
        if(array_key_exists($name,$this->attr) && !is_array($value))
        {
            //todo: validate
            switch($this->attr[$name]['type']){
                case'INT':
                    if(!is_numeric($value)){
                        $this->errorMsg = "attribute {$name} value is not numeric : {$this->modelName}";
                        log_message('debug', $this->errorMsg);
                        return false;
                    }
                    if(strlen($value) > $this->attr[$name]['constraint']){
                        $this->errorMsg = "required attribute {$name} length is not acceptable: {$this->modelName}";
                        log_message('debug', $this->errorMsg);
                        return false;
                    }
                    break;
            }
            $this->attr[$name]['value'] = $value;
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
            $this->query->$where($name, $value);
        }
    }

    public function __get($name)
    {
        if(array_key_exists($name, $this->attr)){
            return $this->attr[$name]['value'];
        }elseif(isset($this->$name)){
            return $this->$name;
        }elseif($this->strict){
            $this->errorMsg = "request non exist attr: {$name} in module: {$this->modelName}";

            log_message('debug', $this->errorMsg);
            show_404();
        }else{
            return false;
        }
    }

}