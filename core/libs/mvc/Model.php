<?php


class Model {
	
	/**
	 * 
	 * @var Controller
	 */
	protected $controller ;
	
	/**
	 * 
	 * @var AbstractDBEngine
	 */
	protected $db ;
	
	protected $structure = array () ;
	
	protected $table ;
	
	function __construct ( Controller &$controller , AbstractDBEngine &$db = null , $table = null ) 
	{
		$this->controller = $controller ;
		
		$this->db = $db;
		
		if ( !is_null($table) )
		{
			if ( !$this->db->tableExists($table) )
			{
				if ( debuggin () )
				{
					trigger_error ( 'Attempting to bind a model to database table that does not exists: ' . $table ) ;
				} else {
					trigger_error ( 'Model error' ) ;
				}
			} else {
				$this->structure = $db->getTableStructure();
			}
		}	
	}
	
	
	function setTable ( $table )
	{
		$this->table = $table ;
	}

	function beforeAdd ( $data )
	{
		return $data ;
	}
	
	function beforeEdit ( $id , $data )
	{
		return $data ;
	}
	
	function onEdit ( $id , $data )
	{
		
	}
	
	function onAdd ( $id , $data )
	{
		
	}

	function beforeDelete ( $id )
	{
		return true ;
	}
	
	function beforeDeleteAll ( $ids )
	{
		return true ;
	}
	
	function onDelete ( $id )
	{
		
	}
	
	function onDeleteAll ( $ids )
	{
		
	}
	
}
?>