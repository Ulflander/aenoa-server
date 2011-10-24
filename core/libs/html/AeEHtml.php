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
		$this->addToken ( '*' ,'makeFormElement' ) ;
		$this->addToken('+', 'pr');
	}

	function pr ( $token , $value, $inline )
	{
		return '<?php pr ( ' . $value . ') ; ?>' ;
	}

	function makeFormElement ( $token , $value , $inline )
	{
		if ( $inline == false )
		{
			$elements = explode(' ', $value) ;
			$ids = explode('/',$elements[0]) ;
			if ( count($ids) < 3 )
			{
				new ErrorException('IDs for field method are not valid') ;
			}
			return '<?php echo $this->getField(\'' . $ids[0] . '\',\'' . $ids[1] . '\',\'' . $ids[2] . '\', isset($baseURL) ? $baseURL : null, isset($data) ? $data : array() ); ?>' ;
		}
		
		return '' ;
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

		return $f2->write($this->evaluate($f1->read())) &&
			$f1->close () &&
			$f2->close () ;


	}


	static function direct ( $file )
	{
		
	}
}

?>
