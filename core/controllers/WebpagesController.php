<?php

class WebpagesController extends Controller {

	
	function beforeAction ( $action )
	{
		if ( App::getUser()->getTrueLevel() > 0 )
		{
		//	App::do401 ('Attempt to access webpages edition') ;
			$this->addResponse('Todo: protect this controller', self::RESPONSE_CRITIC ) ;
		}
		
		
		
	}
	
	function create (  )
	{
		if ( !empty( $this->data ) )
		{
			
		}
	}
	
	function edit ( )
	{
		$webpage = implode('/', func_get_args() ) ;
		
		if ( !$this->futil->fileExists(AE_APP_WEBPAGES.$webpage) )
		{
			$this->addResponse(_(''));
			return;
		}
	}
	
}


?>