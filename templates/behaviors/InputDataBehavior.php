<?php

class InputDataBehavior extends Behavior {
	
	public $type = 'View' ;
	
	function getInput ( $name )
	{
		if ( $this->_parent->has('input_data') && ake ($name , $this->_parent->vars['input_data']) )
		{
			return $this->_parent->vars['input_data'][$name] ;
		}
		
		return '' ;
	}
	
	
	function getValitidy ( $name )
	{
		if ( $this->_parent->has('validities') )
		{
			if ( ake ( $name , $this->_parent->vars['validities'] ) )
			{
				return 'valid' ;
			}

			return 'invalid' ;
		}

		return '' ;
	}
}

?>
