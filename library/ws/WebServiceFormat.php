<?php

namespace aenoa\ws ;

/*
	Class: WebServiceFormat

	Abstract class for Web Services query and response formatting

	[This file is part of Aenoa Server 1.1 codename Alastor]



 */
abstract class WebServiceFormat {

	abstract function decode ( $parameters ) ;

	abstract function encode ( $response ) ;
}

?>
