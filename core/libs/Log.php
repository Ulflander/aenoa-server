<?php


class Log {
	
	private static $futil ;
	
	private static $f ;
	
    function __construct() {
    	global $FILE_UTIL;
		self::$futil = &$FILE_UTIL ;
		self::$f = new File ( ROOT . '.private/.aenoalog' , true );
    }

	public static function w ( $message ) 
	{
		if ( self::$f )
		{
			self::$f->append ( $message . "\n"  . "\n" ) ;
		}
	}
	
	public static function wlog ( $message ) 
	{
		if ( self::$f )
		{
			self::$f->append ( date ( 'Y-m-d H:i:s :: ' ,time () ) . $message . "\n" ) ;
		}
	}
	
	public static function clog ()
	{
		if ( self::$f )
		{
			self::$f->close () ;
		}
	}
}
?>