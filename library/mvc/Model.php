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
 * (end)
 *
 * Propagation of data from Model to View:
 * <Controller>s may require automatic propagation of selected data from <Model> to <View>.
 * This is NOT an automatic behavior (checkout <Controller> for detail).
 * 
 * Data is propagated only if result is not empty. Checkout http://php.net/manual/en/function.empty.php for all values that would lead to not propagating data.
 * 
 * When controllers ask for automatic propagation of selections,
 * they are automatically set in <View> of current controller action using <Controller::propagate> method.
 * 
 * If you need to propagate data with a specific identifier, you can use the "as" feature.
 * It's just calling a unexisting Model property called "asSomeIdentifier", then calling from this property your db method.
 * 
 * (start code)
 * // Example of using the "as" feature from the controller
 * class ProductsController extends AppController {
 *		
 *		public $propagation = true ;
 * 
 *		function index ()
 *		{
 *			// Will propagate an IndexedArray of items with identifier "Products"
 *			$this->Products->getAll () ;		
 *			
 *			// Will propagate an IndexedArray of items with identifier "MySelection"
 *			$this->Products->asMySelection->getAll () ;
 *		}
 * }
 * (end)
 * 
 * Undocumented features:
 * How to use models to get data
 *
 * See also:
 * <Controller>, <AbstractDBEngine>, <IndexedArray>, <GetableCollection>, <Collection>
 */
class Model extends Object {

	static private $_mWrite = array (
		'edit',
		'editAll',
		'add',
		'addAll',
		'newId',
		'delete',
		'deleteAll'
	) ;
	
	static private $_mRead = array (
		'find',
		'findAll',
		'findFirst',
		'findAndOrder',
		'count',
		'lastId',
		'findRandom',
		'findAscendants',
		'findAndRelatives',
		'findRelatives',
		
		// New API
		'get',
		'getChilds',
		'getAndChilds',
		'getAllAndChilds',
		'getAllAndRelatives',
		'getAll',
		'getRand'
		
	) ;

