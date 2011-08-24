<?php

/**
 * AbstractDB describe how all DB engines work.
 * 
 * All DB engines must extend AbstractDB.
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * EXAMPLE OF STRUCTURE OF TABLES AND FIELDS
 * 
 * 
 * 
 * $field = array (
 * 		'name' => 'my_field_id',
 * 		'type' => AbstractDB::TYPE_INT,
 * 		'behavior' => AbstractDB::BHR_INCREMENT,
 * 		'default' => 'any value' 
 * ) ;
 * 
 * for enum type, a fifth property must be applied:
 * 
 * $enumField = array (
 * 		'name' => 'my_enum_field',
 * 		'type' => AbstractDB::TYPE_ENUM,
 * 		'behavior' => AbstractDB::BHR_DT_ON_EDIT,
 * 		'default' => 'any value' 
 * 		'values' => array ( 'any value' , 'another value' )
 * ) ;
 * 
 * 
 * So here what would be an entire table structure:
 * 
 * $usersTable = array (
 * 		array (
 * 			'name' => 'id',
 * 			'type' => AbstractDB::TYPE_INT,
 * 			'behavior' => AbstractDB::BHR_INCREMENT
 * 		),
 * 		array ( 
 * 			'name' => 'email',
 * 			'label' => 'Email address',
 * 			'type' => AbstractDB::TYPE_STRING,
 * 			'validation' => array (
 * 				'rule' => DBValidator::EMAIL,
 * 				'message' => 'The email field must be a well-formatted email address'
 * 			)
 * 		),
 * 		array ( 
 * 			'name' => 'password',
 * 			'label' => 'Password',
 * 			'type' =>  AbstractDB::TYPE_STRING,
 * 			'behavior' => AbstractDB::BHR_SHA1,
 * 			'validation' => array (
 * 				'rule' => '/[A-Za-z0-9\-_]{6,10}/',
 * 				'message' => 'Your password must contain 6 to 10 chars "A" to "Z", "a" to "z", "0" to "9", "-" and "_". '
 * 			)
 * 		),
 * 		array ( 
 * 			'name' => 'profile_picture',
 * 			'label' => 'Profile picture',
 * 			'type' =>  AbstractDB::TYPE_FILE,
 * 			'behavior' => array ( &CallBackObjectInstance , 'methodName' , array ( 'argument 2' , 'some other argument' ) ) ,
 * 			'validation' => array (
 * 				'rule' => array ( 'jpg' , 'png' ),
 * 				'message' => 'Your profile picture must be a JPG or a PNG file.'
 * 			)
 * 		)
 * ) ;
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * ABOUT TYPES
 * 
 * 
 * 
 * 
 * For all other types, it depends on concrete engine, but there should be at least implementation of these ones :
 * - int
 * - float
 * - string
 * - boolean
 * - enum
 * - timestamp
 * - datetime
 * 
 * Concrete engine must reflect these types, for example: in MySQL concrete engine,
 * string will correspond to TEXT, int to INT, boolean will be an enum like this one: ( 'TRUE' , 'FALSE' ), ...
 * Creating a new type (e.g. VARCHAR for MySQL) must be possible using the concrete engine.
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * ABOUT BEHAVIORS
 * 
 * 
 * 
 * 
 * The behaviors that must be usable in concrete engine are:
 * - BHR_INCREMENT : Behavior Increment (only for int typed fields)
 * 		This will increment 
 * 
 * - BHR_TS_ON_EDIT : Behavior Timestamp on edit (only for timestamp type fields)
 * - BHR_DT_ON_EDIT : Behaviour Datetime on edit (only for datetime type fields)
 * - BHR_SHA1 : Behavior sha1 (only for string type fields)
 * - BHR_MD5 : Behavior md5 (only for string type fields)
 * - BHR_URLIZE : Behavior Urlize (only for string type fields)
 * 
 * Behavior for a field can be replaced by a callback method of yours. 
 * Regarding this field behavior:
 * 
 * $profilePictureField = array ( 
 * 			'name' => 'profile_picture',
 * 			'label' => 'Profile picture',
 * 			'type' =>  AbstractDB::TYPE_FILE,
 * 			'behavior' => array ( &CallBackObjectInstance , 'methodName' , array ( 'argument 2' , 'some other argument' ) ) ,
 * 			'validation' => array (
 * 				'rule' => array ( 'jpg' , 'png' ),
 * 				'message' => 'Your profile picture must be a JPG or a PNG file.'
 * 			);
 * 
 * we should have a class named CallBackObjectInstance, that implements the method methodName:
 * 
 * class CallBackObjectInstance {
 * 		function methodName ( $fieldValue , $argument2 , $someOtherArgument ) 
 * 		{
 * 			// do something on value
 * 			if ( is_uploaded_file ( $fieldValue ) )
 * 			{
 * 				//...
 * 			}
 * 			// and return it
 * 			return $fieldValue ;
 * 		}
 * }
 * 
 * Here are some others valid callback behaviors and their corresponding functions:
 * 
 * $behaviorFunction = array ( 'function_name' ) ;
 * 
 * function function_name ( $fieldValue )
 * {
 * 		// Make something
 * 		return $fieldValue;
 * }
 * 
 * 
 * $behaviorFunctionWArgs = array ( 'function_name' , array ( 'an argument' ) ) ;
 * 
 * function function_name ( $fieldValue , $otherArgument)
 * {
 * 		// Make something
 * 		return $fieldValue;
 * }
 * 
 * and so on.
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * ABOUT SPECIAL BEHAVIOR: MAGIC FIELDS NAME
 * 
 * 
 * 
 * There is 3 magic fields name:
 * - updated
 * - modified
 * - created
 * 
 * If you add in your table structure a field named like one of these,
 * when an entry is added in your table, the 'created' field will automatically be
 * filled with current Date and Time or Timestamp.
 * And so on, if an entry is updated, the 'updated' or 'modified' or both fields are automatically filled with
 * current Date and Time or Timestamp.
 * 
 * These fields must be typed as DATETIME or as TIMESTAMP.
 * 
 * This feature is considered as an auto behavior, because the implementation of magic fields resolution is done
 * in DBHelper::applyBehaviors method.
 * 
 * Using BHR_DT_ON_EDIT or BHR_TS_ON_EDIT on magic fields 'updated' and 'modified'  is obvious:
 * the time will be updated twice. 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * ABOUT CONDITIONS IN NORMAL METHODS (findAll, findFirst, editAll, deleteAll and count)
 * 
 * You can use a strict equality creating a simple associative array:
 * 
 * // This will search for all entries which name is foo and password is bar
 * $conditions = array ( 'name' => 'foo' , 'password' => 'bar' );
 * 
 * 
 * // This will search for all entries which name is foo and password is different than bar
 * $conditions = array ( 'name' => 'foo' , 'password' => '!= bar' );
 * 
 * 
 * // This will search for all entries which name is foo and password is different than bar
 * $conditions = array ( 'datetime' => '< NOW()' , 'password' => '!= bar' );
 * 
 * 
 * 
 */

