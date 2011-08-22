<?php

/**
 * AenoaServerProtocol is used to control Aenoa services
 * 
 * @see Gateway
 * @see Service
 * @see ServerAuthCheck
 */
class AenoaServerProtocol extends AbstractProtocol {
	
	const SID_KEY = 'sid' ;
	
	const SERVICE_KEY = 'service' ;
	
	const DATA_KEY = 'data' ;
	
	const ENCRYPTION_KEY = 'seckey' ;
	
	const ERROR_KEY_PREFIX = 'error_' ;
	
	const SUCCESS_KEY = 'success' ;
	
	const SERVICE_STRING_TOKEN = '::' ;
	
	
	
	private $_preformatted = null ;
	
	function addPreformattedData ( $value )
	{
		$this->_preformatted = $value ;
	}
	
	
	function getToSendData ( $useSessIDParam = true )
	{
		$dat = array () ;
		if ( $useSessIDParam == true )
		{
			$dat[self::SID_KEY] = App::$session->getSID() ;
		}
		
		$dat[self::SERVICE_KEY] = $this->_service ;
		
		if ( !is_string($this->_preformatted) )
		{
			$dat[self::DATA_KEY] = json_encode ( $this->_data ) ;
		} else {
			$dat[self::DATA_KEY] = $this->_preformatted ;
		}
		
		return $dat ;
	}
	
	function validateData ( $data )
	{
		if ( array_key_exists(self::SERVICE_KEY, $data ) 
			&& array_key_exists(self::DATA_KEY, $data ) )
		{
			if ( array_key_exists(self::SID_KEY, $data ) && App::$session->checkSID ( $data[self::SID_KEY] , true ) == false )
			{
				App::do403 ('ajax session validation failed') ;
			}
			
			$this->_receivedData = std2arr(json_decode( stripslashes($data[self::DATA_KEY] )) );
			
			$this->_service = $data[self::SERVICE_KEY] ;
			$this->_encryptionKey = @$data[self::ENCRYPTION_KEY] ;
			
			return true ;
		}
		
		return false ;
	}
	
	function getMethodArguments ( $methodDescription ) 
	{
		$res = array () ;
		if ( empty ( $methodDescription ) 
			|| !array_key_exists(ServiceDescription::METHOD_ARGS_KEY, $methodDescription) )
			{
				return $res ;
			}
			
		foreach ( $methodDescription[ServiceDescription::METHOD_ARGS_KEY] as $argument )
		{
			if ( array_key_exists ( $argument[ServiceDescription::ARG_ID_KEY] , $this->_receivedData ) )
			{
				$res[] = $this->_receivedData[$argument[ServiceDescription::ARG_ID_KEY]] ;
			} else
			{
				if ( ake(ServiceDescription::ARG_DEFAULT_KEY, $argument) )
				{
					$res[] = ($argument[ServiceDescription::ARG_DEFAULT_KEY] == '""' ? '' : $argument[ServiceDescription::ARG_DEFAULT_KEY] ) ;
				} else {
					return false ;
				}
			}
		}
		
		return $res ;
	}
	
	function getQuery ()
	{
		$s_arr = explode ( self::SERVICE_STRING_TOKEN , $this->_service ) ;
		$query = new ServiceQuery () ;
		if ( count ( $s_arr ) == 3 )
		{
			$query->servicePackage = $s_arr[0] ;
			$query->serviceClass = $s_arr[1] ; 
			$query->serviceMethod = $s_arr[2] ; 
			$query->serviceData = $this->_receivedData ;
			return $query ;
		} else {
			$query->serviceClass = $s_arr[0] ; 
			$query->serviceMethod = $s_arr[1] ; 
			$query->serviceData = $this->_receivedData ;
			return $query ;
		}
		return null ;
	}
	
	function getFormattedResponse ()
	{
		$r = false ;
		$arr = array () ;
		$arr[self::SID_KEY] = App::$session->getSID () ;
		$arr[self::SERVICE_KEY] = $this->_service ;
		$arr[self::DATA_KEY] = array () ;
	
		if ( !is_string( $this->_preformatted ) )
		{
			foreach ( $this->_data as $k => $v )
			{
				$arr[self::DATA_KEY][$k] = $v ;
			}
		} else {
			$arr[self::DATA_KEY] = '324__DATA__088' ;
			$r = true ;
		}
		
		if ( !empty ( $this->_errs ) )
		{
			
			$i = 0 ;
			while ( !empty ( $this->_errs ) )
			{
				$arr[self::ERROR_KEY_PREFIX . $i] = array_pop( $this->_errs ) ;
				$i ++ ;
			}
			$arr[self::SUCCESS_KEY] = false ;
		} else {
			$arr[self::SUCCESS_KEY] = true ;
		}
		
		
		$s = json_encode($arr)  ;
		if ( $r )
		{
			$s = str_replace('"324__DATA__088"', $this->_preformatted, $s );
		}
		
		header ('Content-Type: text/x-json');
		
		return $s ;
	}

	/**
	 * Called by Gateway::__construct()
	 * @private
	 * @param object $query [optional]
	 * @return 
	 */
	function callService ( $data )
	{
		// If query array as designed in protocol getServiceQuery method is empty
		if( $this->validateData ( $data ) == false )
		{
			if ( ake('query',$data) && count($data) == 1 && debuggin () )
			{
				App::doRespond(200,_('Service system is enabled'), true, _('Service test success'), _('Refer to Aenoa Server documentation to use the services API.')) ;
			} else {
				// send the 404
				App::do404 ('data not valid') ;
			}
		// Query is not empty
		} else {
			// Parse the query
			$query = $this->getQuery () ;
			
			// If query parsing failed
			if ( is_null ( $query ) ) 
			{
				$this->setFailure( ServiceError::ERR_3001 ) ;
			}
			
			// Get the service loader
			$loader = new ServiceLoader ( $query ) ;
			
			// If the service loader failed
			if ( $loader->isUsable () == false )
			{
				$this->setFailure( array ( ServiceError::ERR_3002 , $loader->getErrorCode () ) ) ;
			}
			
			// Service is available
			$service = &$loader->getService () ;
			
			// Initialization of service
			$service->protocol = &$this ;
			
			$arguments = $this->getMethodArguments ( $loader->getMethod () ) ;
			
			
			if ( $arguments === false )
			{
				$this->setFailure ( array ( ServiceError::ERR_3008 ) ) ;
			}
			
			// We call the service method
			$service->applyQuery ( $query , $arguments ) ;
			
			// And we're done
			$this->respond () ;
		}
	}
	
	public function setFailure ( $errorCode )
	{
		if ( is_array ( $errorCode ) ) 
		{
			foreach ( $errorCode as $code )
			{
				$this->addError ( $code ) ;
			}
		} else {
			$this->addError ( $errorCode ) ;
		}
		
		$this->respond () ;
		App::end () ;
	}
}
?>