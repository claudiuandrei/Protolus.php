<?php
    abstract class Data{
        //protected static function performSearch($subject, $predicate);
        //protected static function initialize($options);
        //static $fields
        
        protected abstract function performLoad($id, $field);
        protected abstract function performSave();
        
        //static implementations
        public static $empty_field_mode = 'null'; //null, notice, exception
        public static $registry = array(); // uuid, integer
        public static $core_fields = array('id', 'record_status', 'modification_time', 'creation_time', 'modified_by');
        public static $core_options = array(
            'id' => array(
                'type' => 'integer',
                'identifier' => 'true'
            ), 
            'record_status', 
            'modification_time' => array(
                'type' => 'instant'
            ), 
            'creation_time' => array(
                'type' => 'instant'
            ),  
            'modified_by' => array(
                'type' => 'integer'
            )
        );
        protected static function parseWhere($whereClause){
            $results = array();
            $inQuote = false;
            $quoteChar = '';
            $quotation = '';
            $result = array();
            $quoteChars = array('\'', '"');
            $breakingChars = array(' ', '=', '>', '<', '!');
            for($lcv=0; $lcv<strlen($whereClause); $lcv++){
                if($inQuote){
                    if($whereClause[$lcv] == $quoteChar){ //close a quote
                        $result[sizeof($result)-1] .= '\''.$quotation.'\'';
                        $inQuote = false;
                        $quotation = '';
                    }else{
                        $quotation .= $whereClause[$lcv];
                    }
                }else{
                    if(!isset($result[0])) $result[0] = ''; //init a new subject if we have nothing
                    if($whereClause[$lcv] == ' ' ||
                        (!isset($result[1]) && in_array($whereClause[$lcv], $breakingChars)) ||
                        (isset($result[1]) && !isset($result[2]) && !in_array($whereClause[$lcv], $breakingChars))
                    ){
                        if(isset($result[2]) && strtolower(substr($whereClause, $lcv, 4)) == 'and '){
                            $results[] = $result;
                            $result = array();
                            $lcv += 3; //skip the 'and' chars
                            continue;
                        }
                        if(isset($result[0]) && $result[0] == '') continue; //don't advance if we have nothing yet (leading spaces)
                        if(!isset($result[1])){
                            $result[1] = '';
                        }else if(!isset($result[2])){
                            $result[2] = '';
                        }
                    }
                    if(in_array($whereClause[$lcv], $quoteChars)){ //open a quote
                        $quoteChar = $whereClause[$lcv];
                        $inQuote = true;
                        $quotation = '';
                    }else{
                        $result[sizeof($result)-1] .= $whereClause[$lcv];
                    }
                }
            }
            $results[] = $result;
            return $results;
        }
        
        protected static function convertArrayToSearch($datatype, $query){
			return $query;
            //todo: implement... not really a priority
        }
        
        public static function generateUUID(){
            $uuid = array(
                'time_low'  => 0,
                'time_mid'  => 0,
                'time_hi'  => 0,
                'clock_seq_hi' => 0,
                'clock_seq_low' => 0,
                'node'   => array()
            );
            $uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
            $uuid['time_mid'] = mt_rand(0, 0xffff);
            $uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
            $uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
            $uuid['clock_seq_low'] = mt_rand(0, 255);
            for ($i = 0; $i < 6; $i++) {
                $uuid['node'][$i] = mt_rand(0, 255);
            }
            $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
                $uuid['time_low'],
                $uuid['time_mid'],
                $uuid['time_hi'],
                $uuid['clock_seq_hi'],
                $uuid['clock_seq_low'],
                $uuid['node'][0],
                $uuid['node'][1],
                $uuid['node'][2],
                $uuid['node'][3],
                $uuid['node'][4],
                $uuid['node'][5]
            );
            return $uuid;
        }
        
        protected function checkInitialization(){
            if( !isset($this->data[$this->primaryKey]) || empty($this->data[$this->primaryKey]) ){
                switch($this->key_type){
                    //generate a UUID for the object
                    case 'uuid':
                        $this->data[$this->primaryKey] = $this->generateUUID();
                        break;
                    case 'integer': //AKA autoincrement, implemented in the DB implementation class, not here
                        //todo: implement me
                        break;
                }
                $this->firstSave = true;
            }
        }
        
        public static function search($datatype, $query=null){
            if(is_string($query)){
                $search = Data::parseWhere($query);
            }else{
                if(is_object($query) && is_a($query, 'Search')){
                    $search = $query->discriminants;
                }else if(is_array($query)){
                    $search = $query; //Data::convertArrayToSearch($query);
                }
            }
			$dummy = new $datatype();
            if(isset($search)){
                $resultSet = $datatype::performSearch(array(
                    'type'=>$datatype,
                    'object'=>$datatype
                ), $search, Data::$registry[$dummy->database]);
                $results = array();
                foreach($resultSet as $result){
                    $object = new $datatype();
                    $object->setData($result);
                    $object->firstSave = false;
                    $results[] = $object;
                }
                return $results;
            }else{
                return array();
            }
        }
        
        //instance implementations
        public $cache = null;
        public $isNew = true;
        protected $data = array();
        protected $types = array(); //if not set, assumption is 'string'
        protected $options = array(); //[type][option_name] = value
        public $primaryKey = 'id';
        public $firstSave = false;
        public $key_type = 'uuid'; // uuid, integer, autoincrement
        
        public function __construct($value, $field=null){
            if($value != null && !empty($value)){
                $this->data = $this->load($value, $field);
                //if(empty())
                $class = get_class($this);
                Logger::log('Loading '.($class::$name).' '.$value);
            }
        }
        
        public function type($column){
            if($type = $this->types[strtolower($column)]){
                return $type;
            }else if($type = Data::$core_options[strtolower($column)]['type']){
                return $type;
            }else return false;
        }
        
        public function option($column, $name){
            //print_r($this->options); exit();
            if( ($options = $this->options[strtolower($column)]) && ($option = $options[$name]) ){
                return $option;
            }
            if($option = Data::$core_options[strtolower($column)][$name]){
                return $option;
            }
            return false;
        }
        
        public function load($id, $field=null){
            $this->isNew = false;
            return $this->performLoad($id, $field);
        }
        
        public function save(){
            $this->checkInitialization();
            $this->isNew = false;
            return $this->performSave();
        }
        
        public function increment($key, $amount=1){
            $this->set($key, $this->get($key) + $amount);
        }
        
        public function get($key){
            //todo: we should shortcut if there's no descention
            $parts = explode('.', $key);
            $current = $this->data;
            $currentPath = array();
            foreach($parts as $part){
                $currentPath[] = $part;
                if(!is_array($current) && !array_key_exists($part, $current)){
                    $warning_text = 'Value does not exist('.implode('.', $currentPath).')!';
                    switch($empty_field_mode){
                        case 'notice': //issues notice then drops through to null return 
                            trigger_error($warning_text, E_USER_NOTICE);
                        case 'null':
                            return null;
                        case 'empty':
                            return '';
                        case 'exception':
                            throw new Exception($warning_text);
                    }
                }else{
                    $current = &$current[$part];
                }
            }
            return $current;
        }
        
        public function getData(){
            return $this->data;
        }
        
        public function setData($data){
            return $this->data = $data;
        }
        
        public function set($key, $value){
            //todo: we should shortcut if there's no descention
            $parts = explode('.', $key);
            $parts = array_reverse($parts);
            $current = &$this->data;
            if(sizeof($parts) == 0) throw new Exception('Cannot set an empty value!');
            while(sizeof($parts) > 1){
                $thisPart = array_pop($parts);
                if(!is_array($current) && !array_key_exists($thisPart, $current)){
                    $current[$thisPart] = array();
                }else{
                    $current = &$current[$thisPart];
                }
            }
            $thisPart = array_pop($parts);
            if($value == null){
                unset($current[$thisPart]);
            }else{
                $current[$thisPart] = $value;
            }
        }
    }