// TODO: create a checkStructure method to run before setStructure, to avoid bad structures
class AbstractDB {
	

	
	/////////////////////////////////////////////////////
	// BEHAVIORS
	
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
	

	/////////////////////////////////////////////////////
	// TYPES
	
	const TYPE_FLOAT = 'float' ;
	
	const TYPE_INT = 'int' ;
	
	const TYPE_STRING = 'string' ;
	
	const TYPE_TEXT = 'text' ;
	
	const TYPE_BOOL = 'bool' ;
	
	const TYPE_ENUM = 'enum' ;	
	
	const TYPE_TIMESTAMP = 'timestamp' ;
	
	const TYPE_DATETIME = 'datetime' ;
	
	const TYPE_FILE = 'file' ;
	
	const TYPE_PARENT = 'parent' ;
	
	const TYPE_CHILD = 'child' ;
	
	
	
	static function getPrimary ( &$tableStructure , &$fieldData = null )
	{
		$id = null ;
		$other = null ;
		
		foreach ( $tableStructure as &$field )
		{
			if ( !is_null($fieldData) )
			{
				if ( @$field['behavior'] & self::BHR_PRIMARY )
				{
					$other = $fieldData[$field['name']] ;
				} else if ( @$field['name'] == 'id' )
				{
					$id = $fieldData['id'] ;
				}
			} else {
				if ( @$field['behavior'] & self::BHR_PRIMARY )
				{
					$other =$field['name'];
				} else if ( @$field['name'] == 'id' )
				{
					$id = 'id' ;
				}
			}
		}
		
		if ( !is_null($other) )
		{
			return $other ;
		}
		
		return $id ;
	}
	
