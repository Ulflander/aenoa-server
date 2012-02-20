<?php

/*
 * Class: GetableCollection
 *
 * Internal class.
 *
 * Simple class used that extends collection and offers a dynamic method to get collection data.
 * 
 * How to use:
 * (start code)
 * // Creates a new instance of GetableCollection
 * $coll = new GetableCollection () ;
 * 
 * // Set some values
 * $coll->set ( 'identifier' , 'value' ) ;
 * $coll->set ( 'another' , 'some value' ) ;
 * 
 * // And get some values using GetableCollection dynamic getter
 * echo $coll->identifier ; // value
 * echo $coll->another ; // some value
 * 
 * // Will throw an exception
 * echo $coll->unexistingValue ;
 * 
 * // Avoid throwing exception
 * $coll->setThrowException ( false ) ;
 * 
 * // Will not throw exception, return null
 * echo $coll->unexistingValue ; // NULL
 * (end)
 *
 *
 * See also:
 * <Collection>
 */
class GetableCollection extends Collection {

	private $__throw = true ;

	/**
	 * Set throw exception mode (true to throw exceptions, false otherwise)
	 *
	 * @param boolean $bool True to throw exceptions, false otherwise
	 * @return GetableCollection Current instance for chained command on this element
	 */
	function setThrowException ( $bool )
	{
		if (is_bool($bool) )
		{
			$this->__throw = $bool ;
		}
		
		return $this ;
	}
	
	/**
	 * Get throw exception mode
	 * 
	 * @return boolean True if throwException mode is enabled, false otherwise
	 */
	function getThrowException ()
	{
		return $this->__throw ;
	}

	/**
	 * Return required model by its camelized name, null otherwise.
	 * 
	 * Checkout: http://php.net/manual/en/language.oop5.overloading.php.
	 *
	 * Throws an exception if throwException mode is activated.
	 *
	 * @see Collection::get
	 * @param string $name Name of model to get
	 * @return Model Model if exists, null otherwise
	 */
	function __get ( $name )
	{
		if ( !$this->has($name) && $this->__throw )
		{
			throw new ErrorException( '['.get_class($this) .'][Collection] does not have item corresponding to <strong>' . $name . '</strong>' );
		}
		
		return $this->get( $name );
	}

}

?>