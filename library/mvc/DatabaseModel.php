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
class DatabaseModel extends Collection {

	/**
	 * Return required model by its camelized name, null otherwise.
	 * 
	 * Checkout: http://php.net/manual/en/language.oop5.overloading.php
	 *
	 *
	 * @see DatabaseModel::get
	 * @param string $name Name of model to get
	 * @return Model Model if exists, null otherwise
	 */
	function __get ( $name )
	{
		return $this->get( $name );
	}

}

?>
