<?php

class JavascriptController extends Controller{
	
	function getFile ()
	{
		global $FILE_UTIL;
		
		$file = implode( '/' , func_get_args() ) ;
		
		require_once ( AE_CORE . 'js-packer' . DS . 'JavaScriptPacker.php' ) ;
		

		if ( $FILE_UTIL->fileExists ( ROOT.$file ) )
		{
			$c = file_get_contents (ROOT.$file) ;
			$packer = new JavaScriptPacker($c, 'None', true, false);
			$packed = $packer->pack();
			echo $packed ;
			die () ;
		}
	
		
	}
}