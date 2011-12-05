<?php

class DoController extends Controller{

	
	/**
	 * Public access to change language
	 */
	function switchLanguage ( $newlang , $nextQuery = null )
	{
		if ( App::isAjax() )
		{
			App::do401 ('This method can not be called through Ajax') ;
		}
		
		if ( App::getI18n()->switchSessionTo($newlang) )
		{
			App::getUser()->setProperty('Webkupi.locale',$newlang)->flushProperties() ;
			
			App::redirect ( url () . ( is_null($nextQuery) ? '' : str_replace('_','/',$nextQuery) ) ) ;
		} else {
			App::doRespond(404,'Lang ' . $newlang . ' does not exists', true, _('Unavailable language') , sprintf(_('Application %s is not available in the requested language.'), Config::get(App::APP_NAME)) );
		}
	}
	
	function error ( $type )
	{
		$html = new Template ( null , sprintf(_('%s error'),$type) ) ;
		
		if ( Template::hasCustom('error'.DS.$type) )
		{
			$this->createView ( 'html' . DS . 'error'.DS.$type . '.thtml'  ) ;
		} else {
			
			$html->setFile ( AE_TEMPLATES . 'html/message.thtml' ); 
				
			if ( $type == 404 )
			{
				$html->set ( 'message' , _('This page has not been found on this server.') ) ;
			} else {
				$html->set ( 'message' , _('Server has triggered a forbidden operation.') ) ;
			}
			
			$html->set ( 'message_class' , 'error' ) ;
			$html->render () ;
		}
	}
	
}
	