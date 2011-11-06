<?php

/**
 * Config is a very simple class to stroe configuration values
 * 
 * @see App
 */
class Config {
	
	private static $_vars = array () ;
	
	/**
	 * 
	 * 
	 * @param unknown_type $k
	 * @param unknown_type $v
	 */
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
		ksort($tvars);
		return $tvars ;
	}
}
?>