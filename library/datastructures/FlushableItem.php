<?php

// For Aenoa Server 1.1, codename Alastor
// namespace aenoa\datastructures

/*
	Class: FlushableItem

	Item automatically or manually flushable

	See Also:
	<Item>, <FlushableInterface>
 */
abstract class FlushableItem extends Item implements FlushableInterface {

	protected $autoflush = true ;

	protected $_inited = false ;

	/**
	 * Creates a new FlushableItem instance
	 *
	 * @param mixed $value The value of Item
	 */
	function __construct($value = null) {
		parent::__construct($value);

		$this->_inited = true ;
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

		if ($this->autoflush && $this->_inited ) {
			$this->flush();
		}

		return $this;
	}

	/**
	 * Reset value of Item
	 *
	 * @return Cookie Current instance for chained command on this element
	 */
	function uset() {
		parent::uset();

		if ($this->autoflush) {
			$this->flush();
		}

		return $this;
	}

	function flush () {} 
	
}

?>
