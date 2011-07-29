<?php


class Config {
	
	private static $_vars = array () ;
	
	static function set ( $k , $v ) 
	{
		self::$_vars[$k] = $v ;
	}
	
	static function get ( $k )
	{
		if ( array_key_exists($k, self::$_vars ) )
		{
			return self::$_vars[$k] ;
		}
		
		return null ;
	}
	
	static function has ( $k )
	{
		return array_key_exists($k, self::$_vars ) ;
	}
	
	static function getAll ()
	{
		$tvars = self::$_vars ;
		return $tvars ;
	}
}
?>