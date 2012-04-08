<?php


/*
 * RemoteService connects to a remote Aenoa application, using <AenoaServerProtocol>
 */
class RemoteService {
	
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
	
	/**
	 * <p>Get the used protocol</p>
	 * 
	 * <p>Use it to </p>
	 *
	 * @return AbstractProtocol 
	 */
	public function getProtocol ()
	{
		return $this->_protocol ;
	}
	
	/**
	 * <p>Connects to remote service and get response for a POST query</p>
	 * 
	 * @param bool $returnHeaders
	 * @return string Response body 
	 */
	public function connect ( $returnHeaders = true )
	{
		$ch = curl_init($this->_gatewayURL);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS    , $this->_protocol->getToSendData ( $this->_useSessID ) );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_HEADER      , $returnHeaders ? 1 : 0);  // DO NOT RETURN HTTP HEADERS
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
		curl_setopt($ch , CURLOPT_BINARYTRANSFER , 1 );
		return curl_exec($ch);
	}
	/**
	 * <p>Connects to remote service and get response for a GET query</p>
	 *
	 * @param bool $returnHeaders
	 * @return string Response body
	 */
	public function get ( $returnHeaders = false )
	{
		$ch = curl_init($this->_gatewayURL);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_HEADER      , $returnHeaders ? 1 : 0);  // DO NOT RETURN HTTP HEADERS
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
		curl_setopt($ch , CURLOPT_BINARYTRANSFER , 1 );
		return curl_exec($ch);
	}
}

?>