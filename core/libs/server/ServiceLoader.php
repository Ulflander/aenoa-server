<?php


class ServiceLoader {
	
	private $_usable = false ;
	
	private $_errorCode ;
	
	private $_service ;
	
	private $_method ;
	
	function __construct ( $query )
	{
		$this->_errorCode = ServiceError::ERR_3000 ;
			
		$classname = ucfirst($query->serviceClass) . 'Service' ;
		$dclassname = ucfirst($query->serviceClass) . 'ServiceDescription' ;
		
		$fname = ucfirst($query->serviceClass) . '.service.php' ;
		$dfname = ucfirst($query->serviceClass) . '.description.php' ;
		
		switch ( $query->servicePackage )
		{
			case 'core':
				$rootPath = AE_CORE_SERVICES ;
				break;
			default:
				$rootPath = ROOT .'app'.DS. 'services' . DS . $query->servicePackage . DS ;
				break;
		}
		
		if ( is_dir ( $rootPath ) == false ) 
		{
			$this->_errorCode = ServiceError::ERR_3009 ;
			return;
		}
		
		if ( is_file ( $rootPath . $fname ) == false || !require_once ( $rootPath . $fname ) )
		{
			$this->_errorCode = ServiceError::ERR_3003 ;
			return;
		}
		
		if ( is_file ( $rootPath . $dfname ) == false || !require_once ( $rootPath . $dfname ) )
		{
			$this->_errorCode = ServiceError::ERR_3004 ;
			return;
		}
		
		if ( class_exists( $classname ) == false )
		{
			$this->_errorCode = ServiceError::ERR_3005 ;
			return;
		}
		
		if ( class_exists( $dclassname ) == false )
		{
			$this->_errorCode = ServiceError::ERR_3006 ;
			return;
		}
		
		$serviceClass = new $classname () ;
		$description = new $dclassname () ;
		
		if ( property_exists($description, 'methods' ) == false )
		{
			$this->_errorCode = ServiceError::ERR_3010 ;
			return;
		}
		
		$methods = $description->methods ;
		$method = array () ;
		
		foreach ( $methods['methods'] as $k => $v )
		{
			if ( is_array ( $v ) &&
			 array_keys_exists ( array( 
			 	ServiceDescription::METHOD_ID_KEY,
				ServiceDescription::METHOD_ARGS_KEY,
				ServiceDescription::METHOD_DESC_KEY ) , $v ) )
			{
				if ( $v[ServiceDescription::METHOD_ID_KEY] == $query->serviceMethod )
				{
					$this->_method = $v ;
				}
			} else {
				$this->_errorCode = ServiceError::ERR_3011 ;
				return;
			}
		}
		
		if ( is_null ( $this->_method ) || method_exists( $serviceClass, $query->serviceMethod ) == false ) 
		{
			$this->_errorCode = ServiceError::ERR_3007 ;
			return;
		}
		
		if ( empty ( $this->_method ) )
		{
			$this->_errorCode = ServiceError::ERR_3012 ;
			return;
		}
		
		
		
		$this->_service = $serviceClass ;
		
		$this->_usable = true ;
	}
	
	public function getMethod ()
	{
		return $this->_method;
	}
	
	private $_descriptionArray = array (
		'id' => 'Id of the method' ,
		'arguments' => array (
				'arg_key' => 'Description of the argument'
		) ,
		'description' => 'Description of the service'
	) ;
	
	function isUsable ()
	{
		return $this->_usable ;
	}
	
	function getService ()
	{
		return $this->_service ;
	}
	
	function getErrorCode ()
	{
		return $this->_errorCode ;
	}
}
?>