<?php

/**
 * The DBTableSchema validates and stores a table schema, offering methods to easily access to fields descriptions
 *
 */
class DBTableSchema extends AeObject {

	protected $_name = '' ;

	protected $_fields = array () ;

	protected $_len  = 0 ;

	protected $_primary ;
	
	protected $_initialStructure = array () ;
	
	/**
	 * Create a new table schema
	 *
	 * @param string $name Table name
	 * @param array $fields Fields schema
	 */
	function __construct ( $name , $fields )
	{
		$this->_name = $name ;
		
		$primary = null ;
		$defPrimary = null ;

		foreach ( $fields as $f )
		{
		    $r = null ;
		    
			if ( ake ( 'name', $f ) && ($r = $this->validateField($f)) === true )
			{
				$this->_fields[$f['name']] = $f ;
				
				if ( @$f['behavior'] & DBSchema::BHR_PRIMARY )
				{
					$primary = $f['name'];
				} else if ( @$f['name'] == 'id' )
				{
					$defPrimary = 'id' ;
				}
				
				$this->_initialStructure[$f['name']] = $f ;
			} else if ( !is_null($r) )
			{
				$this->debug($r) ;
			}
		}
		
		
		if ( !is_null($primary) )
		{
			$this->_primary = $primary ;
		} else {
			$this->_primary = $defPrimary ;
		}

		$this->_len = count($this->_fields);
	}

	function get ()
	{
		return $this->_fields ;
	}
	
	/**
	 * Returns number of fields in the table
	 * 
	 * 
	 * @return int Number of fields in the table
	 */
	function getLength ()
	{
		return $this->_len ; 
	}
	
	/**
	 * Get primary field name for this table, or primary field value if a row of data is given 
	 * 
	 * @param string $data Optional, if given will return the primary field value in this row of data, or null if not found
	 * @return string 
	 */
	function getPrimary ( &$data = null )
	{
		if ( !is_null( $data ) )
		{
			if ( ake ( $this->_primary , $data ) )
			{
				return $data[$this->_primary] ;
			}
			
			return null ;
		}
		
		return $this->_primary ;
	}
	
	/**
	 * Returns initial structure as an array of $fieldname => $field
	 * 
	 * @return array An associative array of fields description
	 */
	function getInitial ()
	{
		return $this->_initialStructure ;
	}
	
	/**
	 * Get schema of a field
	 *
	 *
	 * @param string $name
	 * @return mixed Array of field properties if field found, null otherwise
	 */
	function getField ( $name )
	{
		if ( ake ( $name , $this->_fields ) )
		{
			return $this->_fields[$name] ;
		}
		return null ;
	}
	
	/**
	 * Returns true if a field exists in the table, false otherwise
	 * 
	 * 
	 * @param string $name The field name to test
	 * @return boolean True if field exists, false otherwise
	 */
	function fieldExists ( $name )
	{
		return ake ( $name , $this->_fields ) ;
	}
	
	/**
	 * Get link fields
	 *
	 *
	 * @param unknown_type $structure
	 * @param unknown_type $table
	 */
	function getPickFields ()
	{
		$result = array () ;
		foreach ( $this->_fields as $field )
		{
			if (
			ake ( 'source', $field)
			&& $field['source'] == $this->_name
			&& ake('behavior', $field)
			&& ( $field['behavior'] & DBSchema::BHR_PICK_IN
			|| $field['behavior'] & DBSchema::BHR_PICK_ONE)
			)
			{
				if ( !ake ($this->_name, $result ) )
				{
					$result [$this->_name] = array () ;
				}
				$result [$this->_name][] = $field['name'] ;
			}
		}
		return $result ;
	}


	/**
	 * Filter an array to keep only table fiels
	 */
	function filterFields ( $fields )
	{

		if ( empty($fields) )
		{
			return $fields ;
		}
		$ffields = array () ;
		foreach ( $fields as &$f )
		{
			if (  ake ( $f , $this->_fields ) )
			{
				$ffields[] = $f ;
			}
		}
		return $ffields ;
	}

