<?php

class ServiceIntrospector {
	
	
	
	/**
	 * Introspect a service to get a description of all methods.
	 * Returns for each method an array like this one:
	 * [(MethodName)] => Array
	 *          (
	 *              [name] => (MethodName)
	 *              [arguments] => Array
	 *                  (
	 *                      [0] => Array
	 *                          (
	 *                              [name] => (ArgumentName)
	 *                              [optional] => (true/false)
	 *                          )
	 * 
	 *                      [1] => Array
	 *                          (
	 *                              [name] => (ArgumentName)
	 *                              [optional] => (true/false)
	 *                              [default] => (ADefaultValue)
   	 *                          )
	 * 
	 *                  )
	 * 
	 *              [firstLevelReturns] => Array
	 *                  (
	 *                      [0] => Array
	 *                          (
	 *                              [name] => (ReturnDataName)
	 *                              [value] => (ReturnDataValue)
	 *                          )
	 * 
	 *                  )
	 * 
	 *              [secondLevelReturns] => Array
	 *                  (
	 *                      [0] => Array
	 *                          (
	 *                              [name] => (ReturnDataName)
	 *                              [value] => (ReturnDataValue)
	 *                          )
	 * 
	 *                  )
	 * 
	 *         )
	 * 
	 * Returns info (firstLevelReturns , secondLevelReturns)  are indicative, 
	 * it's obviously a 'description' of data that will be returned by the service.
	 * 
	 * FirstLevelReturns means that the data will probably be returned at each time.
	 * SecondLevelReturns means that the data is possibly not returned in some cases. 
	 * It's purely indicative: the developper should explain complex behaviours of services in its own documentation
	 * However, this can be very usefull for debug purposes. 
	 * 
	 * 
	 * @param string $serviceFile
	 * @param string $serviceName
	 * @return array An array with the description of each methods at index 0 and a list of ServiceIntrospectorError at index 1
	 */
	static function introspect ( $serviceFile , $serviceName )
	{
		if ( is_file ( $serviceFile ) )
		{
			require_once ( $serviceFile );
		} else {
			
			return array ( null , array ( new ServiceIntrospectorError ( ServiceIntrospectorError::SERVICE_FILE_NOT_FOUND ) ) ) ;
		}
		
		if ( !class_exists ( $serviceName . 'Service' ) )
		{
			return array ( null , array ( new ServiceIntrospectorError ( ServiceIntrospectorError::SERVICE_NOT_FOUND ) ) ) ;
		}
		
		$coreMethods = array_merge ( get_class_methods ( 'Service' ) , array ( '__construct' , '__destruct' ) ) ;
		$methods = get_class_methods ( $serviceName . 'Service' ) ;
		$description = array () ;
		$errors = array () ;
		$content = str_replace ( array("\t","\n","\r","\r\n") , array('','','','') , file_get_contents($serviceFile) ) ;
		
		$bodyFirstLevelMethodPattern = '/\$this->protocol->addData\s{0,50}\((.*?),(.*?)\)\s{0,50};/ims' ;
		$bodySecondLevelMethodPattern = '/\$this->protocol->setFailure{0,50}\((.*?)\)\s{0,50};/ims' ;
		
		foreach ( $methods as $k => $method )
		{
			if ( in_array ( $method , $coreMethods ) == false )
			{
				$mDesc = array () ;
				
				$pattern = '/function '.$method.'\s{0,500}\(([^\{]*)\)\s{0,500}\{(.*?)\}\s{0,500}(\}|private|protected|public|function|var)\s{0,500}/ims' ;
				preg_match($pattern , $content , $res ) ;
				
				if ( !empty ( $res ) )
				{
					$mDesc['name'] = $method ;
					$mDesc['arguments'] = array () ;
					if ( !empty ( $res[1] ) )
					{
						$arguments = explode ( ',' , 	$res[1] ) ;
						$argument = array () ;
						foreach ( $arguments as $_arg )
						{
							$__arg = explode ( '=' , $_arg ) ;
							$argument['name'] = trim ( $__arg[0] , ' $' ) ;
							$argument['optional'] = (count($__arg) == 2) ;
							if ( $argument['optional'] )
							{
								$argument['default'] = str_replace('\'','"',trim($__arg[1])) ;
							}
							$mDesc['arguments'][] = $argument ;
						}
					}
					
					$methodBody = trim($res[2] , ' {}') ;
					
					$mDesc['firstLevelReturns'] = array () ;
					preg_match_all ( $bodyFirstLevelMethodPattern ,$methodBody , $res2 ) ;
					if ( !empty ( $res2[1] ) )
					{
						$c = count($res2[1]) ;
						for ( $i=0 ; $i<$c ; $i++ )
						{
							$mDesc['firstLevelReturns'][] = array ( 'name' => trim($res2[1][$i],'\' ') , 'value' => str_replace('\'','"',trim($res2[2][$i])) ) ;
						}
					}
					
					$mDesc['secondLevelReturns'] = array () ;
					preg_match_all ( $bodySecondLevelMethodPattern ,$methodBody , $res2 ) ;
					if ( !empty ( $res2[1] ) )
					{
						$c = count($res2[1]) ;
						for ( $i=0 ; $i<$c ; $i++ )
						{
							$mDesc['secondLevelReturns'][] = array ( 'name' => trim($res2[1][$i],'\' ') ) ;
						}
					}
					
					
				} else {
					$errors[] = new ServiceIntrospectorError ( ServiceIntrospectorError::REGEXP_FAILED , $method ) ;
				}
				
				$description[$method] = $mDesc ;
			}
		}
		
		return array ( $description , $errors ) ;
			
	}
	
}

class ServiceIntrospectorError {
	
	const SERVICE_FILE_COMPILE_ERROR = 'Service file has not been compiled successfully by the PHP compiler. Check errors in the PHP file.' ;
	
	const SERVICE_FILE_NOT_FOUND = 'Service file not found' ;
	
	const SERVICE_NOT_FOUND = 'Service class does not exists' ;
	
	const REGEXP_FAILED = 'Method not formatted correctly' ;
	
	public $method ;
	
	public $error ;
	
	function __construct ( $error , $method = null )
	{
		$this->method = $method ;
		
		$this->error = $error ;
	}
}

?>