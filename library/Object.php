<?php

/**
 * Class: Object
 *
 * Object is the base class of most of Aenoa Server classes
 * 
 * It only offers some methods to debug
 * 
 */
class Object {

	private $_log = array();

	/**
	 * Display (print on screen using <pr> function) a value, only if debug mode on.
	 * 
	 * If debuggin mode is set to off, calling this method will trigger a 500 http error using <App::do500>
	 * 
	 * @param mixed $obj
	 * @return Current instance of object for chained command on this element
	 */
	function debug($obj) {
		if (debuggin()) {
			pr($obj);
		} else {
			App::do500(_('Using Object->debug() in production mode is not allowed'));
		}

		return $this;
	}

	/**
	 * Log a message in a log, in debug mode only
	 *
	 * @param string $msg Message to log
	 * @return Current instance of object for chained command on this element
	 */
	function log($msg) {
		if (debuggin()) {
			$this->_log[] = $msg;
		}

		return $this;
	}

	/**
	 * Returns the array of logs, debug mode only
	 * 
	 * 
	 * @return array The log array, an empty array in production mode
	 */
	function getLog() {
		if (debuggin()) {
			return $this->_log;
		}

		return array();
	}

}

?>