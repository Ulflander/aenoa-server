<?php

class Behavior extends Object {

	protected $_parent = null ; 
	
	final public function __construct(&$parent) {
		
		if ( property_exists( $this, 'type' ) && !is_subclass_of($parent, $this->type) )
		{
			throw new ErrorException('[BEHAVIOR]['.get_class($this).'] must be associated only to class ['.$this->type.'] or its subclasses.') ;
		}
		
		$this->_parent = $parent;
	}
	
}

?>