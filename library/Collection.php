<?php

/**
 * Class: Collection
 *
 * Collection is a very simple class to store keys and values. 
 *
 * It's used by <Config> to store static configuration values, or by <Widget> for example as classe extension.
 *
 * Notice:
 * Please note that format of keys are not verified. Extended classes like <Session> may have a dot syntax (Keys like "Some.value"),
 * but others like <View> won't support dot syntax.
 *
 */
class Collection extends Object {

	/**
	 * @private 
	 * @var array 
	 */
	private $_vars = array();

	/**
	 * @private
	 * @var array
	 */
	private $_formal = array();
	
	/**
	 *@private
	 * @var array 
	 */
	private $_indexes = array () ;
	
	/**
	 * @private  
	 */
	private $_length = 0 ;
	
	/**
	 *
	 * @var type 
	 */
	private $_idx = 0 ;
	
	private $_curkey = null ;
	
	/**
	 * Create a new Collection
	 *
	 * @param array $vars [Optional] Associative array of items names/values
	 */
	function __construct ( array $vars = array() )
	{
		$this->setAll ( $vars ) ;
	}
	
	
	function next ()
	{
		if ( empty ( $this->_indexes ) )
		{
			$this->_indexes = $this->inventory () ;
		}
		
		
		$item = $this->item() ;
		
		
		
		if ( !is_null ( $item ) )
		{
			$this->_curkey = $this->_indexes[$this->_idx] ;
			$this->_idx ++ ;
		} else {
			$this->_curkey = null ;
			$this->_idx = 0 ;
		}
		
		return $item ;
	}
	
	/**
	 * Reset the iterator to 0 or to given index
	 *
	 * @param int $index [Optional] Index of iterator, default is 0
	 * @return Collection Current instance for chained command on this element
	 */
	function iterator ( $index = 0 )
	{
		$this->_idx = $index ;

		return $this ;
	}
	
	/**
	 * Get the current value of iterator
	 * 
	 * @return int Current value of iterator
	 */
	function current ()
	{
		return $this->_idx; 
	}

	/**
	 * Get value for current iterator index
	 *
	 * @return mixed Value if index exists, null otherwise
	 */
	function item ()
	{
		return $this->_idx < $this->_length ? $this->_vars[$this->_indexes[$this->_idx]] : null ;
	}
	
	function key ()
	{
		return $this->_curkey ;
	}

	
	
	
	
	/**
	 * Set an item given its key and its value
	 *
	 * @param string $k Key of item
	 * @param mixed $v Value of item
	 * @return Collection Current instance for chained command on this element
	 */
	function set($k, $v) {
		// We check if item has been formalized by a DBField object
		if (!empty($this->_formal) && ake($k, $this->_formal) && !$this->_formal[$k]->validate($v)) {
			$msg = 'Value ' . $v . ' for item ' . $k . ' is not valid';

			if (debuggin()) {
				throw new ErrorException($msg);
			} else {
				App::do500($msg);
			}

			return $this;
		}
		
		$this->_length ++ ;

		$this->_vars[$k] = $v;

		ksort($this->_vars);

		return $this;
	}

	/**
	 * Unset an item if item exists
	 * 
	 * @param string $k Key of item
	 * @return Collection Current instance for chained command on this element
	 */
	function uset($k) {

		if (strpos($k, '*') === false) {
			unset($this->_vars[$k]);
		} else {

			foreach ($this->_vars as $key => $val) {
				preg_match_all('|^' . str_replace('\\*', '[a-z0-9\_\/]{1,}', preg_quote($k)) . '$|i', $key, $m);

				if ($m && !empty($m[0])) {
					unset($this->_vars[$key]);
				}
			}
		}

		$this->_length -- ;

		if (ake($k, $this->_vars)) {
			unset($this->_vars[$k]);
		}

		return $this;
	}

