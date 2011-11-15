<?php

/**
 * Config is a very simple class to store configuration values
 * 
 * @see App
 */
class Config extends AeObject {

	/**
	 *
	 * @var Options
	 */
	private static $_vars ;
	
	/**
	 * 
	 * 
	 * @param unknown_type $k
	 * @param unknown_type $v
	 */
	static function set ( $k , $v ) 
	{
		self::$_vars->set ( $k, $v ) ;
	}

	static function get ( $k )
	{
		return self::$_vars->get($k) ;
	}
	
	static function has ( $k )
	{
		return self::$_vars->has($k) ;
	}
	
	static function getAll ()
	{
		return self::$_vars->getAll() ;
	}

	static function init ()
	{
		if ( is_null( self::$_vars ) )
		{
			self::$_vars = new Options () ;
		}
	}
	
}

Config::init () ;

?>