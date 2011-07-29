<?php

class Callback {
	
	var $method ;
	
	var $object ;
	
	var $staticClassStr ;
	
	public function __construct ( $method , &$object = null , $staticClassStr = null )
	{
		$this->object = $object ;
		
		$this->method = $method ;
		
		$this->staticClassStr = $staticClassStr ;
	}
	
	public function apply ( $arguments )
	{
		if ( !is_null ( $this->staticClassStr ) )
		{
			return @call_user_func_array( $this->staticClassStr .'::'. $this->method , $args ) ;
		}
		return @call_user_func_array( array( $this->object , $this->method ) , $arguments ) ;
	}
	
}

?>