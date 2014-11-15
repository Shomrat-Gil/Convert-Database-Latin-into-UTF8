<?php
  
/*
Convert exist database tables data from Latin into UTF8 include content conversion.
based on Zend Framework 1
*/

class latin_to_utf {     
       
        protected static $instance;
        public $db;    
        protected $selected_db;    
       
        protected function __construct($selected_db){ 
                 $this->db          = Zend_Registry::get('db') ; 
                 $this->selected_db = $selected_db ; 
        }
       
       
        /**
        * get instance of latin_to_utf
        *
        * @param mixed $selected_db
        * @return latin_to_utf
        */
         public static function GetInstance($selected_db=null){
            $selected_db = filter_var($selected_db, FILTER_SANITIZE_STRIPPED);
            if(empty($selected_db)){return false;}
            if(empty(self::$instance)){
                self::$instance = new latin_to_utf($selected_db); 
            }
            return self::$instance;
        } 
       
      
       
        /**
        * convert tables, cels type and data to utf8
        *
        */
        public function convert(){
            $arr_table = $this->latin_tables();
            foreach($arr_table as $str_table){
                // change table type 
                $this->db->alter("{$this->selected_db}.{$str_table}","CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
                $this->change_columns($str_table);
                echo "Table name: {$str_table}, converted to UTF8.";
            }
            echo "---END---";
            if(empty($arr_table)){
                echo "Found no tables to converted.";
            } else{
                echo "All tables converted.";
            }
           
        }
       
         /**
        * get all tables type = latin
        *
        */
        protected function latin_tables(){
              $sql = "
                     SELECT
                        T.TABLE_NAME AS table_name
                     FROM
                        information_schema.TABLES AS T
                     WHERE
                        T.TABLE_COLLATION LIKE '%latin%'
                        AND T.TABLE_SCHEMA = '{$this->selected_db}'   
                     ";
                     $results = $this->db->fetchAll($sql);
                     $arr_tables = array();
                     foreach($results as $result){
                         $arr_tables[] = $result['table_name'];
                     }
                     return $arr_tables;
        }
       
        /**
        * change content encoding
        *
        * @param mixed $table
        */
        protected function change_columns($table){ 
            $sql = "SHOW COLUMNS FROM {$this->selected_db}.{$table} WHERE TYPE LIKE '%text%' OR TYPE LIKE '%char%'";
            $results = $this->db->fetchAll($sql);
            foreach($results as $result){ 
                $arr_save = array();
                $arr_save[$result['Field']] = "CONVERT(CONVERT(CONVERT({$result['Field']} USING latin1) USING BINARY) USING utf8)";
                $this->db->update("{$this->selected_db}.{$table}",$arr_save);
            }   
        }
       
       
       
// END CLASS
}

$convert_db_name = "YOUR-DATABASE-NAME";
$obj_convert = latin_to_utf::GetInstance($convert_db_name);
$obj_convert-$convert();

