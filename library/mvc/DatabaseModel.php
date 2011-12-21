<?php

/*
 * Class: DatabaseModel
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
class DatabaseModel extends Object {


	private $_models = array () ;

	/**
	 * Create a new DatabaseModel
	 *
	 * @param type $tables Associative array with camelized models names as keys and models as values
	 */
	function __construct ( $models )
	{
		$this->_models = $models ;
	}

	/**
	 * Return required model by its camelized name, null otherwise.
	 *
	 *
	 * @link http://php.net/manual/en/language.oop5.overloading.php
	 * @param string $name Name of model to get
	 * @return Model Model if exists, null otherwise
	 */
	function __get ( $name )
	{
		return array_key_exists($name, $this->_models) ? $this->_models[$name] : null ;
	}

	/**
	 * Check if a model exists
	 *
	 * @param type $name
	 * @return type 
	 */
	function has ( $name )
	{
		return array_key_exists($name, $this->_models) ;
	}
	
	/**
	 * Returns a list of all models names
	 */
	
	function inventory ()
	{
		return array_keys($this->_models) ;
	}
}

?>
