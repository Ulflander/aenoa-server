<?php

/**
 * Config is a very simple class to store configuration values.
 *
 * It uses an <Collection> instance to store data.
 *
 * An internal directive ensures that Config will be properly inited before any call.
 *
 *
 * @see Collection
 * @see App
 */
class Config extends Object {

	/**
	 * @private
	 * @var Collection
	 */
	private static $_vars ;
	
	/**
	 * Set a config key/value
	 * 
	 * @param string $k Key
	 * @param string $v Value
	 */
	static function set ( $k , $v ) 
	{
		self::$_vars->set ( $k, $v ) ;
	}

	/**
	 * Get a config value given its key
	 * 
	 * @param string $k
	 * @return mixed Config value if exists, NULL otherwise
	 */
	static function get ( $k )
	{
		return self::$_vars->get($k) ;
	}

	/**
	 * Checks if a value exists
	 *
	 * @param string $k Key
	 * @return boolean True if value is defined, false otherwise
	 */
	static function has ( $k )
	{
		return self::$_vars->has($k) ;
	}

	/**
	 * Set a bunch of config values from an array
	 *
	 * @param type $array
	 */
	static function setAll ( $array )
	{
		self::$_vars->setAll( $array ) ;
	}

	/**
	 * Get all config keys/values as a key sorted array
	 *
	 * @return array Whole config
	 */
	static function getAll ()
	{
		return self::$_vars->getAll() ;
	}

	/**
	 * Initialization of Config class
	 * @private
	 */
	static function init ()
	{
		if ( is_null( self::$_vars ) )
		{
			self::$_vars = new Collection () ;
		}
	}
	
}

Config::init () ;

?>