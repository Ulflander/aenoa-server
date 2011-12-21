<?php

/*
 * Class: Model
 *
 * <Model> is
 * 
 */
class Model extends Object {

	static private $_mWrite = array (
		'edit',
		'editAll',
		'add',
		'addAll',
		'count',
		'lastId',
		'newId',
		'delete',
		'deleteAll'
	) ;
	
	static private $_mRead = array (
		'find',
		'findAll',
		'findFirst',
		'findAndOrder',
		'findRandom',
		'findAscendants',
		'findAndRelatives',
		'findRelatives'
	) ;
	
	public $selection = array () ;

	final function __call($name, $arguments) {

		array_unshift( $arguments , $this->table ) ;
			
		if ( in_array ( $name , self::$_mWrite) )
		{
			pr($arguments);
			
			return call_user_func_array(array($this->db, $name), $arguments ) ;
		} else if ( in_array($name, self::$_mRead) )
		{
			$result = call_user_func_array(array($this->db, $name), $arguments ) ;
			
			$this->selection = $result ;
			
			return $result ;
		}

		throw new ErrorException('Method ' . $name . ' does not exist in class ' . get_class($this) );
	}
	
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
				
				
				$this->setTable ( $table ) ;
				
				$this->structure = $db->getTableStructure( $table );
			}
		}	
	}
	
	
	final function setTable ( $table )
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
