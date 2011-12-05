<?php

class Callback {
	
	var $method ;
	
	var $object ;
	
	var $staticClassStr ;
	
	public function __construct ( $method , &$object = null , $staticClassStr = null )
	{
		$this->method = $method ;
		
		$this->object = $object ;
		
		$this->staticClassStr = $staticClassStr ;
	}
	
	public function apply ( $arguments )
	{
		if ( is_callable( $this->method ) )
		{
			return @call_user_func_array( $this->method, $arguments ) ;
		}
		
		if ( !is_null ( $this->staticClassStr ) )
		{
			return @call_user_func_array( $this->staticClassStr .'::'. $this->method , $args ) ;
		}
		return @call_user_func_array( array( $this->object , $this->method ) , $arguments ) ;
	}
	
}

?>