<?php

class Widget extends Options {

	protected $file = null ;
	
	function __construct ( $options , $render = true , $echo = true )
	{

		$this->setFile( AE_TEMPLATES . 'widgets' . DS . uncamelize(get_class($this)) .'thtml' ) ;

		$this->setAll ( $options ) ;

		if ( $render )
		{
			$this->render ( $echo ) ;
		}
	}

	/**
	 * Abstract to method to be overidden in concrete Widget classe
	 */
	function check ()
	{
		
	}

	function getFile ()
	{
		return $this->file ;
	}

	function setFile ( $file )
	{
		if ( $this->exists($file) )
		{
			$this->file = $file ;
		}
	}

	function exists ( $file = null )
	{
		if ( is_null( $file ) )
		{
			$file = $this->file ;
		}

		return App::$futil->fileExists($file) ;
	}

	function render ( $echo = true )
	{
		$result = '' ;

		if ( is_null( $this->file ) )
		{
			App::do500 ( _('Template file for widget not found') ) ;
		}
	}

}

?>
