<?php


/*
 * Class: ConfDriven
 *
 * ConfDriven is an extensible class with some data parsed from given configuration file.
 * 
 * Conf file will generate an indexed or an associative array of configuration values.
 *
 * Conf is protected, so only extended classes have access to it.
 *
 * Example of basic conf file:
 * The following example will create an indexed array of configuration values when parsed.
 * This file would be located at /path/to/simple/conf.
 * (start code)
 * value1
 * value2
 * value3
 * (end)
 *
 * Example of associative conf file:
 * The following example will create an associative array of configuration keys/values when parsed.
 * This file would be located at /path/to/associative/conf.
 * (start code)
 * key1 > value1
 * key2 > value2
 * (end)
 *
 * Example of use:
 * (start code)
 * // We create a new class that extends ConfDriven
 * class Foo extends ConfDriven {
 *
 *		// We give to our class the path to the simple conf
 *		// file we created juste below
 *		function __construct ()
 *		{
 *			parent::__construct ( 'path/to/simple/conf' ) ;
 *		}
 *
 *		// tests whether a value is in conf
 *		function isInConf ( $val )
 *		{
 *			return in_array ( $val ) ;
 *		}
 * }
 *
 * $foo = new Foo () ;
 *
 * $foo->isInConf ( 'value1' ) ;
 * // true
 *
 * $foo->isInConf ( 'someUnknownValue' ) ;
 * // false
 * 
 * // Next, with an associative conf file
 *
 * // We create a new class that extends ConfDriven
 * class Bar extends ConfDriven {
 *
 *		// We give to our class the path to the associative conf
 *		// file we created juste below
 *		function __construct ()
 *		{
 *			parent::__construct ( 'path/to/associative/conf' ) ;
 *		}
 *
 *		// tests whether a value is in conf
 *		function isInConf ( $val )
 *		{
 *			return in_array ( $val ) ;
 *		}
 *
 *		// get value of a key
 *		function get ( $key )
 *		{
 *			if ( array_key_exists ( $key, $this->conf ) )
 *			{
 *				return $this->conf[$key]
 *			}
 *
 *			return null ;
 *		}
 * }
 *
 * $bar = new Bar () ;
 *
 * $bar->isInConf ( 'value1' ) ;
 * // true
 *
 * $bar->isInConf ( 'value3' ) ;
 * // false
 *
 * $bar->get ( 'key1' ) ;
 * // value1
 *
 * $bar->get ( 'key3' ) ;
 * // null
 *
 * (end)
 * 
 */
class ConfDriven extends Object {

	/**
	 * Variable: Conf file location
	 *
	 * This property is protected so usable in extended classes
	 *
	 * @var string
	 */
	protected $file = '' ;

	/**
	 * Variable: Array of configuration values or keys/values
	 *
	 * This property is protected so usable in extended classes
	 *
	 * @protected
	 * @var array
	 */
	protected $conf = array () ;

	/**
	 * Create a new ConfDriven instance
	 *
	 * @param string $file Location of conf file
	 */
	function __construct ( $file = null )
	{
		if ( !is_null ( $file ) )
		{
			$this->file = $file ;
		}

		if ( !empty ( $this->file ) )
		{
			$f = new File($this->file, true);
			$conf = explode("\n", $f->read());
			$f->close();
			
			$this->parseConf( $conf ) ;
		}
	}
	
	/**
	 *
	 *
	 * @param array $values
	 */
	protected function parseConf ( $values = array () )
	{
		foreach ($values as $val ) {
			if (strpos($val, '>') === false) {
				if ( !empty($val) )
				{
					array_push ( $this->conf , $val ) ;
				}
				
				continue;
			}

			$v = explode('>', $val);
			$this->conf[trim($v[0])] = trim($v[1]);
			
		}
	}
	
}

?>
