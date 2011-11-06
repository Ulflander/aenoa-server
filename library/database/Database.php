<?php

/**
 * <p>Database is an interface to access to DB engines that extends <AbstractDBEngine></p>
 * 
 * <p>Engines are implemented in Database class by aggregation.</p>
 * <p>All methods available in concrete engine will be available as well in Database instance.</p>
 * 
 * <p>It only manage one engine per instance.</p>
 * 
 * <h3>Warning</h3>
 * <p>You may not create it directly, this class is part of Aenoa Server automatisms. To get a database, declare it using App::declareDatabase and get it using App::getDatabase</p>
 * 
 * <p>Check out docs below to know more about databases.</p>
 * 
 * @see App::declareDatabase
 * @see App::getDatabase
 * @see AbstractDBEngine
 * @see MySQLEngine
 * @see DBSchema
 * @see DBTableSchema
 */
final class Database {

	private $engine ;

	private $engineName = '' ;



	/**
	 * Set the concrete database engine (MySQLEngine)
	 *
	 * If the db engine is valid, it will open database.
	 *
	 * @param object $engine
	 * @return AbstractDBEngine The new engine if engine sucessfully created, false otherwise
	 */
	public function setEngine ( $engine )
	{
		if ( is_subclass_of( $engine, 'AbstractDBEngine' ) == false )
		{
			trigger_error( '$engine must extends AbstractDBEngine' ) ;
		} else {
				
			if ( !is_null ( $this->engine ) )
			{
				$this->engine->close () ;
			}
				
			$this->engine = $engine ;
				
			$this->engineName = get_class ( $engine ) ;
				
			return $this->engine ;
		}
	}

	/**
	 * Returns the set DB engine, can be null if no engine set.
	 *
	 * @return AbstractDBEngine The DB engine
	 */
	public function getEngine ()
	{
		return $this->engine ;
	}

	/**
	 * Returns the class name of the current engine, can be an empty string if no engine set
	 *
	 *
	 */
	public function getEngineName ()
	{
		return $this->engineName ;
	}

	/**
	 * Destruct the engine
	 */
	public function __destruct ()
	{
		if ( !is_null ( $this->engine ) )
		{
			$this->engine->close () ;
			$this->engine = null ;
			$this->engineName = '' ;
		}
	}

	/**
	 * Call a concrete engine method
	 * 
	 * @param string $name Name of the method to call
	 * @param array $arguments Arguments to apply to the method
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array( array( $this->engine , $name ) , $arguments ) ;
	}

}
?>