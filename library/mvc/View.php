<?php


abstract class View extends Behaviorable {
	
	
	
	/**
	 * Is the template rendered or not.
	 * @var boolean
	 */
	protected $rendered = false ;
	
	public function isRendered ()
	{
		return $this->rendered ;
	}
	
	abstract function set ( $name , $val ) ;
	
	abstract function get ( $name ) ;
	
	abstract function getAll ( ) ;
	
	abstract function setAll ( $array ) ;
	
	abstract function has ( $name ) ;
	
	abstract function prependTemplate ( $content ) ;
	
	abstract function appendTemplate ( $content ) ;
	
	abstract function render ( $echo = true ) ;
	
}
?>