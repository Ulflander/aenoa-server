<?php


/**
 * QueryString is a little class that read a query to aenoa server and offers methods to access it quickly and easily
 *
 * @see Dispatcher
 */
class QueryString extends AeObject {


	/**
	 * First token in URLs to access to core DatabaseController
	 *
	 * Value:
	 * 'database'
	 */
	const DB_TOKEN = 'database' ;

	/**
	 * First token in URLs to access to Server/Services system
	 *
	 * Value:
	 * 'api'
	 */
	const SERVICES_TOKEN = 'api' ;

	/**
	 * First token in URLs to access to REST service
	 *
	 * Value:
	 * 'rest'
	 */
	const REST_TOKEN = 'rest' ;

	/**
	 * First token in URLs to access Dev Kit features
	 *
	 * Value:
	 * 'dev'
	 */
	const DEV_TOKEN = 'dev' ;

	private $_tokens = array () ;

	private $_raw = '' ;

	private $_count = 0 ;


	/**
	 * Create a new QueryString object with the given query
	 */
	function __construct ( $query )
	{
		$this->reset($query) ;
	}


	function getType ()
	{
		$tok = $this->getAt(0) ;

		switch ( $tok )
		{
			case self::DB_TOKEN:
				return self::DB_TOKEN ;

			case self::DB_TOKEN:
				return self::DB_TOKEN ;

			case self::DB_TOKEN:
				return self::DB_TOKEN ;

			case self::DB_TOKEN:
				return self::DB_TOKEN ;
		}

		return null ;
	}
	
	function count ()
	{
		return $this->_count ;
	}

	function raw ()
	{
		return $this->_raw ;
	}

	function getAt ( $index = 0 , $default = null )
	{
		return $this->_count > $index ? $this->_tokens[$index] : $default ;
	}

	function getFrom ( $index )
	{
		return array_slice($this->_tokens , 5 ) ;
	}

	function setAt ( $index, $value )
	{
		$this->_tokens[$index] = $value ;
		$this->_raw = implode('/',$this->_tokens) ;
		$this->_count = count ( $this->_tokens ) ;
	}

	function get ()
	{
		return $this->getAt(0) ;
	}

	function reset ( $query )
	{
		$this->_raw = $query ;

		$this->_tokens = explode('/',$query) ;

		$this->_count = count ( $this->_tokens ) ;
	}

}

?>
