<?php


class Behavior {
	
	protected $_parent = null ;
	
	final public function __construct ( &$parent )
	{
		$this->_parent = $parent ;	
	}
}

?>