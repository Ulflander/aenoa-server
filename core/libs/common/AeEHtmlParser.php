<?php


/**
 * Description of AeEHtmlParser
 *
 * Get a EHtml template and returns a HTML template
 */
class AeEHtmlParser {
	//put your code here


	static $STATE_COMMENT = 'comment' ;
	
	static $STATE_INLINE = 'inline' ;

	static $STATE_MULTILINE = 'multiline' ;


	function evaluate ( $template = 'No template given' , $variables = array () )
	{
		extract($variables) ;

		$inComment = false ;

		$lines = explode ("\n" , $template ) ;

		

		$length = strlen($template) ;


		
	}


}

?>