	static function getSearchable ( &$tableStructure )
	{
		foreach ( $tableStructure as &$field )
		{
			if ( array_key_exists('searchable', $field ) && $field['searchable'] == true )
			{
				return $field['name'] ;
			}
		}
		
		return false ;
	}
	
	static function getFilterable ( &$tableStructure )
	{
		foreach ( $tableStructure as &$field )
		{
			if ( array_key_exists('filterable', $field ) && $field['filterable'] == true )
			{
				return $field['name'] ;
			}
		}
		
		return false ;
	}
	
	
	
	/////////////////////////////////////////////////////
	// Properties usable in concrete classes
	
	
	/**
	 * An untyped reference to what is a database for a concrete engine
	 * e.g. In concrete MySQL DB, $database will be an array containing db login data
	 * if JSONDatabase, $database will be a string containing path to the JSON file
	 * and so on...
	 * 
	 * @access protected
	 */
	protected $database ;  
	
	/**
	 * Data stored in memory
	 * 
	 * Some engines does not need this 
	 * 
	 * @var
	 */
	protected $data ;
	
	/**
	 * Structure of database
	 * 
	 * @var
	 */
	protected $structure = array () ;
	
	/**
	 * Indexed structure of database
	 * 
	 * @var
	 */
	protected $struct = array () ;
	
	/**
	 * List of tables 
	 * 
	 * @var
	 */
	protected $tables = array () ;
	
	
	/**
	 * Last id
	 * 
	 * @var
	 */
	protected $__lastId ;
	
	
	protected $id ;

	
	protected $_log = array () ;
	
	protected $hasStructureDeploymentError = false ;
	
	function getLog ()
	{
		return $this->_log ;
	}
	
	function setID ( $id )
	{
		$this->id = $id ;
	}
	
	
	/////////////////////////////////////////////////////
	// Magic methods
	
	
	final function __construct ()
	{
		
	}
	
	final function __destruct ()
	{
		
	}
	
	
	
	
	/////////////////////////////////////////////////////
	// Methods to override 
	
	
	/**
	 * This method open the database
	 * 
	 * @return 
	 */
	function open ()
	{
		return false ;
	}
	
	/**
	 * This method close the database
	 * 
	 * @return 
	 */
	function close ()
	{
		return false ;
	}
	
	/**
	 * This method only tests connectivity without opening any database.
	 * 
	 * For some concrete engines, like JSONDBEngine, $host should be the path of the JSON DB file. 
	 * 
	 * @param $host
	 * @param $login
	 * @param $password
	 * @return unknown_type
	 */
	function test ( $host , $login , $password )
	{
		return false ;
	}
	
	/**
	 * Check if engine is usable and database is opened and ready
	 * 
	 * @return bool True if db engine is usable, false otherwise
	 */
	function isUsable () { return false; }
	
	function getIdentifiers () {
		return $this->database ;
	}
	
