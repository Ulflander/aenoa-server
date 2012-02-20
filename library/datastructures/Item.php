<?php

// For Aenoa Server 1.1, codename Alastor
// namespace aenoa\datastructures

/*

	Class: Item

	Item is a simple class to store one unidentified, untyped value.

	It's not very useful as itself but is very practical for making subclasses that formalize a main value to manage.
	
	Example of use:
	(start code)

	$item = new Item () ;

	$item->set ( 'Hello World' ) ;

	echo $item->get () ;
	// Hello World

	var_dump ( $item->has () ) ;
	// bool(true)

	$item->uset () ;
	
	var_dump ( $item->has () ) ;
	// bool(false)

	(end)






 */
class Item extends Object {

	private $_v = null ;

	/**
	 * Creates a new item instance
	 *
	 * @param mixed $value The value of Item
	 */
	public function __construct ( $value = null )
	{
		if ( !is_null($value) )
		{
			$this->set ( $value ) ;
		}
	}

	/**
	 * Get value of item
	 *
	 * @return mixed Value of item
	 */
	public function get ()
	{
		return $this->_v ;
	}

	/**
	 * Set value of Item
	 *
	 * @param mixed $value Value of Item
	 * @return Item Current instance for chained command on this element
	 */
	public function set ( $value )
	{
		$this->_v = $value ;

		return $this ;
	}

	/**
	 * Reset value of Item
	 * 
	 * @return Item Current instance for chained command on this element
	 */
	public function uset ()
	{
		$this->_v = null ;

		return $this ;
	}

	/**
	 * Checks if value of Item has been set
	 *
	 * @return boolean True if value of Item is not null, false otherwise
	 */
	public function has ()
	{
		return is_null($this->_v) === false ;
	}

	/**
	 * Checks if value of Item has not been set
	 *
	 * @return boolean True if value of Item is null, false otherwise
	 */
	public function hasNot ()
	{
		return is_null($this->_v) === true ;
	}


}

?>