<?php


class I18nService extends Service {
	
	
	function getLangSourceFiles ()
	{
		global $futil ;
		
		if ( $futil->dirExists ( 'aenoa-locales' ) == false )
		{
			$this->protocol->addError ( 'There is no folder of Aenoa System & Apps locale source files.' ) ;
			return;
		}
		
		$this->protocol->addData ( 'list' , $this->futil->getFilesList ( ROOT . 'aenoa-locales' ) ) ;
	}
	
}
?>