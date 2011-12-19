<?php


/**
 * <p>This class validates and stores a database schema.</p>
 *
 * <p>It stores too all predefined values of databases schemas (Types, Behaviors...)</p>
 *
 * <p>It is used by most of DB classes to access to database structure.</p>
 *
 * @see DBTableSchema
 */
class DBSchema extends Object {


	/**
	 * An untyped reference to what is a database for a concrete engine
	 * e.g. In concrete MySQL DB, $database will be an array containing db login data
	 * if JSONDatabase, $database will be a string containing path to the JSON file
	 * and so on...
	 *
	 * @access protected
	 * @var array
	 */
	protected $databaseId ;


	/**
	 * List of schemas per table
	 *
	 * Each value of this array is a <DBTableSchema> instance
	 *
	 * @var array
	 */
	protected $schema = array () ;

	/////////////////////////////////////////////////////
	// TYPES

	/**
	 * Float type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_FLOAT = 'float' ;

	/**
	 * Int type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_INT = 'int' ;

	/**
	 * String type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_STRING = 'string' ;

	/**
	 * Text type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_TEXT = 'text' ;

	/**
	 * Boolean type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_BOOL = 'bool' ;

	/**
	 * Enum type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_ENUM = 'enum' ;

	/**
	 * Timestamp type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_TIMESTAMP = 'timestamp' ;

	/**
	 * Datetime type definition
	 *
	 * <p>Format: <strong>0000-00-00 00:00:00</strong></p>
	 *
	 * @var string
	 */
	const TYPE_DATETIME = 'datetime' ;

	/**
	 * Date type defintion
	 *
	 * <p>Format: <strong>0000-00-00</strong></p>
	 *
	 * @var string
	 */
	const TYPE_DATE = 'date' ;

	/**
	 * File type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_FILE = 'file' ;

	/**
	 * Parent type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_PARENT = 'parent' ;

	/**
	 * Child type definition
	 *
	 *
	 * @var string
	 */
	const TYPE_CHILD = 'child' ;

	/**
	 * Document type definition
	 * 
	 * Document is a JSON file stored in a protected location. Format of the JSON is free.
	 * 
	 * Fields that have this type are never really created in Database.
	 * 
	 *
	 * @var string
	 */
	const TYPE_DOCUMENT = 'document' ;


	/////////////////////////////////////////////////////
	// BEHAVIORS

	/**
	 * Auto increment behavior definition
	 *
	 *
	 * @var int
	 */
	const BHR_INCREMENT = 1 ;

	const BHR_DT_ON_EDIT = 2 ;

	const BHR_TS_ON_EDIT = 4 ;

	const BHR_SHA1 = 8 ;

	const BHR_MD5 = 16 ;

	const BHR_URLIZE = 32 ;

	const BHR_AS_CHILD = 64 ;

	const BHR_AS_CODE = 128 ;

	const BHR_PICK_IN = 256 ;

	const BHR_PICK_ONE = 512 ;

	const BHR_UNIQUE = 1024 ;

	const BHR_UNEDITABLE = 2048 ;

	const BHR_PRIMARY = 4096 ;

	const BHR_LAT_LNG = 8192 ;

	const BHR_SERIALIZED = 16384 ;

	final function __construct ( $id , $schema )
	{
		$this->databaseId = $id ;

		foreach ( $schema as $name => $fields )
		{
			$this->schema[$name] = new DBTableSchema($name, $fields) ;
		}
	}

	function getDatabaseId ()
	{
		return $this->databaseId ;
	}


	function getSchema ()
	{
		return $this->schema ;
	}

	/**
	 * Get the DBTableSchema instance of a table
	 *
	 *
	 * @param string $name
	 * @return DBTableSchema
	 */
	function getTableSchema ( $name )
	{
		if ( $this->tableExists( $name ) )
		{
			return $this->schema[$name] ;
		}

		return null ;
	}


	/**
	 * Returns true if a table exists, false otherwise
	 *
	 * @param object $tableName
	 * @return bool True if table exists, false otherwise
	 */
	function tableExists ( $name )
	{
		return ake( $name, $this->schema ) ;
	}
	
	/**
	 * Trigger an HTTP 403 error if given table does not exists, returns table schema if table exists
	 * 
	 * 
	 * @param string $name Name table
	 * @return DBTableSchema
	 */
	function tableExistsOr403 ( $name )
	{
		if( !$this->tableExists( $name ) )
		{
			App::do403( sprintf( _('Table %s does not exists' ), $name ) ) ;
		}
		
		return $this->schema[$name] ;
	}

}



?>
