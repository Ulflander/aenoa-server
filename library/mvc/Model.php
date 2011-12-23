<?php

/*
 * Class: Model
 *
 * Callbacks:
 * Model contains callbacks that are automatically called when data is added, edited or deleted.
 * You should override callbacks in concrete <Model>s classes.
 * 
 * Callback example:
 * In this example we will avoid saving data in case a field has a forbidden value.
 * Please note we could do that using structure validation.
 * (start code)
 * class FooModel extends Model {
 *		
 *		function beforeAdd ( $data )
 *		{
 *			// If data is not valid
 *			if ( $data['field'] == 'unauthorized_value' )
 *			{
 *				// We do not want to save it
 *				return false ;
 *			}
 *			
 *			// Data is valid, let's save it
 *			return $data ;
 *		}
 * }
 * 
 * (end)
 * 
 * See also:
 * <Controller>
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
//	protected $structure = array () ;
	
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
		
		if ( !is_null($table) && !is_null($this->db) )
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
			}
		}	
	}
	
	
	final function setTable ( $table )
	{
		$this->table = $table ;
		
		if ( !is_null($this->db) )
		{
			$this->setSchema( $this->db->getTableSchema( $table ) ) ;
		}
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
	 * @return mixed Array of data to save, or boolean false if data must not be saved
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
	 * @return mixed Array of data to save, or boolean false if data must not be saved
	 */
	function beforeEdit ( $id , $data )
	{
		return $data ;
	}

	/**
	 * [Callback] Called after data edited
	 *
	 * @param mixed $id Identifier of data row
	 * @param array $data Saved data
	 */
	function onEdit ( $id , $data )
	{
		
	}
	

	/**
	 * [Callback] Called after data added
	 *
	 * @param mixed $id Identifier of data row
	 * @param array $data Saved data
	 */
	function onAdd ( $id , $data )
	{
		
	}

	/**
	 * [Callback] Called before data deletion
	 *
	 * @param mixed $id Identifier of data row to be deleted 
	 * @return boolean True if data can be deleted, false otherwise
	 */
	function beforeDelete ( $id )
	{
		return true ;
	}
	
	/**
	 * [Callback] Called before multiple data deletion
	 *
	 * @param array $ids Array of identifiers of data rows to be deleted 
	 * @return boolean True if data can be deleted, false otherwise
	 */
	function beforeDeleteAll ( $ids )
	{
		return true ;
	}
	
	/**
	 * [Callback] Called after data deletion
	 * 
	 * @param mixed $id Identifier of deleted data
	 */
	function onDelete ( $id )
	{
		
	}
	
	/**
	 * [Callback] Called after multiple data deletion
	 * 
	 * @param array $ids Array of identifiers of deleted data rows
	 */
	function onDeleteAll ( $ids )
	{
		
	}
	
}
?>