	/**
	 * Validates schema of a field
	 *
	 * If field is not valid, result is sended to <AeObject.debug> method
	 *
	 * @param string $description
	 * @return boolean True if field schema is valid
	 */
	static function validateField ( $description )
	{
		$res = array () ;

		// Checkin name and type
		if ( !is_array($description)
		|| !ake('name', $description)
		|| !ake('type', $description)
		|| !( is_string ('name') || is_int ('name') )
		|| !is_string ('type') || strlen ('type') < 1 )
		{
		    trigger_error( "DB Errors: \n" . 'Name or type or both are not valids : name must be an int or a string, and type must be a string ', E_USER_ERROR ) ;
		}

		// Checking ENUM type : values, default value
		if ( $description['type'] == DBSchema::TYPE_ENUM )
		{
			if ( !ake('values', $description) || !is_array ( $description['values'] ) || count ( $description['values'] ) == 0 )
			{
				$res[] = 'For enum type, a key "values" must exists in field description, and its value must be a non-empty array.' ;
			}

			if ( ake('default', $description) && !in_array ( $description['default'] , $description['values'] ) )
			{
				$res[] = 'For enum type, the default value must exists in "values" array' ;
			}
			// Checking non ENUM type default value
		} else if ( ake('default', $description) && !self::validateFieldType($description['type'], $description['default']) )
		{
			$res[] = 'The default value must be typed as "type" key' ;
		}

		// Checking behavior regarding type
		if ( ake ( 'behavior', $description ) )
		{
			switch ( true )
			{
				case $description['behavior'] & DBSchema::BHR_INCREMENT:
					if ( $description['type'] != DBSchema::TYPE_INT)
					{
						$res[] = 'The INCREMENT behavior must be applied on INT typed fields' ;
						unset ( $description['behavior'] ) ;
					}
					break;
				case $description['behavior'] & DBSchema::BHR_DT_ON_EDIT:
					if ( $description['type'] != DBSchema::TYPE_DATETIME)
					{
						$res[] = 'The DATETIME_ON_EDIT behavior must be applied on DATETIME typed fields' ;
						unset ( $description['behavior'] ) ;
					}
					break;
				case $description['behavior'] & DBSchema::BHR_TS_ON_EDIT:
					if ( $description['type'] != DBSchema::TYPE_TIMESTAMP )
					{
						$res[] = 'The TIMESTAMP_ON_EDIT behavior must be applied on TIMESTAMP typed fields' ;
						unset ( $description['behavior'] ) ;
					}
					break;
				case $description['behavior'] & DBSchema::BHR_SHA1:
				case $description['behavior'] & DBSchema::BHR_MD5:
					if ( $description['type'] != DBSchema::TYPE_STRING)
					{
						$res[] = 'The TIMESTAMP_ON_EDIT behavior must be applied on TIMESTAMP typed fields' ;
						unset ( $description['behavior'] ) ;
					}
					break;
			}
		}

		if ( count ( $res ) > 0 )
		{
			$this->debug( "DB Errors: \n" . implode(",\n" , $res) ) ;
		}

		return true ;
	}

	/**
	 * Validate a value given its type
	 *
	 *
	 * @param string $type Type of field, should be on o the types of <DBSchema>
	 * @param mixed $value The value to test
	 * @param array $enumValues Optional, if type is <DBSchema.TYPE_ENUM>, the array of valid values
	 * @return boolean Result of test : true if value is valid, false otherwise
	 */
	public static function validateFieldType ( $type , $value , $enumValues = array () )
	{
		switch ( $type )
		{
			case DBSchema::TYPE_FLOAT:
				return ( is_float( $value ) || is_int($value)) ;
			case DBSchema::TYPE_INT:
				return ( is_int( intval( $value ) ) ) ;
			case DBSchema::TYPE_STRING:
			case DBSchema::TYPE_TEXT:
				return ( is_string( $value ) ) ;
			case DBSchema::TYPE_BOOL:
				return ( is_bool( $value ) ) ;
			case DBSchema::TYPE_ENUM:
				return ( in_array ( $value , $enumValues ) ) ;
			case DBSchema::TYPE_TIMESTAMP:
			case DBSchema::TYPE_DATETIME:
				return true;
			default:
				return true ;
		}
	}

	/**
	 * The method will apply the behaviors for OUTPUT PURPOSE in $field['behaviors'] on $row content
	 *
	 * Types that modify content
	 *
	 * Behaviors that modify content:
	 *  - None far the less
	 *
	 * @param object $field
	 * @param object $row
	 * @return array The row
	 */
	public static function applyOutputBehaviors ( &$fieldDesc , &$value )
	{
		if ( $fieldDesc['type'] == DBSchema::TYPE_TIMESTAMP )
		{
			$value = self::datetimeToTimestamp ( $value ) ;
		}
		if ( ake ('behavior' , $fieldDesc ) && $fieldDesc['behavior'] & DBSchema::BHR_SERIALIZED )
		{
			$value = unserialize($value);
		}
		return $value ;
	}

