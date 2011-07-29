<?php


abstract class Service {
	
	public $protocol ;
	
	public final function applyQuery ( ServiceQuery $query , array $arguments = null )
	{
		if ( !is_null ( $arguments ) ) 
		{
			$this->beforeService() ;
			
			call_user_func_array(array ( $this , $query->serviceMethod ) , $arguments ) ;
			
			$this->afterService () ;
			
			return true ;
		}
		
		return false ;
	}
	
	public function beforeService ()
	{
		
	}

	public function afterService ()
	{
		
	}
}
?>