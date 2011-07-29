<?php


class ServiceError {
	
	/**
	 * Unknown error
	 */
	const ERR_3000 = '3000/ Unknown error' ;
	
	/**
	 * Query is null (error sended by Protocol::callService)
	 */
	const ERR_3001 = '3001/ Null query' ;
	
	/**
	 * Service loader is not usable (error sended by Gateway.php::callService)
	 */
	const ERR_3002 = '3002/ Service loader not usable' ;
	
	/**
	 * Service file is missing
	 */
	const ERR_3003 = '3003/ Service file missing' ;
	
	/**
	 * Service description file is missing
	 */
	const ERR_3004 = '3004/ Service description file missing' ;
	
	/**
	 * Service class is missing
	 */
	const ERR_3005 = '3005/ Service class missing' ;
	
	/**
	 * Service description class is missing
	 */
	const ERR_3006 = '3006/ Service description class missing' ;
	
	/**
	 * Service method is not available
	 */
	const ERR_3007 = '3007/ Service method not available' ;
	
	/**
	 * Service requires data
	 */
	const ERR_3008 = '3008/ Service requires data, data is missing' ;
	
	/**
	 * Service package does not exist
	 */
	const ERR_3009 = '3009/ Service package does not exist' ;
	
	/**
	 * Method list in service description is not available
	 */
	const ERR_3010 = '3010/ Method list in service description file not available' ;
	
	/**
	 * Method list in service description is malformed
	 */
	const ERR_3011 = '3011/ Method list in service description file malformed' ;
	
	/**
	 * There is no service method
	 */
	const ERR_3012 = '3012/ No method for the service' ;
	
}
?>