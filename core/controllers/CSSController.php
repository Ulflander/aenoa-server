<?php

class CSSController extends Controller{
	
	function getFile ()
	{
		global $FILE_UTIL;
		
		$file = implode( '/' , func_get_args() ) ;
		
		if ( $FILE_UTIL->fileExists ( ROOT.$file ) )
		{
			$c = file_get_contents (ROOT.$file) ;
			echo $c ;
			die () ;
		}
	}
}