<?php


class Field {
    
	public $name ;
	
	public $label ;
	
	public $description = '' ;
	
	public $type = 'input' ;
	
	public $required ;
	
	public $validation ;
	
	public $values ;
	
	public $value ;
	
	public $attributes = array () ;
	
	public $urlize = false ;
	
	public $valid ;
	
	public $fieldset ;
	
    function setupConfirm ( $message ) {
    	
		$this->label = $message ;
		$this->name = 'confirm' ;
		$this->type = 'radio' ;
		$this->description = 'You must confirm you want to run this task.' ;
		$this->values = array ( 'confirmed' => 'Confirm' , 'cancelled' => 'Cancelled' ) ;
		$this->required = true ;
    }
    
    /**
     * Set value of the field depending of its type
     * @param $value
     * @return unknown_type
     */
    function setValue ( $value )
    {
    	switch ( $this->type )
    	{
    		case 'checkbox':
    			if ($value=='on' || $value === true || $value =='true' ) 
    				$this->attributes['checked'] = 'checked' ;
    			else unset ( $this->attributes['checked'] ) ;
    			break;
    		case 'input':
    		case 'textfield':
    		case 'select':
    		case 'radio':
    			$this->value = $value ;
    				break;
    	}
    }
    
    function disable ()
    {
    	$this->attributes['readonly'] = 'true' ;
    }
    
    function enable ()
    {
    	unset ( $this->attributes['readonly'] ) ;
    }
}
?>