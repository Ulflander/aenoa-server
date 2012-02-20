<?php


/*
	Class: FlushableCollection

	Collection automatically or manually flushable
	
	See Also:
	<Collection>, <FlushableInterface>
 */
abstract class FlushableCollection extends Collection implements FlushableInterface {
	
	/**
	 * Is collection automatically saved at each edition of data.
	 * 
	 * If $autoflush is set to false, developer has to flush manually the data, however data would be lost.
	 * 
	 * @var boolean 
	 */
	public $autoflush = true;

	protected $_inited = false ;

	/**
	 * Creates a new FlushableCollection instance
	 * 
	 * @param array $vars Array of variables ot insert into collection
	 */
	function __construct(array $vars = array()) {
		parent::__construct($vars);

		$this->_inited = true ;
	}
	

	/**
	 * Set a new pair key/value in the Collection
	 * 
	 * @see Collection::set
	 * @param string $k Key of option
	 * @param mixed $v Value of option
	 * @return Cookie Current instance for chained command on this element
	 */
	function set($k, $v) {
		parent::set($k, $v);

		if ($this->autoflush && $this->_inited ) {
			$this->flush();
		}

		return $this;
	}

	/**
	 * Unset a value if key exists
	 *
	 * @param string $k Key of option
	 * @return Cookie Current instance for chained command on this element
	 */
	function uset($k) {
		parent::uset($k);

		if ($this->autoflush) {
			$this->flush();
		}

		return $this;
	}

	/**
	 * Set a bunch of keys/values pairs from an associative array
	 *
	 * @see Collection::setAll
	 * @param array $array
	 * @return Cookie Current instance for chained command on this element
	 */
	function setAll(array $array) {
		
		$af = $this->autoflush ;

		$this->autoflush = false ;

		parent::setAll($array);

		$this->autoflush = $af ;

		if ($af && $this->_inited) {
			$this->flush();
		}

		return $this;
	}

	/**
	 * Empty all items
	 *
	 * @return Collection Current instance for chained commands on this element
	 */
	function usetAll() {
		parent::usetAll();

		if ($this->autoflush) {
			$this->flush();
		}

		return $this;
	}
	
}

?>
