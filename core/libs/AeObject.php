<?php

/**
 * <p>AeObject is the base class of most of Aenoa Server classes.</p>
 * 
 * <p>It only offers some methods to debug.</p>
 * 
 */
class AeObject {

    private $_log = array();

    /**
     * Display a simple or complex var value, only if debug mode on.
     * If debuggin mode is set to off, calling this method will trigger a 500 http error
     * 
     * @param mixed $obj
     */
    function debug($obj) {
	if (debuggin()) {
	    pr($obj);
	} else {
	    App::do500(_('Using AeObject->debug() in production mode is not allowed'));
	}
    }

    /**
     * Log a message in a log, in debug mode only
     * 
     * @param string $msg Message to log
     */
    function log($msg) {
	if (debuggin()) {
	    $this->_log[] = $msg;
	}
    }

    /**
     * Returns the array of log, debug mode only
     * 
     * 
     * @return array The log array, an empty array in production mode
     */
    function getLog() {
	return $this->_log;
    }

}

?>