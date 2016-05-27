<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 
 * Common Functions.
 * @author Deepak Bawa
 */
class db_core {
    
    /**
     *
     * @var string $host_name used to hold name of host.
     * @access private
     */
    private $host_name = '';
    /**
     *
     * @var string $user_name used to hold privilege name
     * @access private
     */
    private $user_name = '';
    /**
     *
     * @var $password used to hold password
     * @access private
     */
    private $password = '';
    /**
     *
     * @var string $database_name used to hold database name
     * @access private
     */
    private $database_name = '';
    /**
     *
     * @var object of connection 
     * @access public
     */
    public $db = '';

    /**
     * 
     * @param array $param key-value pair of DB connection details
     */
    function __construct($param = array()) {
        $this->initialize($param);
    }
    
    /**
     * 
     * Initialize the values of the variables
     * @param array $param key-value pair of DB connection details
     */
    private function initialize($param = array()) 
    {
        if(count($param)){
            foreach($param AS $k => $v){
                if(property_exists('db_core',$k)){
                    $this->$k = $v;
                }
            }
        }
        $this->connect();
    }
    
    /**
     * 
     * Create connection with DB and initialize the connection object
     */
    private function connect()
    {
        $this->db = new mysqli($this->host_name, $this->user_name, $this->password, $this->database_name);
        if ($this->db->connect_error) {
            die("Connect failed: ". $this->db->connect_error);
            exit();
        }
    }
    
    /**
     * 
     * base_url Generate host address for server and local machine
     * @return String host address
     */
    public function base_url()
    {
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/^((\[[0-9a-f:]+\])|(\d{1,3}(\.\d{1,3}){3})|[a-z0-9\-\.]+)(:\d+)?$/i', $_SERVER['HTTP_HOST']))
        {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $baseurl = $protocol.$_SERVER['HTTP_HOST'].substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
        }
        else
        {
            $baseurl = 'http://localhost/';
        }

        return $baseurl;
    }
    
    /**
     * 
     * Sanitize the input either array or string
     * @param  Array/String $param Data for sanitize
     * @return Array/String        Sanitized data
     */
    public function sanitize($param)
    {
        $data = [];
        if(is_string($param) || is_numeric($param) )
        {
            return $aparam = $this->db->real_escape_string(trim($param));
        }
        elseif(is_array($param))
        {
            foreach ($param as $key => $value) 
            {
                $data[$key] = $this->db->real_escape_string(trim($value));
            }
            return $data;
        } 
        else 
        {
            return $param;
        }
    }
    
    /**
     * 
     * Count table rows and return number of row value in integer
     * @param  string  $tableName name of table
     * @param  integer/string $cond condition for how to get data
     * @return boolean/interger no record found/total rows count
     */
    public function countRows($tableName, $cond = 1)
    {
        $qry = "SELECT count(*) as cnt FROM ".$tableName." WHERE ".$cond;
        $result = $this->db->query($qry) or die($this->db->error);
        
        if($result->num_rows)
        {
            $row = $result->fetch_assoc();
            return $row['cnt'];
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * 
     * createId To generate new id
     * @param  string $letter prefix of id that will be used newly generated id
     * @param  string $idColoum Name of column
     * @param  string $tableName Name of table
     * @return boolean/string no record found/new generated id
     */
    public function createId($letter, $idColoum, $tableName)
    {
        $qry = "SELECT ". $idColoum ." as id FROM ". $tableName ." ORDER BY ". $idColoum ." DESC";
        $result = $this->db->query($qry) or die($this->db->error);
        
        if($result->num_rows)
        {
            $row = $result->fetch_assoc();
            $str = substr($row['id'], 2);
            $prodStageId = intval($str) + 1;
            $zero = str_pad($prodStageId, 3, '0', STR_PAD_LEFT);
            $final = $letter.$zero;
            return $final;
        }
        else 
        {
            $prodStageId = 1;
            $zero = str_pad($prodStageId, 3, '0', STR_PAD_LEFT);
            $final = $letter.$zero;
            return $final;
        }
    }
    
    /**
     * 
     * @param string $param string to be matched for duplication
     * @param array $dbParams contains column names and table name
     * @param string/bool $id used in update case to verify record duplication
     * @return boolean true if duplicate found false otherwise
     */
    public function isExist($param, $dbParams, $id = FALSE) 
    {
        if($id)
        {
            $qry = "SELECT ". $dbParams['dbColoumName'] ." as col FROM ". $dbParams['tableName'] ." WHERE ". $dbParams['dbColoumName'] ." = '".$this->sanitize($param)."' AND ". $dbParams['dbColoumId'] ." != '". $id ."'";
        }
        else
        {
            $qry = "SELECT ". $dbParams['dbColoumName'] ." as col FROM ". $dbParams['tableName'] ." WHERE ". $dbParams['dbColoumName'] ." = '".$this->sanitize($param)."'";
        }
        
        $result = $this->db->query($qry) or die($this->db->error);
        
        if($result->num_rows)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }   
    }
    
    /**
     * 
     * @param array $data contains column names, table name and condition
     * @return boolean/array 
     */
    public function getRow($data)
    {
        $qry = "SELECT ". $data['selectColumns'] ." FROM ". $data['tableName'] ." WHERE ". $data['condition'];
        $result = $this->db->query($qry) or die($this->db->error);
        
        if($result->num_rows)
        {
            return $result->fetch_assoc();
        }
        else
        {
            return FALSE;
        }
    }
    
    public function __destruct() 
    {
        //mysqli_close($this->db);
        $this->db->close();
    }
}
