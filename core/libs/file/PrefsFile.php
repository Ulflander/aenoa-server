<?php


class PrefsFile extends File {
	
	public $data = array () ;
	
	protected $path = '' ;
	
	final public function __construct ( $filepath , $create = false , $chmod = 0777 )
	{
		parent::__construct ( $filepath , $create , $chmod ) ;
		
		if ( $this->exists () )
		{
			$this->read () ;
		}
	}
	
	public function uset ( $key )
	{
		if ( $this->has ( $key ) ) 
		{
			unset ( $this->data[$key] ) ;
		}
	}
	
	public function set ( $key , $val , $flush = true )
	{
		if ( $val == null && array_key_exists($key, $this->data) )
		{
			unset ( $this->data[$key]);
			return;
		}
		
		$this->data[$key] = $val ;
		
		if ( $flush === true )
		{
			return $this->flush () ;
		}
		
		return true ;
	}
	
	public function get ( $key , $default = null )
	{
		if ( $this->has ( $key ) ) 
		{
			return $this->data[$key] ;
		}
		return $default ;
	}
	
	public function has ( $key )
	{
		return array_key_exists( $key, $this->data ) ;
	}
	
	public function isEmpty ( )
	{
		return !$this->exists () || empty ( $this->data ) ;
	}
	
	/**
	 * Overwrite this method in concrete classes
	 * 
	 * @return 
	 */
	public function flush ()
	{
	}
	
	/**
	 * Overwrite this method in concrete classes
	 * 
	 * @return 
	 */
	final public function close () 
	{
		parent::close () ;
	}
	
	final function __destruct ()
	{
		$this->close () ;
	}
}
?>