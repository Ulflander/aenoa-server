<?php


/**
 * Class: QueryString
 *
 * QueryString is a little utility to work with query string (e.g. some/slashed/string.html)
 *
 * It's mainly used by <Dispatcher> to easily read the client get query string.
 *
 * See also:
 * <App>, <Dispatcher>
 */
class QueryString extends Object {


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

	// Tokens of query string as array
	private $_tokens = array () ;

	// Raw query string
	private $_raw = '' ;

	// Number of tokens
	private $_count = 0 ;

	/**
	 * Create a new QueryString object with the given query
	 */
	function __construct ( $query )
	{
		$this->reset($query) ;
	}

	/**
	 * Returns type of query, for core main tokens (database, api, rest, dev)
	 *
	 * @return mixed Returns first token if core token, false otherwise
	 */
	function getType ()
	{
		switch ( $this->get () )
		{
			case self::DB_TOKEN:
				return self::DB_TOKEN ;

			case self::SERVICES_TOKEN:
				return self::SERVICES_TOKEN ;

			case self::REST_TOKEN:
				return self::REST_TOKEN ;

			case self::DEV_TOKEN:
				return self::DEV_TOKEN ;
		}

		return null ;
	}

	/**
	 * Returns number of tokens in query string
	 *
	 * @return int Number of tokens in query string
	 */
	function count ()
	{
		return $this->_count ;
	}

	/**
	 * Returns the raw query string
	 *
	 * @return string Raw query string
	 */
	function raw ()
	{
		return $this->_raw ;
	}

	/**
	 * Get first token
	 *
	 * @return string First token of query string
	 */
	function get ()
	{
		return $this->getAt(0) ;
	}

	/**
	 * Get a token at given index.
	 *
	 * Returns a default value if given
	 *
	 * @param int $index Index of token to get, default is 0
	 * @param string $default Default value to return if token does not exists, default is null
	 * @return string Token if exists, default value otherwise
	 */
	function getAt ( $index = 0 , $default = null )
	{
		return $this->_count > $index ? $this->_tokens[$index] : $default ;
	}

	/**
	 * Get list of tokens from a given index
	 *
	 * @param int $index Start index for tokens selection, default is 0
	 * @return array List of tokens as array, from given index
	 */
	function getFrom ( $index = 0 )
	{
		return array_slice( $this->_tokens , $index ) ;
	}

	/**
	 * Get a raw query string from start index
	 *
	 * @param int $index, default is 0
	 * @return string Query string of tokens from given index
	 */
	function getRawFrom ( $index = 0 )
	{
		return implode('/', $this->getFrom( $index ) ) ;
	}

	/**
	 * Reset the query string with the given string
	 *
	 * @param string $query New query string
	 * @return QueryString Current instance for chaind command on this element
	 */
	function reset ( $query )
	{
		if ( !is_string( $query ) )
		{
			return $this;
		}

		$this->_tokens = explode('/',$query) ;
		
		// Clear empty tokens
		array_clean( $this->_tokens ) ;

		$this->_raw = implode('/', $this->_tokens );

		$this->_count = count ( $this->_tokens ) ;

		return $this;
	}

	/**
	 * Set a token at given index
	 *
	 * @param int $index Index of token
	 * @param string $value Token value
	 * @return QueryString Current instance for chaind command on this element
	 */
	function setAt ( $index, $value )
	{
		if ( !is_string( $value ) )
		{
			return $this;
		}

		$this->_tokens[$index] = $value ;
		
		// Clear empty tokens
		array_clean( $this->_tokens ) ;

		$this->_raw = implode('/',$this->_tokens) ;
		
		$this->_count = count ( $this->_tokens ) ;

		return $this;
	}

}


?>