	/**
	 * Set database source
	 * 
	 * If a database was opened yet, then this one will be closed before creating new database.
	 * New database will be set as current database.
	 * 
	 * If the database is the same as the current database, then setSource will always return true.
	 * 
	 * The create option may be unavailable in some engines.
	 * 
	 * @see AbstractDB::createSource
	 * @param string $database
	 * @param object $create [optional] Create DB if database does not exists
	 * @return bool True if database exists and is usable, false otherwise
	 */
	function setSource ( $database , $create = false ) { return false; }
	
	/**
	 * Create database source
	 * 
	 * If a database was opened yet, then this one will be closed before creating new database.
	 * New database will be set as current database.
	 * 
	 * This method can be unavailable depending on concrete database engine.
	 * Refer to AbstractDB::hasCreationCapability to know if an engine can create new databases.
	 * 
	 * @param string $database
	 * @return bool True if database has been created, false otherwise
	 */
	function createSource ( $database ) { return false; }
	
	/**
	 * Returns true if concrete engine can create a new database
	 * 
	 * @return bool
	 */
	function hasCreationCapability () { return false; }
	
	/**
	 * Check if a database source exists
	 * 
	 * @param object $database
	 * @return bool
	 */
	function sourceExists ( $database ) { return false; }
	
	/**
	 * Returns structure of all tables
	 * 
	 * @return array The structure (fields list) of a table, false if table is not found or if concrete engine
	 * has no structure capability.
	 * 
	 */
	function getStructure (  ) {
		return $this->struct ;
	}

	/**
	 * Returns structure of a table
	 * 
	 * @return array The structure (fields list) of a table, false if table is not found or if concrete engine
	 * has no structure capability.
	 * 
	 */
	function getTableStructure ( $table ) {
		if ( $this->tableExists($table) )
		{
			return $this->struct[$table] ;
		} 
		
		return false ;
	}
	
	/**
	 * Set structure of database
	 * 
	 * If table does not exists, then it should be created
	 * 
	 * The method can be unavailable depending on concrete database engine.
	 * Use hasStructureCapability method to define if query is usable or not in
	 * concrete classe.
	 * 
	 * @return True if structure has been set, fals otherwise
	 */
	function setStructure ( &$structure = array () , $create = false ) { 
		return false;
	}
	
	/**
	 * Returns true if concrete engine can create db structure
	 * 
	 * @return bool
	 */
	function hasStructureCapability () { return false; }
	
	/**
	 * Perform a user query on a table
	 * 
	 * The method can be unavailable depending on concrete database engine.
	 * Use hasQueryCapability method to define if query is usable or not in
	 * concrete classe.
	 * 
	 * @return array Results if found, false on error
	 */
	function query ( $query ) { return false; }
	
	/**
	 * Returns true if concrete engine can perform direct queries
	 * 
	 * @return bool
	 */
	function hasQueryCapability () { return false; }
	
	
	/**
	 * Returns true if a table exists, false otherwise
	 * 
	 * @param object $tableName
	 * @return bool True if table exists, false otherwise
	 */
	function tableExists ( $tableName ) 
	{
		return false ;
	}
	
	
	/**
	 * Find an entry in a table based on ID
	 * 
	 * @param object $table The table where to search
	 * @param object $id The ID of an entry
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @return bool False on failure, entries on success
	 */
	function find ( $table , $id , $fields = array () ) { return false; }
	
	/**
	 * Find all entries in a table, depending on conditions
	 * 
	 * @param object $table The table where to search
	 * @param object $cond [optional] Conditions
	 * @param object $limit [optional] Max returned entries
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @return bool False on failure, entries on success
	 */
	function findAll ( $table , $cond = array () , $limit = 0 , $fields = array ()  ) { return false; }
	
