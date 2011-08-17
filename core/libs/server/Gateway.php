<?php

/**********************************
 * Aenoa Server Engine
 * (c) Xavier Laumonier 2010
 *
 * Since : 1.0
 * Author : Xavier Laumonier
 *
 **********************************/

/**
 * This is an abstract implementation of we web service protocol.
 *
 */
class Gateway {
	
	protected $protocol;
	
	protected $data ;
	
	/**
	 * Constructor will receive the query and call the service
	 * 
	 * @param string $query
	 */
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
	
	/**
	 * This method call the protocol to apply the required service
	 * 
	 *
	 */
	protected function callService ()
	{
		$this->protocol->callService( $this->data ) ;
	}
	
	/**
	* This method returns the good protocol, depending on the concrete implementation of a Gateway
	*
	*@see AbstractProtocol
	* @return AbstractProtocol
	*/
	protected function getProtocol ()
	{
		return new AenoaServerProtocol () ;
	}
	
}
?>