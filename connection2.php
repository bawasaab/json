<?php
	
	class Connection
	{
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
	    protected $qryObj = '';

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
	                if(property_exists('Connection',$k)){
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

	    protected function query( $queryString )
		{
			$this->qryObj = $this->db->query( $queryString );
			return $this->qryObj;
		}

		private function escape( $params )
		{
			return $this->db->real_escape_string( trim( $params ) );
		}

		public function sanitize( $params )
		{
			if( is_array( $params ) )
			{
				$result = array();

				foreach( $params AS $key => $value )
				{
					$result[$key] = $this->escape( $value );
				} 
				return $result;
			}
			
			return $this->escape( $params );
		}

		protected function Insert($InsertArr, $table)
	    {
	        if( is_array( $InsertArr ) && !empty( $table ) )
	        {
		        $k = $v = '';
		        
	            foreach($InsertArr AS $key => $val)
	            {
	                $k .= $key.',';
	                $v .= "'".$val."',";
	            }
	            
	            $qry = "INSERT INTO ". $table ."(". trim( $k, ',' ) .") VALUES(". trim( $v, ',' ) .")"; 
	            return $this->query( $qry );
	        }
	        return FALSE;
	    }

	    protected function Update( $InsertArr, $table, $condition )
	    {
	    	$set = '';

	    	if( is_array( $InsertArr ) && !empty( $table ) && !empty( $condition ) )
	        {
	            foreach($InsertArr AS $key => $val)
	            {
	                $set .= $key ." = '". $val ."', ";
	            }

	            $qry = "UPDATE ". $table ." SET ". trim( $set, ", ") ." WHERE ". $condition; 
	            return $this->query( $qry ); 
	        }
	        return FALSE;
	    }

	    //Tested OK
	    protected function fetch_assoc_array()
	    {
	    	if( is_object( $this->qryObj ) )
	    	{
	    		if( $this->qryObj->num_rows > 0 )
	    		{
	    			$data = array();
	    			while( $row = $this->qryObj->fetch_assoc() )
	    			{
	    				$data[] = $row;
	    			} 
	    			return $data;
	    		}
	    		return FALSE;
	    	}
	    	return FALSE;
	    }

	    //Tested OK
	    protected function fetch_assoc_row()
	    {
	    	if( is_object( $this->qryObj ) )
	    	{
	    		return $this->qryObj->num_rows > 0 ? $this->qryObj->fetch_assoc() : FALSE;
	    	}
	    	return FALSE;
	    }

	    private function set_query( $data )
	    {
	    	$queryString = "SELECT ". $data['selectColumns'] ." 
	        				FROM ". $data['tableName'] ." 
	        				WHERE ". $data['condition'] ."
	        				ORDER BY ". $data['order_by'];

	        return isset( $data['offset'] ) && isset( $data['limit'] ) && $data['limit'] > 0 && $data['offset'] >= 0 ? $queryString .= " LIMIT ". $data['offset'] .", ". $data['limit'] : $queryString;
	    }

	    //Tested OKK
	    protected function getRow( $data )
	    {
	        if( !is_array( $data ) || empty( $data ) )
	        {
	            return FALSE;
	        }

	        $this->qryObj = $this->query( $this->set_query( $data ) );
	        return $this->fetch_assoc_row();
	    }

	    //Tested OKK
	    public function getRows( $data )
	    {
	        if( !is_array( $data ) || empty( $data ) )
	        {
	            return FALSE;
	        }

	        $this->qryObj = $this->query( $this->set_query( $data ) );
	        return $this->fetch_assoc_array();
	    }

	    //Tested OK
	    protected function fetch_obj_array()
	    {
	    	if( is_object( $this->qryObj ) )
	    	{
	    		if( $this->qryObj->num_rows > 0 )
	    		{
	    			$data = array();
	    			while( $row = $this->qryObj->fetch_object() )
	    			{
	    				$data[] = $row;
	    			} 
	    			return $data;
	    		}
	    		return FALSE;
	    	}
	    	return FALSE;
	    }

	    //Tested OK
	    protected function fetch_obj_row()
	    {
	    	if( is_object( $this->qryObj ) )
	    	{
	    		return $this->qryObj->num_rows > 0 ? $this->qryObj->fetch_object() : FALSE;
	    	}
	    	return FALSE;
	    }

	    //Complications
	    protected function fetch_assoc_row1()
	    {
	    	if( is_object( $this->qryObj ) )
	    	{
	    		return $this->qryObj->num_rows > 0 ? $this->qryObj->fetch_row() : FALSE;
	    	}
	    	return FALSE;
	    }

	    //Complications
	    protected function fetch_obj()
	    {
	    	if( is_object( $this->qryObj ) )
	    	{
	    		if( $this->qryObj->num_rows > 0 )
	    		{ 
	    			return $this->qryObj->fetch_object();
	    		}
	    		return FALSE;
	    	}
	    	return FALSE;
	    }

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

	    public function __destruct()
	    {
	        $this->db->close();
	        unset( $this );
	    }
	}
?>
