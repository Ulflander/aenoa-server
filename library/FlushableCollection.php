<?php


/**
 * Class: FlushableCollection
 * 
 */
abstract class FlushableCollection extends Collection {
	
	/**
	 * Is collection automatically saved at each edition of data.
	 * 
	 * If $autoflush is set to false, developer has to flush manually the data, however data would be lost.
	 * 
	 * @var boolean 
	 */
	public $autoflush = true;
	

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
		parent::setAll($array);


		if ($this->autoflush) {
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
	
	
	abstract function flush () ;
	
}

?>
