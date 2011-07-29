<?php

class Server {
	
	private $_gatewayURL ;
	
	private $_protocol ;
	
	private $_useSessID = false ;
	
	/**
	 * Create a new connection to use a remote service.
	 * 
	 * @param $gatewayURL The URL of the remote gateway
	 * @param $protocol The protocol 
	 * @param $useSessIDParam Send or not the Session ID.
	 */
	function __construct ( $gatewayURL , $protocol = null, $useSessIDParam = false )
	{
		$this->_gatewayURL = $gatewayURL ;
		
		if ( !is_null( $protocol ) )
		{
			$this->_protocol = $protocol ;
		} else {
			$this->_protocol = new AenoaServerProtocol () ;
		}
		
		$this->_useSessID = $useSessIDParam ;
	}
	
	public function getProtocol ()
	{
		return $this->_protocol ;
	}
	
	public function connect ()
	{
		$ch = curl_init($this->_gatewayURL);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS    , $this->_protocol->getToSendData ( $this->_useSessID ) );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_HEADER      ,1);  // DO NOT RETURN HTTP HEADERS
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
		curl_setopt($ch , CURLOPT_BINARYTRANSFER , 1 );
		return curl_exec($ch);
	}
}

?>