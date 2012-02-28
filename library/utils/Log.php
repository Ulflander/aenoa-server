<?php

/*
	Class: Log
  
	A very simple class to create and use a log file
	
	
 */
class Log {
	
	
	private static $f ;
	
	private $log ;
	
	/**
	 * Creates a new Log instance
	 * 
	 * @param string $logfile 
	 */
    function __construct( $logfile = null ) {
		
		if ( is_null($logfile) )
		{
			$logfile = ROOT . '.private/.aenoalog' ;
		}
		
		if ( is_null(self::$f) )
		{
			self::$f = new File ( ROOT . '.private/.aenoalog' , true );
		} else {
			$this->log = new File ( $logfile ) ;
		}
    }
	
	public function set ( $msg = '' )
	{
		if (is_null($msg) )
		{
			throw new ErrorException ('No message to log');
		}
		
		if ( is_null($this->log) )
		{
			throw new ErrorException ('Log file not inited');
		}
		
		$this->log->append(date ( 'Y-m-d H:i:s :: ' ,time () ) . $msg . "\n");
	}
	
	public function get ()
	{
		if ( is_null($this->log) )
		{
			throw new ErrorException ('Log file not inited');
		}
		
		return $this->log->read() ;
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