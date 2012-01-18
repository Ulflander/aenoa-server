<?php

class InputDataBehavior extends Behavior {
	
	public $type = 'View' ;
	
	function getInput ( $name )
	{
		if ( $this->has('input_data') && ake ($name , $this->vars['input_data']) )
		{
			return $this->vars['input_data'][$name] ; 
		}
		
		return '' ;
	}
	
	
	function getValitidy ( $name )
	{
		
	}
}

?>
