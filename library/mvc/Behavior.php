<?php

class Behavior extends Object {

	protected $_parent = null;
	
	final public function __construct(&$parent) {
		$this->_parent = $parent;
	}

}

?>