<?php


/**
 * Class: DBSchema
 *
 * This class validates and stores a database schema.
 * 
 *
 * Aenoa Server 1.0.x schemas:
 *
 * In this early version of Aenoa Server, schemas and structures are both used.
 *
 * - Structures define database schema as a data/conf file in app/structures folder
 * - Schemas are automatically created at runtime from structure file, corresponding <DBTableSchema>s are created as well
 *
 * Structures:
 *
 *
 *
 * > $field = array (
 * >		'name' => 'my_field_id',
 * >		'type' => DBSchema::TYPE_INT,
 * >		'behavior' => DBSchema::BHR_INCREMENT,
 * >		'default' => 'any value'
 * > ) ;
 *
 * for enum type, a fifth property must be applied:
 *
 * > $enumField = array (
 * >		'name' => 'my_enum_field',
 * >		'type' => DBSchema::TYPE_ENUM,
 * >		'behavior' => DBSchema::BHR_DT_ON_EDIT,
 * >		'default' => 'any value'
 * >		'values' => array ( 'any value' , 'another value' )
 * > ) ;
 *
 *
 * So here what would be an entire table structure:
 *
 * (start code)
 * $usersTable = array (
 * 		array (
 * 			'name' => 'id',
 * 			'type' => DBSchema::TYPE_INT,
 * 			'behavior' => DBSchema::BHR_INCREMENT
 * 		),
 * 		array (
 * 			'name' => 'email',
 * 			'label' => 'Email address',
 * 			'type' => DBSchema::TYPE_STRING,
 * 			'validation' => array (
 * 				'rule' => DBValidator::EMAIL,
 * 				'message' => 'The email field must be a well-formatted email address'
 * 			)
 * 		),
 * 		array (
 * 			'name' => 'password',
 * 			'label' => 'Password',
 * 			'type' =>  DBSchema::TYPE_STRING,
 * 			'behavior' => DBSchema::BHR_SHA1,
 * 			'validation' => array (
 * 				'rule' => '/[A-Za-z0-9\-_]{6,10}/',
 * 				'message' => 'Your password must contain 6 to 10 chars "A" to "Z", "a" to "z", "0" to "9", "-" and "_". '
 * 			)
 * 		),
 * 		array (
 * 			'name' => 'profile_picture',
 * 			'label' => 'Profile picture',
 * 			'type' =>  DBSchema::TYPE_FILE,
 * 			'behavior' => array ( &CallBackObjectInstance , 'methodName' , array ( 'argument 2' , 'some other argument' ) ) ,
 * 			'validation' => array (
 * 				'rule' => array ( 'jpg' , 'png' ),
 * 				'message' => 'Your profile picture must be a JPG or a PNG file.'
 * 			)
 * 		)
 * ) ;
 * (end)
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * Types:
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
 * - date
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
 * Behaviors:
 *
 *
 *
 *
 * The behaviors that must be usable in concrete engine are:
 * - BHR_INCREMENT : Behavior Increment (only for int typed fields)
 * - BHR_TS_ON_EDIT : Behavior Timestamp on edit (only for timestamp type fields)
 * - BHR_DT_ON_EDIT : Behaviour Datetime on edit (only for datetime type fields)
 * - BHR_SHA1 : Behavior sha1 (only for string type fields)
 * - BHR_MD5 : Behavior md5 (only for string type fields)
 * - BHR_URLIZE : Behavior Urlize (only for string type fields)
 *
 * Behavior for a field can be replaced by a callback method of yours.
 * Regarding this field behavior:
 *
 * (start code)
 * $profilePictureField = array (
 * 			'name' => 'profile_picture',
 * 			'label' => 'Profile picture',
 * 			'type' =>  DBSchema::TYPE_FILE,
 * 			'behavior' => array ( &CallBackObjectInstance , 'methodName' , array ( 'argument 2' , 'some other argument' ) ) ,
 * 			'validation' => array (
 * 				'rule' => array ( 'jpg' , 'png' ),
 * 				'message' => 'Your profile picture must be a JPG or a PNG file.'
 * 			);
 * (end)
 * we should have a class named CallBackObjectInstance, that implements the method methodName:
 * (start code)
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
 * (end)
 *
 * Here are some others valid callback behaviors and their corresponding functions:
 *
 * (start code)
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
 * (end)
 * and so on.
 *
 * Magic field names:
 *
 * There is 3 magic fields name,
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
 * in DBTableSchema::applyInputBehaviors method.
 *
 * Using BHR_DT_ON_EDIT or BHR_TS_ON_EDIT on magic fields 'updated' and 'modified'  is obvious:
 * the time will be updated twice.
 *
 *
 *
 *
 * Aenoa Server 1.1.x schemas:
 *
 * In this future version of Aenoa Server, schemas will be defined directly by users,
 * any "structure" concept in any part of Aenoa Server will be transformed to fit the schema spirit.
 *
 * Note that DBSchema will extends Collection, as well as DBTableSchema.
 *
 * A factory of schemas will be available to avoid complex declarations
 *
 *
 * Example of 1.1.x schema declaration:
 *
 * (start code)
 *
 * // Legal way will be in any case
 *
 * $schema = new DBSchema ( 'main' ) ;
 *
 * $userTableSchema = $schema->set ( 'users' , new DBTableSchema ( 'users' ) ) ;
 *
 * $idField = $userTableSchema->set ( 'id', new DBField ( 'id' ) ) ;
 *
 * $idField->label = _('id') ;
 *
 * $idField->type = DBField::TYPE_INT ;
 *
 * $emailField = $userTableSchema->add( new DBField ( 'email' ) ) ;
 *
 * $emailField->label = _('Email') ;
 *
 * $emailField->type = DBField::TYPE_STRING ;
 *
 *
 *
 * // Same example, but using factories
 *
 * $schema = DBSchema::create ( 'main' ,
 *				DBTableSchema::create ( 'users' ,
 *					DBField::create ( 'id' , DBField::TYPE_INT , _('id') ) ,
 *					DBField::create ( 'email' , DBField::TYPE_INT , _('id') )
 *				)
 *			);
 *
 * // but factory could be less verbose, for example a "DB" class instead of
 * // using each of classes as factory for any schema class
 * // this method is in my sense more clear than old structures formatting,
 * // or any other way using schemas in legal ways
 * // (note that way using factory has everyting legal, however
 * // it has to be considered only as a shortcut)
 *
 *
 * $schema = DB::schema ( 'main' ,
 *				DB::table ( 'users' ,
 *					DB::field ( 'id' , DBField::TYPE_INT , _('id') ) ,
 *					DB::field ( 'email' , DBField::TYPE_STRING , _('Email') )
 *				)
 *			);
 * (end)
 *
 * See also:
 * <DBTableSchema>, <AbstractDBEngine>
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