<?php

namespace aenoa\ws ;

/*
	Class: WebServiceProtocol
	
	Abstract class for Web Services Protocols

	[This file is part of Aenoa Server 1.1 codename Alastor]



 */

abstract class WebServiceProtocol {

	abstract function query () ;

	abstract function respond ( $response ) ;
}

?>