	/**
	 * Set a bunch of items from an associative array
	 *
	 * @param array $array
	 * @return Collection Current instance for chained command on this element
	 */
	function setAll(array $array) {
		
		
		foreach ($array as $k => $v) {
			$this->set($k, $v);
		}

		return $this;
	}


	/**
	 * Empty all items
	 *
	 * @return Collection Current instance for chained commands on this element
	 */
	function usetAll() {
		$this->_vars = array();
		$this->_length = 0 ;
		return $this;
	}
	
	/**
	 * Tests if an item exists
	 *
	 * @param string $k The key
	 * @return bool True if item exists, false otherwise
	 */
	function has($k) {
		return array_key_exists($k, $this->_vars);
	}

	/**
	 * Returns an item given its key
	 *
	 * @param string $k The key
	 * @return mixed The item value if exists, NULL otherwise
	 */
	function get($k) {
		if ( is_string($k) ) {
			if (array_key_exists($k, $this->_vars)) {
				return $this->_vars[$k];
			}
		} else {
			throw new ErrorException('Collection keys must allways be string') ;
		}
		
		return null;
	}

	/**
	 * Returns all items, sorted by keys
	 *
	 * @return array All items sorted by keys
	 */
	function getAll() {
		$tvars = $this->_vars;
		return $tvars;
	}

	/**
	 * Delete and returns an item
	 *
	 * Returns null if does not exist
	 *
	 * @param string $key Key of item
	 * @return mixed Value of item if item exists, null otherwise
	 */
	function uget($key) {
		if (array_key_exists($key, $this->_vars)) {
			$val = $this->_vars[$key];
			unset($this->_vars[$key]);
			return $val;
		}

		return null;
	}

	/**
	 * Returns a list of all items names
	 *
	 * @return array List of items names
	 */
	function inventory ()
	{
		return array_keys($this->_vars) ;
	}

	/**
	 * Formalize items with DBFields objects
	 * 
	 * @param array $fields 
	 * @return Collection Current instance for chained command on this element
	 */
	function formalize(array $fields) {
		foreach ($fields as $f) {
			$this->setFormal($f);
		}
		return $this;
	}

	/**
	 * Set a formal item
	 * 
	 * @param DBField $field Field object that formalize an item
	 * @return Collection Current instance for chained command on this element
	 */
	function setFormal(DBField $field) {
		$this->_formal[$f->name] = $f;

		return $this;
	}

	/**
	 * Unset formalization of a field
	 * 
	 * @param string $k Key of formalized item
	 * @return Collection Current instance for chained command on this element
	 */
	function usetFormal($k) {
		if (ake($k, $this->_formal)) {
			unset($this->_formal[$k]);
		}

		return $this;
	}

	/**
	 * Check if an item has been formalized
	 * 
	 * @param string $k Key of formalized item
	 * @return boolean True if item is formalized, false otherwise
	 */
	function hasFormal($k) {
		return ake($k, $this->_formal);
	}

	/**
	 * Get DBField object of a formalized item
	 * 
	 * @param string $k Key of formalized item
	 * @return DBField DBField object if found, null otherwise 
	 */
	function getFormal($k) {
		if (ake($k, $this->_formal)) {
			return $this->_formal[$k];
		}

		return null;
	}

	/**
	 * Validate formalized fields
	 * 
	 * <p>Loop on all formalized fields, apply values, then validates fields</p>
	 * 
	 * @return mixed Boolean true if all fields validated, array of unvalidated DBField objects otherwise
	 */
	function validate() {
		$errors = array();


		foreach ($this->_formal as &$field) {
			$field->value = $this->get($field->name);

			if (!$field->validate()) {
				$errors[] = $field;
			}
		}

		return empty($errors) ? true : $errors;
	}

	/**
	 * Returns count of elements in Collection
	 *
	 * @return int Count of elements in Collection
	 */
	function count ()
	{
		return $this->_length ;
	}

	/**
	 * Alias of Collection::count
	 *
	 * @see Collection::count
	 * @return int Count of elements in Collection
	 */
	function length ()
	{
		return $this->count() ;
	}
	
}

?>