	/**
	 * Convert an associative array to GetableCollection
	 *
	 * @param array $arr Array to convert
	 * @return GetableCollection Instance of GetableCollection
	 */
	static function arrayToCollectionModel ( array &$arr ) {

		$sel = new GetableCollection () ;

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
	 * @var GetableCollection
	 */
	private $_unqSel = null ;

	/*
	 * Multi selection
	 *
	 * @var IndexedArray
	 */
	private $_multiSel = null ;

	/*
	 * Do propagate data to controller and view. Default false.
	 *
	 * @var boolean
	 */
	private $_propagate = false ;
	
	/**
	 * Next selection propagation name
	 * @var string
	 */
	private $_nextPropName = null ;
	
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
	protected $_table ;


	private $_camelizedTable ;

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
	 * @param string $table [Optional] Database _table name to bind to model
	 */
	function __construct ( Controller &$controller , $db = 'main' , $table = null )
	{
		$this->controller = $controller ;
		
		$this->db = DatabaseManager::get($db);

		$this->_unqSel = new GetableCollection () ;
		
		$this->_multiSel = new IndexedArray () ;
		
		if ( !is_null($table) && !is_null($this->db) )
		{
			if ( !$this->db->tableExists($table) )
			{
				if ( debuggin () )
				{
					throw new ErrorException( 'Attempting to bind a model to database _table that does not exists: ' . $table ) ;
				} else {
					App::do500 () ;
				}
			} else {
				$this->setTable ( $table ) ;
			}
		}	
	}


	final function __call($name, $arguments) {

		array_unshift( $arguments , $this->_table ) ;

		new IndexedArray () ;

		if ( in_array ( $name , self::$_mWrite) )
		{
			return call_user_func_array(array($this->db, $name), $arguments ) ;
			
		} else if ( in_array($name, self::$_mRead) )
		{
			$result = call_user_func_array(array($this->db, $name), $arguments ) ;

			$result = $this->afterSelect( $result ) ;
			
			if ( empty ( $result ) )
			{
				return $result ;
				
			} else if (is_assoc($result))
			{
				$this->_unqSel = self::arrayToCollectionModel(camelize_keys($result)) ;
				
				if ( $this->_propagate )
				{
					$this->controller->propagate ( $this->_getPropagateName(true) , $this->_unqSel ) ;
				}

			// Int becuase count method returns a simple int
			} else if ( !is_array( $result ) )
			{
				if ( $this->_propagate )
				{
					$this->controller->propagate ( $this->_getPropagateName() , $result ) ;
				}
			} else {
				$res = $result ; 
				
				$this->_multiSel = new IndexedArray() ;

				foreach ( $result as $k => &$v )
				{
					$this->_multiSel->set ( self::arrayToCollectionModel(camelize_keys($v)) ) ;
				}

				
				if ( $this->_propagate )
				{
					$this->controller->propagate ( $this->_getPropagateName() , $this->_multiSel ) ;
				}
			}

			return $result ;
		}

		throw new ErrorException ('Method <strong>' . $name . '</strong> does not exist in class <strong>' . get_class($this) .'</strong>' );
	}
	
	
	private function _getPropagateName ( $singularize = false )
	{
		switch(true)
		{
			case !is_null ($this->_nextPropName):
				$n = $this->_nextPropName ;
				$this->_nextPropName = null ;
				return $n ;
				break;
			case $singularize === true:
				return  Inflector::singularize($this->_camelizedTable) ;
				break;
			default:
				return $this->_camelizedTable ;
		}
	}

	function __get ( $name )
	{
		if ( preg_match ('/^as[A-Z]{1}[a-zA-Z]{1,}/', $name) === 1 )
		{
			$this->_nextPropName = substr($name, 2);
			
			return $this ;
		}
		
		return $this->_unqSel->$name ;
	}

	/**
	 * Returns current unique selection if negative $index given,
	 * returns an item of current multiple selection if $index given.
	 * Default behavior (no parameters given) returns current unique selection.
	 *
	 * @param int $index [Optional] Index
	 * @return GetableCollection
	 */
	function item ( $index = -1 )
	{
		if ( $index < 0 )
		{
			return $this->_unqSel ;
		}

		return $this->_multiSel->get( $index ) ;
	}

	/**
	 * Returns current multiple selection
	 *
	 * @return IndexedArray
	 */
	function selection ()
	{
		return $this->_multiSel ;
	}

	/**
	 * Ask for automatic propagation of data from model to controller and view
	 *
	 * @param bool $bool Boolean "true" to propagate data to controller and view, false or anything else otherwise
	 * @return Model Current instance for chained command on this element
	 */
	function propagate ( $bool = false )
	{
		if ( $bool === true )
		{
			$this->_propagate = $bool ;
		}

		return $this ;
	}

	/**
	 *
	 *
	 * @return bool True if model propagating data to controller and view, false otherwise
	 */
	function isPropagating ()
	{
		return $this->_propagate ;
	}

	/**
	 * Change the table used by this model (within the same database). If database found, then new table schema is applied to model.
	 *
	 * @param string $table Name of table
	 */
	final function setTable ( $table )
	{
		$this->_table = $table ;

		$this->_camelizedTable = camelize( $table, '_' ) ;
		
		if ( !is_null($this->db) )
		{
			$this->setSchema( $this->db->getTableSchema( $table ) ) ;
		} else {
			$this->setSchema( null ) ;
		}

		return $this ;
	}

	/**
	 * Returns currently used table
	 *
	 * @return string Table name
	 */
	function getTable ()
	{
		return $this->_table ;
	}

	function getCamelizedTable ()
	{
		return $this->_camelizedTable ;
	}

	/**
	 *
	 *
	 * @return DBTableSchema Schema of the _table if exists, null otherwise
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
	 * Validate an array of data. Returns localized messages in case of failure.
	 * 
	 *
	 * @param type $data
	 */
	function validate ( $data , $result = array () )
	{
		if ( empty($result) )
		{
			$result['messages'] = array () ;
			$result['validities'] = array () ;
			$result['data'] = array () ;
		}

		foreach ( $data as $k => $v )
		{
			$v = $this->schema->validate($k, $v) ;

			if ( $v !== true )
			{
				$result['messages'][$k] = $v ;
			}
			
			$result['validities'][$this->db->getDatabaseId().'-'.$this->getTable().'-'.$k] = $v === true ? true : false ;

			$result['data'][$k] = $v ;
		}

		return $result ;
	}


	/**
	 * [Callback] Called after data has been retrieved
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
