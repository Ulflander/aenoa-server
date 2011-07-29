<?php

class AeCSSForm extends AeCSSEdition {
	
	
	private $__classes = array () ;
	
	function __construct ( $css )
	{
		$this->__classes = $this->parse($css);
	}
	
	
	function render ( $fieldnamePrefix = 'css' , $fieldsetClass = '' )
	{
		$result = array() ;
		
		foreach ( $this->__classes as $classname => $rules )
		{
			$result[] = '<fieldset class="'.$fieldsetClass.'">' ;
			$result[] = '<legend>' . $classname . '</legend>' ;
			foreach ( $rules as $property => $value )
			{
				$fieldname = $fieldnamePrefix .'/' .urlize($classname) .'/' .urlize($property) ;
				$result[] = $this->renderField( $fieldname , $classname, $property, $value ) ;
			}
			$result[] = '</fieldset>' ;
		}
		
		return implode("\n", $result);
	}
	
	function renderField ( $fieldname , $classname, $property, $value )
	{
		$str = '<label for="'.$fieldname.'">' . ucfirst(_($property)) . '</label>' ;
		$str .= '<input type="text" id="'.$fieldname.'" name="'.$fieldname.'" value="'.$value.'" data-field-type="color-picker" pattern="^#[A-Fa-f0-9]{3}$|^#[A-Fa-f0-9]{6}$" class="mini" />' ;
		
		return $str ;
	}
	
	function generate ( $data )
	{
		
	}
	
	
}

?>