<?php

/**
 * <p>Options is a very simple class to store keys and values</p>
 *
 * <p>It can be considered as a basic Collection.</p>
 *
 * It's used by <Config> to store static configuration values, or by <Widget> for example as classe extension.
 * 
 */
class Options extends AeObject {

	/**
	 * @private 
	 * @var array 
	 */
	private $_vars = array () ;

	/**
	 * Set an option given its key and its value
	 *
	 * @param string $k
	 * @param mixed $v
	 * @return Options Current instance for chained command on this element
	 */
	function set ( $k , $v )
	{
		$this->_vars[$k] = $v ;
		
		ksort($this->_vars);

		return $this ;
	}

	/**
	 * Set a bunch of options from an array
	 *
	 * @param array $array
	 * @return Options Current instance for chained command on this element
	 */
	function setAll ( array $array )
	{
		foreach ( $array as $k => $v )
		{
			$this->set($k, $v) ;
		}

		ksort($this->_vars);

		return $this ;
	}

	/**
	 * Tests if an option exists
	 *
	 * @param string $k The key
	 * @return bool True if option exists, false otherwise
	 */
	function has ( $k )
	{
		return array_key_exists($k, $this->_vars ) ;
	}

	/**
	 * Returns an option given its key
	 *
	 * @param string $k The key
	 * @return mixed The option value if exists, false otherwise
	 */
	function get ( $k )
	{
		if ( array_key_exists($k, $this->_vars ) )
		{
			return $this->_vars[$k] ;
		}

		return null ;
	}

	/**
	 * Returns all options sorted by keys
	 *
	 * @return array All options sorted by keys
	 */
	function getAll ()
	{
		$tvars = $this->_vars ;
		ksort($tvars);
		return $tvars ;
	}

}
?>