<?php

/**
 * 
 *
 */
abstract class AbstractProtocol {
	
	
	protected $_success = true ;
	
	protected $_errs = array () ;
	
	protected $_response = array () ;
	
	protected $_data = array () ;
	
	protected $_receivedData = array () ;
	
	protected $_service = '' ;
	
	protected $_encryptionKey ;
	
	protected $_gzip = false ;
	
	/**
	 * getFormattedResponse should be implemented in concrete protocol class
	 * and should format the response.
	 */
	abstract function getFormattedResponse () ;
	
	/**
	 * validateData should be implemented in concrete protocol class
	 * and should return true if data is valid and usable, and false otherwise.
	 * 
	 * If data is valid, then it has to be set in {Service}->_receivedData protected prop
	 * to be usable in concrete AbstractProtocol methods.
	 * 
	 * The $data var contains all data received in POST.
	 */
	abstract function validateData ( $data ) ;
	
	/**
	 * getQuery should be implemented in concrete protocol class
	 * and should return a ServiceQuery object.
	 */
	abstract function getQuery () ;
	
	abstract function getToSendData () ;
	
	abstract function addPreformattedData ( $value ) ;
	
	final function setService ( $service )
	{
		$this->_service = $service ;
	}
	
	final function getReceivedData ()
	{
		$tdata = $this->_receivedData ;
		return $tdata ;
	}
	
	final function getData ()
	{
		$tdata = $this->_data ;
		return $tdata ;
	}
	
	final function addData ( $key , $value )
	{
		$this->_data[$key] = $value ;
	}
	
	final function setGZip ( $val )
	{
		if ( $val === true )
		{
			$this->_gzip = $val ;
		} else {
			$this->_gzip = false ;
		}
	}
	
	final function isGZip ()
	{
		return $this->_gzip ;
	}
	
	final function addError ( $message )
	{
		if ( $this->_success == true )
		{
			$this->_success = false ;
		}
		$this->_errs[] = $message ;
	}
	
	final function setSuccess ( $success )
	{
		$this->_success = $success ;
	}
	
	final function respond ()
	{
		if ( $this->_gzip == false )
		{
			echo $this->getFormattedResponse() ;
			return ;
		}
		
		header('Content-Encoding: gzip');
		ob_start("ob_gzhandler");
		echo $this->getFormattedResponse() ;
		ob_end_flush();
	}
	
}
?>