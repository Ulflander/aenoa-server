<?php

/**
 * 
 */
final class Database {
	
	private $engine ;
	
	private $engineName ;
	
	private $called = 0 ;
	
	
	
	/**
	 * Set the concrete database engine (JSONDatabase, MySQLDatabase, CVSDatabase...)
	 * 
	 * If the db engine is valid, it will open database
	 * 
	 * @param object $engine
	 * @return 
	 */
	public function setEngine ( $engine )
	{
		if ( is_subclass_of( $engine, 'AbstractDB' ) == false )
		{
			trigger_error( '$engine must extends AbstractDB' ) ;
		} else {
			
			if ( !is_null ( $this->engine ) ) 
			{
				$this->engine->close () ;
			}
			
			$this->engine = $engine ;
			
			$this->engineName = get_class ( $engine ) ;
		}
	}
	
	public function getEngine ()
	{
		return $this->engine ;
	}
	
	public function getEngineName () 
	{
		return $this->engineName ;
	}
	
	
	public function getCallCount ()
	{
		return $this->called ;
	}
	
	public function __call($name, $arguments) {
		
        if ( method_exists( $this->engine , $name ) )
		{
			$this->called ++ ;
			
			return call_user_func_array( array( $this->engine , $name ) , $arguments ) ;
		}
		
		return false ;
    }
	
	public function __destruct ()
	{
		if ( !is_null ( $this->engine ) )
		{
			$this->engine->close () ;
		}
	}
	
	
}
?>