<?php

/*
 * Class: ModelCollection
 *
 * Internal class.
 *
 * Simple class used by controllers to easily access to database's models.
 * 
 *
 *
 * See also:
 * <Model>, <Controller>
 */
class ModelCollection extends Collection {

	private $__throw = true ;

	/**
	 * 
	 *
	 * @param type $val
	 */
	function setThrowException ( $val )
	{
		if (is_bool($val) )
		{
			$this->__throw = $val ;
		}
	}

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
	 * @see ModelCollection::get
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