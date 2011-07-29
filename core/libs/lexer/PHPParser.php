<?php

class PHPParser {
	
	function __construct ( $file )
	{
		
		$lexer = new Stagehand_PHP_Lexer ( $file ) ;
		$parser = new Stagehand_PHP_Parser ( $lexer ) ;
		$tokens = $lexer->getTokens() ;
		
		foreach ( $tokens as $k => $v )
		{
			echo $k . ' : ' ;
			print_r ( $v ) ;
			echo '<br>';
		}
		
		
	}
	
}

?>