	/**
	 * The method will apply the behaviors in $field['behaviors'] on $row content
	 *
	 * @param object $field Field structure
	 * @param object $value Value
	 * @return array The row of data
	 */
	public static function applyInputBehaviors ( &$fieldDesc , &$value , $edit = true , &$mysql_connection = null )
	{
		if ( !ake('name', $fieldDesc ) || !ake('type', $fieldDesc ) )
		{
			return $value;
		}

		$n = $fieldDesc['name'] ;
		$t = $fieldDesc['type'] ;

		// SPECIAL TYPES
		if ( $t == DBSchema::TYPE_BOOL )
		{
			$value = ($value != 'true' ? 0 : 1 ) ;
		}

		if ( $t == DBSchema::TYPE_INT )
		{
			$value = intval($value);
		}

		if ( $t == DBSchema::TYPE_TIMESTAMP && is_int($t) )
		{
			$value = intval($value);
		}

		// BEHAVIORS
		if ( ake('behavior', $fieldDesc ) )
		{
			$b = $fieldDesc['behavior'] ;
			switch ( true )
			{
				case is_array ( $b ):
					//array ( CallBackObjectInstance , 'methodName' , array ( 'argument 2' , 'some other argument' ) )
					if ( count ( $b ) >= 2 && is_object ( $b[0] ) && method_exists($b[0], $b[1]) )
					{
						if ( count ( $b ) == 2 || !is_array ( $b[2] ) )
						{
							$b[2] = array () ;
						}
						$value = call_user_method_array($b[1], $b[0], array_merge ( array ( $value ) , $b[2] ) ) ;
					} elseif ( count ( $b ) >= 1 && is_string ( $b[0] ) && function_exists( $b[0] ) )
					{
						if ( count ( $b ) == 1 || !is_array ( $b[1] ) )
						{
							$b[1] = array () ;
						}
						$value = call_user_func_array( $b[0], array_merge ( array ($value ) , $b[2] ) ) ;
					}
					break;
				case $b & DBSchema::BHR_TS_ON_EDIT
				&& $t == DBSchema::TYPE_TIMESTAMP:
				$value = self::getTimestamp() ;
				break;
				case $b & DBSchema::BHR_DT_ON_EDIT
				&& $t == DBSchema::TYPE_DATETIME
				&& $n != 'created'
				&& $n != 'updated'
				&& $n != 'modified':
				$value = self::getDatetime() ;
				break;
				case $b & DBSchema::BHR_SHA1
				&& $t == DBSchema::TYPE_STRING
				&& is_string( $value ):
				$value = sha1 ( $value ) ;
				break;
				case $b & DBSchema::BHR_MD5
				&& $t == DBSchema::TYPE_STRING
				&& is_string( $value ):
				$value = md5 ( $value ) ;
				break;
				case $b & DBSchema::BHR_URLIZE
				&& $t == DBSchema::TYPE_STRING
				&& function_exists('urlize'):
				$value = urlize ( $value ) ;
				break;
				case $b & DBSchema::BHR_PICK_IN:
					if(!is_array($value)) $value = explode(',',$value);
					foreach ( $value as &$v ) $v = trim($v);
					$value = implode(',',array_unique($value));
					break;
				case $b & DBSchema::BHR_PICK_ONE:
					if(is_array($value)) $value = serialize($value);
					else $value = trim($value);
					break;
				case $b & DBSchema::BHR_SERIALIZED:
					$value = serialize($value);
					break;
			}
		}


		// MAGIC FIELDS updated, modified, created
		if (
		( ($n == 'updated' || $n == 'modified') && $edit === true )
		|| ( $n == 'created' && $edit === false ) )
		{
			if ( $t == DBSchema::TYPE_DATETIME )
			{
				$value = self::getTimestamp() ;
			} elseif ( $t == DBSchema::TYPE_TIMESTAMP )
			{
				$value = self::getTimestamp() ;
			}
		}
		// TODO: mysql_real_escape_string must be done in MySQL engine, not here
		if ( !is_null($mysql_connection) && ( $t == DBSchema::TYPE_STRING || $t == DBSchema::TYPE_TEXT ) )
		{
			$value = mysql_real_escape_string ( $value , $mysql_connection ) ;
		}

		return $value ;
	}


	static function getTimestamp ( $timestamp = null )
	{
		if ( is_null( $timestamp ) )
		{
			$timestamp = time () ;
		}
		return date("Y-m-d H:i:s", $timestamp) ;
	}



	public static function datetimeToTimestamp ( $date = null )
	{
		return strtotime( $date ) ;
	}

}

?>
