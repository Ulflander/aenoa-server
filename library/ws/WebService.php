<?php

namespace aenoa\ws ;

/*
	Class: WebService

	Base class for all web services

	[This file is part of Aenoa Server 1.1 codename Alastor]



 */

class WebService extends Object {

	function before ( $paremeters )
	{
		return $paremeters ;
	}

	function after ( $response )
	{
		return $response ;
	}

	function get ( $parameters )
	{
		return array () ;
	}

	final function load ( $parameters , aenoa\ws\WebServiceProtocol $protocol )
	{
		$this->before($paremeters) ;

		$response = $this->get( $parameters ) ;

		$this->after ( $response ) ;

		$response = $this->after($response) ;

		$protocol->respond ( $response ) ;
	}
}

?>
