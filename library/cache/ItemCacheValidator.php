<?php

// For Aenoa Server 1.1, codename Alastor
// namespace aenoa\cache

/*
	Class: ItemCacheValidator
	
	Validates cache data. Data to be stored in caches must NOT be objects.
  
	In debuggin mode, an ErrorZException will be threw in case data is not valid. In production mode, validate method just return result of validation.
	
	See Also:
	<CacheBase>
	
 */
class ItemCacheValidator extends Object {

	private $_recursive = true ;
	
	/**
	 * Set recursivity of data validation
	 * 
	 * @param boolean $recursive [Optional] Recursivity of validation, default is true
	 * @return ItemCacheValidator Current instance for chained command on this element
	 * @throws ErrorException An error is threw if $recursive argument is not typed as boolean
	 */
	function setRecursive ( $recursive = true )
	{
		if (is_bool($recursive))
		{
			$this->_recursive = $recursive ;
		} else {
			throw new ErrorException ('recursive parameter must be a boolean') ;
		}
		
		return $this ;
	}
	
	/**
	 * Get recursivity of data validation
	 * 
	 * @return boolean True if validation is recursive, false otherwise
	 */
	function getRecursive ()
	{
		return $this->_recursive ;
	}
	
	/**
	 * Validate data
	 * 
	 * @param mixed $value The value to validate
	 * @return boolean True if $value is valid, false otherwise
	 * @throws ErrorException Threw if debuggin mode and data not valid
	 */
	function validate ( $value = null )
	{
		$valid = true ;
		
		if (is_object($value) )
		{
			$valid = false ;
			
			if ( debuggin () )
			{
				throw new ErrorException ('Objects are not authorized to be stored in caches ' . $value ) ;
			}
			
		} else if ( is_array ( $value ) && $this->getRecursive() === true ) 
		{
			foreach ( $value as &$val )
			{
				if ( $this->validate ( $val ) === false )
				{
					$valid = false ;
				}
			}
		}
		
		return $valid ;
	}
	

}

?>