	/**
	 * Find first entry in a table, depending on conditions
	 * 
	 * @param object $table The table where to search
	 * @param object $cond [optional] Conditions
	 * @param object $fields [optional] Fields to return, default will return all fields
	 * @return bool False on failure, entry on success
	 */
	function findFirst ( $table , $cond = array () , $fields = array ()  ) { return false; }
	
	/**
	 * Edit an entry
	 * 
	 * @param object $table The table where to search
	 * @param object $id The ID of entry to edit
	 * @param object $content [optional] Entry content
	 * @return bool False on failure, true on success
	 */
	function edit ( $table , $id , $content = array () ) { return false; }
	
	/**
	 * Edit all entries based on some conditions
	 * 
	 * @param object $table The table where to search
	 * @param object $content [optional] Entry content
	 * @param array $cond An array of conditions
	 * @return bool False on failure, true on success
	 */
	function editAll ( $table , $content = array () , $cond = array () ) { return false ; }
	
	/**
	 * Add an entry
	 * 
	 * @param object $table The table where to add entry
	 * @param object $content [optional]
	 * @return bool False on failure, true on success
	 */
	function add ( $table , $content = array () ) { return false; }
	
	/**
	 * Add many entries
	 * 
	 * @param object $table The table where to add entries
	 * @param object $rows [optional] The rows to add
	 * @return bool False on failure, true on success
	 */
	function addAll ( $table , $rows = array () ) { return false; }
	
	/**
	 * Return count of entries depending on conditions
	 * 
	 * @param object $table The table where to add entry
	 * @param array $cond [optional]
	 * @return false on failure, entries count on success
	 */
	function count ( $table , $cond = array () ) { return false; }
	
	/**
	 * Return last ID 
	 * @return  last ID
	 */
	function lastId ( $table ) { return false; }
	
	/**
	 * Return a new ID
	 * 
	 * In some concrete engines, ID will be set automatically by the Database when inserting a new entry,
	 * so this method is only for virtual DB (CVS, JSON...).
	 * 
	 *  
	 * @return mixed New ID if possible, false otherwise
	 */
	function newId ( $table ) { return false; }
	
	/**
	 * Delete an entry based on its ID
	 * 
	 * @param object $table The table where to search
	 * @param object $id The ID of entry to delete
	 * @return bool False on failure, true on success
	 */
	function delete ( $table , $id ) { return false; }
	
	/**
	 * Delete an entry based on its ID
	 * 
	 * @param object $table The table where to search
	 * @param array $cond [optional]
	 * @return bool False on failure, true on success
	 */
	function deleteAll ( $table , $cond = array () ) { return false; }
	
	
	
	
	/////////////////////////////////////////////////////
	// Help methods
	
	/**
	 * In a row array, this method will select only $fields entries
	 * 
	 * @param object $row [optional]
	 * @param object $fields [optional]
	 * @return Array of fields
	 */
	function selectFieldsInRow ( &$row = array () , &$fields = array () )
	{
		if ( empty ( $row ) || empty( $fields ) )
		{
			return $row ;
		}
		
		$result = array () ;
		
		foreach ( $fields as &$fieldName )
		{
			if ( array_key_exists ( $fieldName , $row ) )
			{
				$result[$fieldName] = &$row[$fieldName] ;
			}
		}
		
		return $result ;
	}
	
	/**
	 * Get one or many rows in an array depending on a strict equality condition
	 * 
	 * @param object $table
	 * @param object $fieldName
	 * @param object $fieldValue
	 * @return 
	 */
	function getRows ( &$table , $fieldName , $fieldValue , $first = false )
	{
		if ( empty ( $table ) )
		{
			return array () ;
		}
		
		$result = array () ;
		
		foreach ( $table as &$row ) 
		{
			if ( array_key_exists($fieldName, $row ) && $row[$fieldName] == $fieldValue )
			{
				if ( $first == true )
				{
					return $row ;
				}
				
				$result[] = $row ;
			}
		}
		return $result ;
	}
	
	
}

















?>