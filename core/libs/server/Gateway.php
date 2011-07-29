<?php

/**********************************
 * Aenoa Server Engine
 * (c) Xavier Laumonier 2010
 *
 * Since : 1.0
 * Author : Xavier Laumonier
 *
 **********************************/

class Gateway {
	
	protected $protocol;
	
	protected $data ;
	
	// Constructor
	function __construct ( $query = null )
	{
		App::noCache () ;
		
		$this->data = App::$sanitizer->getAll ( 'POST' );
		
		if ( $this->data == false )
		{
			$this->data = App::$sanitizer->getAll( 'GET' ) ;
		}
		
		// Create base protocol
		$this->protocol = $this->getProtocol () ;
		
		$this->callService ();
	}
	
	
	protected function callService ()
	{
		$this->protocol->callService( $this->data ) ;
	}
	
	protected function getProtocol ()
	{
		return new AenoaServerProtocol () ;
	}
	
}
?>