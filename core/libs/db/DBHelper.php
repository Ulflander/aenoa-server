<?php


class DBHelper {
	
	
	public static function dbToFormFields ( $dbid , $table , $data )
	{
		$d = array () ;
		foreach ( $data as $k => $v )
		{
			$d[$dbid.'/'.$table.'/'.$k] = $v ;
		}
		return $d ;
	}
	
	public static function validate ( $type , $value , $enumValues = array () )
	{
		switch ( $type )
		{
			case AbstractDB::TYPE_FLOAT:
				return ( is_float( $value ) || is_int($value)) ;
			case AbstractDB::TYPE_INT:
				return ( is_int( intval( $value ) ) ) ;
			case AbstractDB::TYPE_STRING:
			case AbstractDB::TYPE_TEXT:
				return ( is_string( $value ) ) ;
			case AbstractDB::TYPE_BOOL:
				return ( is_bool( $value ) ) ;
			case AbstractDB::TYPE_ENUM:
				return ( in_array ( $value , $enumValues ) ) ;
			case AbstractDB::TYPE_TIMESTAMP:
			case AbstractDB::TYPE_DATETIME:
				return true;
			default:
				return true ;
		}
	}
	
	
	/**
	 * Get tables and fields that reference given table
	 * @param array $structure
	 * @param string $table
	 * @return array
	 */
	public static function extractReferences ( $structure , $table )
	{
		$result = array () ;
		foreach ( $structure as $tname => $t )
		{
			foreach ( $t as $field )
			{
				if ( 
					ake ( 'source', $field) 
					&& $field['source'] == $table
					&& ake('behavior', $field) 
					&& ( $field['behavior'] & AbstractDB::BHR_PICK_IN 
						|| $field['behavior'] & AbstractDB::BHR_PICK_ONE)
					)
				{
					if ( !ake ($tname, $result ) )
					{
						$result [$tname] = array () ;
					}
					$result [$tname][] = $field['name'] ; 
				}
			}
		}
		return $result ;
	}
	
	/**
	 * Validate the description of a field 
	 *
	 * @param object $fieldDesc
	 * @return An array of strings errors if there is any error, or true if validation is successful
	 */
	public static function validateField ( &$fieldDesc )
	{
		$res = array () ;
		// Checkin name and type
		if ( !is_array($fieldDesc) 
			|| !ake('name', $fieldDesc) 
			|| !ake('type', $fieldDesc) 
			|| !( is_string ('name') || is_int ('name') )
			|| !is_string ('type') || strlen ('type') < 1 ) 
		{
			$res[] = 'Name or type or both are not valids : name must be an int or a string, and type must be a string ' ;
			return $res ;
		}
		
		// Checking ENUM type : values, default value
		if ( $fieldDesc['type'] == AbstractDB::TYPE_ENUM )
		{
			if ( !ake('values', $fieldDesc) || !is_array ( $fieldDesc['values'] ) || count ( $fieldDesc['values'] ) == 0 )
			{
				$res[] = 'For enum type, a key "values" must exists in field description, and its value must be a non-empty array.' ;
			}
			
			if ( ake('default', $fieldDesc) && !in_array ( $fieldDesc['default'] , $fieldDesc['values'] ) )
			{
				$res[] = 'For enum type, the default value must exists in "values" array' ;
			} 
		// Checking non ENUM type default value
		} else if ( ake('default', $fieldDesc) && !self::validate($fieldDesc['type'], $fieldDesc['default']) )
		{
			$res[] = 'The default value must be typed as "type" key' ;
		}
		
		// Checking behavior regarding type
		if ( ake ( 'behavior', $fieldDesc ) )
		{
			switch ( true )
			{
				case $fieldDesc['behavior'] & AbstractDB::BHR_INCREMENT:
					if ( $fieldDesc['type'] != AbstractDB::TYPE_INT)
					{
						$res[] = 'The INCREMENT behavior must be applied on INT typed fields' ;
						unset ( $fieldDesc['behavior'] ) ;
					}
					break;
				case $fieldDesc['behavior'] & AbstractDB::BHR_DT_ON_EDIT:
					if ( $fieldDesc['type'] != AbstractDB::TYPE_DATETIME)
					{
						$res[] = 'The DATETIME_ON_EDIT behavior must be applied on DATETIME typed fields' ;
						unset ( $fieldDesc['behavior'] ) ;
					}
					break;
				case $fieldDesc['behavior'] & AbstractDB::BHR_TS_ON_EDIT:
					if ( $fieldDesc['type'] != AbstractDB::TYPE_TIMESTAMP )
					{
						$res[] = 'The TIMESTAMP_ON_EDIT behavior must be applied on TIMESTAMP typed fields' ;
						unset ( $fieldDesc['behavior'] ) ;
					}
					break;
				case $fieldDesc['behavior'] & AbstractDB::BHR_SHA1:
				case $fieldDesc['behavior'] & AbstractDB::BHR_MD5:
					if ( $fieldDesc['type'] != AbstractDB::TYPE_STRING)
					{
						$res[] = 'The TIMESTAMP_ON_EDIT behavior must be applied on TIMESTAMP typed fields' ;
						unset ( $fieldDesc['behavior'] ) ;
					}
					break;
			}
		}
		
		if ( count ( $res ) > 0 )
		{
			App::do500 ( 'DB Error: ' . implode(',' , $res) ) ;
		}
		
		return true ;
	}
	
