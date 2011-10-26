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

	function makeFormElement ( $token , $value , $inline = false , $element = null )
	{
		if ( $inline == false )
		{
			$elements = explode(' ', $value) ;
			$ids = explode('/',  array_shift($elements)) ;
			if ( count($ids) < 3 )
			{
				new ErrorException('IDs for field method are not valid') ;
			}
			if ( count ($elements) == 0 )
			{
				$container = 'true' ;
				$label = 'true' ;
				$field = 'true' ; 
				$desc = 'true' ;
			} else {
				$container = 'false' ;
				$label = 'false' ;
				$field = 'false' ;
				$desc = 'false' ;
				
				while ( $el = array_shift($elements) )
				{
					if ( substr($el,0,1) === '_' )
					{
						switch ( $el )
						{
							case '_label': $label = 'true'; break;
							case '_container': $container = 'true'; break;
							case '_field': $field = 'true'; break;
							case '_description': $desc = 'true'; break;
						}
					}
				}
			}
			
			return '<?php echo $this->getField(\'' . $ids[0] . '\',\'' . $ids[1] . '\',\'' . $ids[2] . '\', isset($baseURL) ? $baseURL : null, isset($data) ? $data : array() , '.$container.' , '.$label.' , '.$field.' , '.$desc.' );  ?>' ;
		} else if ( !is_null($element) && $element->keyword == 'form' )
		{
			$elements = explode(' ', $value) ;
			$ids = explode('/',  array_shift($elements)) ;
			if ( count($ids) < 2 )
			{
				new ErrorException('IDs for field method are not valid') ;
			}
			return '<?php echo $this->getFormTagAttributes(\'' . $ids[0] . '\',\'' . $ids[1] . '\', isset($baseURL) ? $baseURL : null, isset($data) ? $data : array() );  ?>' ;
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
