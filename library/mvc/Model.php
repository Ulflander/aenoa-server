<?php

/*
 * Class: Model
 *
 * <Model> is
 * 
 */
class Model extends Object {

	static private $methods = array (
		'find',
		'findAll',
		'findFirst',
		'findAndOrder',
		'findRandom',
		'findAscendants',
		'findAndRelatives',
		'findRelatives',
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

	final function __call($name, $arguments) {

		if ( in_array ( $name , self::$methods) )
		{
			array_unshift( $arguments , $this->table ) ;

			return call_user_method_array($name, $this->db, $arguments ) ;
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