	public static function getTimestamp ( $timestamp = null )
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
	
	public static function getDatetime ()
	{
		return date("Y-m-d H:i:s") ;
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
		if ( $fieldDesc['type'] == AbstractDB::TYPE_TIMESTAMP )
		{
			$value = self::datetimeToTimestamp ( $value ) ;
		}
		return $value ;
	}
	
	/**
	 * The method will apply the behaviors in $field['behaviors'] on $row content
	 * 
	 * @param object $field
	 * @param object $row
	 * @return array The row
	 */
	public static function applyBehaviors ( &$fieldDesc , &$value , $edit = true , &$connection = null )
	{
		if ( !ake('name', $fieldDesc ) || !ake('type', $fieldDesc ) ) 
		{
			return $value;
		}
		
		$n = $fieldDesc['name'] ;
		$t = $fieldDesc['type'] ;
		
		// SPECIAL TYPES
		if ( $t == AbstractDB::TYPE_BOOL )
		{
			$value = ($value != 'true' ? 0 : 1 ) ;
		}
		
		if ( $t == AbstractDB::TYPE_INT )
		{
			$value = intval($value);
		}
		
		if ( $t == AbstractDB::TYPE_TIMESTAMP && is_int($t) )
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
				case $b & AbstractDB::BHR_TS_ON_EDIT 
					&& $t == AbstractDB::TYPE_TIMESTAMP:
						$value = self::getTimestamp() ;
					break;
				case $b & AbstractDB::BHR_DT_ON_EDIT 
					&& $t == AbstractDB::TYPE_DATETIME
					&& $n != 'created'
					&& $n != 'updated'
					&& $n != 'modified':
						$value = self::getDatetime() ;
					break;
				case $b & AbstractDB::BHR_SHA1 
					&& $t == AbstractDB::TYPE_STRING
					&& is_string( $value ):
						$value = sha1 ( $value ) ;
					break;
				case $b & AbstractDB::BHR_MD5 
					&& $t == AbstractDB::TYPE_STRING
					&& is_string( $value ):
						$value = md5 ( $value ) ;
					break;
				case $b & AbstractDB::BHR_URLIZE
					&& $t == AbstractDB::TYPE_STRING
					&& function_exists('urlize'):
						$value = urlize ( $value ) ;
					break;
				case $b & AbstractDB::BHR_PICK_IN:
					if(!is_array($value)) $value = explode(',',$value);
					foreach ( $value as &$v ) $v = trim($v);
					$value = implode(',',array_unique($value));
					break;
				case $b & AbstractDB::BHR_PICK_ONE:
					if(is_array($value)) $value = serialize($value);
					else $value = trim($value);
			}
		}
		
		
		// MAGIC FIELDS updated, modified, created
		if ( 
			( ($n == 'updated' || $n == 'modified') && $edit === true ) 
			|| ( $n == 'created' && $edit === false ) ) 
		{
			if ( $t == AbstractDB::TYPE_DATETIME )
			{
				$value = self::getDatetime() ;
			} elseif ( $t == AbstractDB::TYPE_TIMESTAMP )
			{
				$value = self::getTimestamp() ;
			}
		}
		
		if ( !is_null($connection) && ( $t == AbstractDB::TYPE_STRING || $t == AbstractDB::TYPE_TEXT ) )
		{
			$value = mysql_real_escape_string ( $value , $connection) ;
		}

		return $value ;
	}
}
?>