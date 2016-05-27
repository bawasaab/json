<?php
/**
* 
*/
namespace db;
use db\Config;

class Connection extends Config
{
	protected $mysqli = '';
	protected $qryObj = '';

	function __construct()
	{
		parent::__construct();
		$this->connection();
	}

	protected function connection()
	{
		$this->mysqli = new \mysqli($this::HOST, $this::USER, $this::PASSWORD, $this::DATABASE);
	}

	protected function triggerQery($queryString)
	{
		$executeQuery = $this->mysqli->query($queryString) or die($this->mysqli->error);
		return $this->qryObj = $executeQuery;
	}

	protected function escape( $params )
	{
		return $this->mysqli->real_escape_string( trim( $params ) );
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
		}
		else
		{
			$result = '';

			$result = $this->escape( $params );
		}

		return $result;
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
            
            $qryObj = "INSERT INTO ".$table."(".trim($k,',').") VALUES(".trim($v,',').")"; 
            return $this->triggerQery( $qryObj );
        }
        return FALSE;
    }

    protected function Update($InsertArr, $table, $condition)
    {
    	$set = '';

    	if(is_array($InsertArr) && !empty($table) && !empty($condition))
        {
            foreach($InsertArr AS $key => $val)
            {
                $set .= $key ." = '". $val ."', ";
            }

            $qry = "UPDATE ". $table ." SET ". trim( $set , ", ") ." WHERE ". $condition; 
            return $this->triggerQery( $qry ); 
        }
        return FALSE;
    }

    public function countRows($tableName, $cond = 1)
    {
        $qry = "SELECT count(*) as cnt FROM ".$tableName." WHERE status != 'DELETED' AND ".$cond;
        $this->triggerQery($qry);
        
        if($row = $this->getAssocRow())
        {
            return $row['cnt'];
		}
		return false;
    }

    protected function getAssocRow()
    {
    	if( is_object( $this->qryObj ) )
    	{
    		return $this->qryObj->num_rows > 0 ? $this->qryObj->fetch_assoc() : FALSE;
    	}
    	return FALSE;
    }

    protected function getAssocRows()
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

    public function isExist($param, $dbParams, $id = FALSE) 
    {
        if($id)
        {
            $qry = "SELECT ". $dbParams['dbColoumName'] ." as col FROM ". $dbParams['tableName'] ." WHERE ". $dbParams['dbColoumName'] ." = '".$param."' AND ". $dbParams['dbColoumId'] ." != '". $id ."'";
        }
        else
        {
            $qry = "SELECT ". $dbParams['dbColoumName'] ." as col FROM ". $dbParams['tableName'] ." WHERE ". $dbParams['dbColoumName'] ." = '".$param."'";
        }
        
        $this->triggerQery($qry);
        
        if($this->getAssocRow())
        {
            return TRUE;
        }
        return FALSE;   
    }
}
?>
