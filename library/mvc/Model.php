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

	static function arrayToCollectionModel ( array &$arr ) {

		$sel = new ModelCollection () ;

		foreach ( $arr as $k => $v )
		{
			if (is_array($v))
			{
				$sel->set ( $k , self::arrayToCollectionModel($v) ) ;
			} else {
				$sel->set ( $k , $v ) ;
			}
		}

		return $sel ;
	}

	/*
	 * Unique item selection
	 *
	 * @var ModelCollection
	 */
	private $_unqSel = null ;

	/*
	 * Multi selection
	 *
	 * @var array
	 */
	private $_multiSel = array () ;

	private $_multiSelLength = 0 ;
	
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
	 * @var string
	 */
	protected $table ;


	/**
	 *
	 * @var DBTableSchema
	 */
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

		if (is_null($this->_unqSel ) )
		{
			$this->_unqSel = new ModelCollection () ;
		}
		
		if ( !is_null($table) && !is_null($this->db) )
		{
			if ( !$this->db->tableExists($table) )
			{
				if ( debuggin () )
				{
					throw new ErrorException( 'Attempting to bind a model to database table that does not exists: ' . $table ) ;
				} else {
					throw new ErrorException( 'Model error' ) ;
				}
			} else {
				
				
				$this->setTable ( $table ) ;
			}
		}	
	}


	final function __call($name, $arguments) {

		array_unshift( $arguments , $this->table ) ;

		new ModelSelection () ;

		if ( in_array ( $name , self::$_mWrite) )
		{
			return call_user_func_array(array($this->db, $name), $arguments ) ;
			
		} else if ( in_array($name, self::$_mRead) )
		{
			$result = call_user_func_array(array($this->db, $name), $arguments ) ;
			
			// Returns on resut
			if (is_assoc($result))
			{
				$this->_unqSel = self::arrayToCollectionModel(camelize_keys($result)) ;
				
			} else {

				$res = $result ; 

				foreach ( $res as $k => &$v )
				{
					if (is_array($v))
					{
						$v = self::arrayToCollectionModel(camelize_keys($v)) ;
					}
				}

				$this->_multiSel = $res ;

				$this->_multiSelLength = count($res);

			}


			return $result ;
		}

		throw new ErrorException ('Method <strong>' . $name . '</strong> does not exist in class <strong>' . get_class($this) .'</strong>' );
	}

	/**
	 * 
	 *
	 * @param type $name
	 * @return type 
	 */
	final function __get ( $name )
	{
		return $this->_unqSel->$name ;
	}

	/**
	 * Returns current unique selection
	 *
	 * @return 
	 */
	final function selection ()
	{
		return $this->_unqSel ; 
	}

	/**
	 * Returns current multiple selection
	 *
	 * @return
	 */
	final function selections ()
	{
		return $this->_multiSel ;
	}

	/**
	 * Returns ModelCollection instance of current unique selection, or of given index of current multiple selection.
	 *
	 * @param int $index [Optional] If given, must be an int
	 * @return ModelCollection If $index given, returns
	 */
	final function item( $index = null )
	{
		if ( is_null($index) )
		{
			return $this->_unqSel ;
		}

		if ( $index < $this->_multiSelLength )
		{
			return $this->_multiSel[$index] ;
		}
	}

	/**
	 * Change the table used by this model (within the same database). If database found, then new table schema is applied to model.
	 *
	 * @param string $table Name of table
	 */
	final function setTable ( $table )
	{
		$this->table = $table ;
		
		if ( !is_null($this->db) )
		{
			$this->setSchema( $this->db->getTableSchema( $table ) ) ;
		} else {
			$this->setSchema( null ) ;
		}

		return $this ;
	}

	/**
	 *
	 *
	 * @return DBTableSchema Schema of the table if exists, null otherwise
	 */
	final function getSchema ()
	{
		return $this->schema ;
	}

	/**
	 *
	 *
	 * @param type $schema
	 * @return Model
	 */
	final function setSchema ( $schema )
	{
		$this->schema = $schema ;

		return $this ;
	}

	/**
	 * [Callback][NOT WORKING YET] Called after data has been retrieved
	 *
	 * @param array $data Array of data from database
	 * @return array Array of data to return to controller
	 */
	function afterSelect ( $data )
	{
		return $data ;
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
