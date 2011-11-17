<?php


/**
 * DBField class is used to formalize fields in Aenoa Server.
 *
 * <p>DBField can be used to formalize DB table schemas, widgets options</p>
 *
 * @see DBTableSchema
 * @see DBSchema
 * @see DBValidator
 * @see Widget
 */
class DBField extends AeObject {


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


	public $name ;
	
	public $label ;

	public $type ;

	public $description = '' ;
	
	public $required = false ;
	
	public $validation ;
	
	public $values ;
	
	public $value ;
	
	public $attributes = array () ;
	
	public $urlize = false ;
	
	public $valid ;
	
	public $group ;

	/**
	 * Creates a new DBField
	 *
	 * @param string $name Name of the field
	 * @param string $label Readable label of the field
	 * @param validation $validation Regexp to test to validate field value
	 * @param mixed $value Value of the field
	 */
	public function __construct ( $name = null , $type = null , $label = null , $validation = null , $value = null )
	{
		if ( !is_null($name) )
		{
			$this->name = $name ;
		}

		if ( !is_null($type) )
		{
			$this->type = $type ;
		}

		if ( !is_null($label) )
		{
			$this->label = $label ;
		}

		if ( !is_null($validation) )
		{
			$this->validation = $validation ;
		}

		if ( !is_null($value) )
		{
			$this->value = $value ;
		}
	}

	public function validate ()
	{
		// ...
	}
}
?>