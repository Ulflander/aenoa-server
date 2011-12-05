<?php


/**
 * Callback is a little utility to quickly create callbacks to static or instance methods, defined or anonymous functions
 * 
 * 
 * It basically uses the php call_user_func_array.
 * 
 * 
 * (start code)
 *	Code test 
 * (end)
 * 
 * 
 * 
 */
class Callback {
	
	var $method ;
	
	var $object ;
	
	/**
	 * Creates a new callback
	 * 
	 * @param mixed $method An anonymous function, a function name, or a method name
	 * @param object $object OPtional, the object instance if callback calls an instance method, or a string if callback calls a static method
	 */
	public function __construct ( $method , &$object = null )
	{
		$this->method = $method ;
		
		$this->object = $object ;
	}
	
	/**
	 * Apply the callback on given arguments
	 * 
	 * Will trigger a notice error if callback is not usable
	 * 
	 * @param array $arguments An indexed array of arguments
	 * @return mixed Returned value by callback
	 */
	public function apply ( $arguments )
	{
		if ( is_callable( $this->method ) )
		{
			return call_user_func_array( $this->method, $arguments ) ;
		}
		
		if ( is_null ( $this->object ) )
		{
			if ( function_exists($this->method ) )
			{
				return call_user_func_array( $this->method, $arguments ) ;
			}
			
			trigger_error ( 'Callback NULL::'. $this->method . ' is not callable : object ' ) ;
			
			return null ;
		}
		
		if ( is_string ( $this->object ) )
		{
			return call_user_func_array( $this->object .'::'. $this->method , $arguments ) ;
		}
		
		return call_user_func_array( array( $this->object , $this->method ) , $arguments ) ;
	}
	
	/**
	 * Check whether the callback is usable
	 * 
	 * @return boolean True if callback is valid and usable, false otherwise 
	 */
	public function isUsable ()
	{
		if ( is_callable( $this->method ) )
		{
			return true ;
		}
		
		if ( is_null ( $this->object ) )
		{
			if ( function_exists($this->method ) )
			{
				return true ;
			}
			
			return false ;
		}
		
		
		$reflexion = new ReflectionMethod($this->object, $this->method); 
			
		
		if ( is_string ( $this->object ) )
		{
			return $reflexion->isStatic () ;
		}
		
		return $reflexion->isPublic(); 
	}
	
}

?>