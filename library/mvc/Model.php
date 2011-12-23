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

	/**
	 *
	 * @var type
	 */
	protected $structure = array () ;
	
	protected $table ;

	protected $schema ;

	/**
	 * Creates a new model.
	 *
	 * 
	 * @see Controller::setModels
	 * @param Controller $controller Controller that load the model
	 * @param string $db [Optional] Database identifier, default is "main"
	 * @param string $table [Optional] Database table name to bind to model
	 */
	function __construct ( Controller &$controller , $db = 'main' , $table = null )
	{
		$this->controller = $controller ;
		
		$this->db = DatabaseManager::get($db);
		
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
				
				$this->structure = $this->db->getTableStructure( $table );
			}
		}	
	}
	
	
	final function setTable ( $table )
	{
		$this->table = $table ;
	}

	final function getSchema ()
	{
		return $this->schema ;
	}

	final function setSchema ( $schema )
	{
		$this->schema = $schema ;
	}

	/**
	 * [Callback] Called before data addition
	 *
	 * @param array $data Data to save
	 * @return array Data to save
	 */
	function beforeAdd ( $data )
	{
		return $data ;
	}

	/**
	 * [Callback] Called before data edition
	 *
	 * @param mixed $id Identifier of data row to save 
	 * @param array $data Data to save
	 * @return array Data to save
	 */
	function beforeEdit ( $id , $data )
	{
		return $data ;
	}

	/**
	 * [Callback] Called after data edited
	 *
	 * @param type $id
	 * @param type $data
	 */
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
