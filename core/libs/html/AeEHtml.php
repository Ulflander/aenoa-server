<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AeEFHtml
 *
 * @author xavier
 */
class AeEHtml extends EHtmlBase {


	function __construct ()
	{
		$this->addToken ( '$$' , array ($this, 'makeFormElement') ) ;
	}

	function makeFormElement ( $token , $value )
	{
		
	}

	function fromFileToFile ( $from , $to )
	{
		$f1 = new File ( $from , false ) ;

		if ( !$f1->exists() )
		{
			new ErrorException('File '.$from.' does not exists') ;
			return ;
		}

		$f2 = new File ( $to , true ) ;

		if ( !$f2->exists() )
		{
			new ErrorException('File '.$to.' has not been created') ;
			return ;
		}

		$f2->write($this->evaluate($f1->read())) ;

		$f1->close () ;
		$f2->close () ;
	}


	static function direct ( $file )
	{
		
	}
}

?>
