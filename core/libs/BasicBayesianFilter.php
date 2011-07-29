<?php



class BasicBayesianFilter {
	
	
	private $data ;
	
	function __construct ( $dataFile )
	{
		$this->data = new PHPPrefsFile ( $dataFile, true ) ;
	}
	
	function __destruct ()
	{
		if ( $this->usable () )
		{
			$this->data->flush () ;
			$this->data->close () ;
			$this->data = null ;
		}
	}
	
	function usable ()
	{
		return $this->data->exists () ;
	}
	
	function add ( $word , $value )
	{
	
		if ( $this->usable () == false )
		{
			return ;
		}
		if ( $value < 1 )
		{
			$value = 1 ;
		}
		
		$this->data->set($word, $value) ;
	}
	
	function rem ( $word )
	{
		if ( $this->usable () && $this->data->has( $word ) )
		{
			$this->data->uset( $word ) ;
			return ;
		}
	}
	
	function train ( $content )
	{
		if ( $this->usable () == false )
		{
			return ;
		}
		
		$words = explode ( ' ' , $content ) ;
		foreach ( $words as $word )
		{
			//if ( $this->data->has())
		}
	}
	
	// 0 to 100  
	function guess ( $content )
	{
		
	}
}



?>