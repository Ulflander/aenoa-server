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
	private $_vars = array();

	/**
	 * @private
	 * @var array
	 */
	private $_formal = array();

	/**
	 * Set an option given its key and its value
	 *
	 * @param string $k Key of option
	 * @param mixed $v Value of option
	 * @return Options Current instance for chained command on this element
	 */
	function set($k, $v) {
		// We check if option has been formalized by a DBField object
		if (!empty($this->_formal) && ake($k, $this->_formal) && !$this->_formal[$k]->validate($v)) {
			$msg = 'Value ' . $v . ' for option ' . $k . ' is not valid';

			if (debuggin()) {
				new ErrorException($msg);
			} else {
				App::do500($msg);
			}

			return $this;
		}

		$this->_vars[$k] = $v;

		ksort($this->_vars);

		return $this;
	}

	/**
	 * Unset an option if option exists
	 * 
	 * @param string $k Key of option 
	 * @return Options Current instance for chained command on this element
	 */
	function uset($k) {
		if (ake($k, $this->_vars)) {
			unset($this->_vars[$k]);
		}

		return $this;
	}

	/**
	 * Set a bunch of options from an associative array
	 *
	 * @param array $array
	 * @return Options Current instance for chained command on this element
	 */
	function setAll(array $array) {
		foreach ($array as $k => $v) {
			$this->set($k, $v);
		}

		ksort($this->_vars);

		return $this;
	}

	/**
	 * Tests if an option exists
	 *
	 * @param string $k The key
	 * @return bool True if option exists, false otherwise
	 */
	function has($k) {
		return array_key_exists($k, $this->_vars);
	}

	/**
	 * Returns an option given its key
	 *
	 * @param string $k The key
	 * @return mixed The option value if exists, false otherwise
	 */
	function get($k) {
		if (array_key_exists($k, $this->_vars)) {
			return $this->_vars[$k];
		}

		return null;
	}

	/**
	 * Returns all options sorted by keys
	 *
	 * @return array All options sorted by keys
	 */
	function getAll() {
		$tvars = $this->_vars;
		ksort($tvars);
		return $tvars;
	}

	/**
	 * Formalize options with DBFields objects
	 * 
	 * @param array $fields 
	 * @return Options Current instance for chained command on this element
	 */
	function formalize(array $fields) {
		foreach ($fields as $f) {
			$this->setFormal($f);
		}
		return $this;
	}

	/**
	 * Set a formal option
	 * 
	 * @param DBField $field Field object that formalize an option
	 * @return Options Current instance for chained command on this element
	 */
	function setFormal(DBField $field) {
		$this->_formal[$f->name] = $f;

		return $this;
	}

	/**
	 * Unset formalization of a field
	 * 
	 * @param string $k Key of formalized option
	 * @return Options Current instance for chained command on this element
	 */
	function usetFormal($k) {
		if (ake($k, $this->_formal)) {
			unset($this->_formal[$k]);
		}

		return $this;
	}

	/**
	 * Check if an option has been formalized
	 * 
	 * @param string $k Key of formalized option
	 * @return boolean True if option is formalized, false otherwise
	 */
	function hasFormal($k) {
		return ake($k, $this->_formal);
	}

	/**
	 * Get DBField object of a formalized option
	 * 
	 * @param string $k Key of formalized option
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

}

?>