<?php

class WebpagesController extends Controller {

    function beforeAction($action) {
	if (App::getUser()->getTrueLevel() > 0) {
	    App::do401('Attempt to access webpages edition');
	}
	
	$this->view->layoutName = 'layout-backend' ;
    }

    function create() {
	if (!empty($this->data)) {
	    
	}
    }

    function edit() {
	$webpage = implode('/', func_get_args()) ;
	
	if ( strpos($webpage,'.html') === false )
	{
	    $webpage .= '.html';
	}

	if (!$this->futil->fileExists(AE_APP_WEBPAGES . $webpage)) {
	    App::do404('Required webpage does not exist');
	}
	
	
	
	if ( !empty ( $this->data ) && ake ('webpage_content', $this->data ) )
	{
	    $f = new File ( AE_APP_WEBPAGES . $webpage ) ;
	    if ( $f->write ( $this->data['webpage_content'] ) )
	    {
		$this->addResponse(_('Webpage has been successfully edited'));
	    } else {
		$this->addResponse(_('Webpage has NOT been edited. Check out file rights.'));
	    }
	    $f->close() ;
	}
	
	$f = new File ( AE_APP_WEBPAGES . $webpage ) ;
	
	$this->view->set ( 'webpage_content' , $f->read () ) ;
	$this->view->set ( 'webpage_filename' , $webpage ) ;
    }

    
    function preview ()
    {
	$webpage = implode('/', func_get_args()) ;
	
	App::setAjax(false);
	
	if ( !empty ( $this->data ) && ake ('webpage_content', $this->data ) )
	{
	    $hash = sha1($webpage) ;
	    
	    App::$query = $hash ;
	    
	    $f = new File ( AE_APP_WEBPAGES . $hash . '.html' , true ) ;

	    $f->write ( $this->data['webpage_content'] ) ;
	    
	    $f->close() ;
	    	    
	    $this->setView(new Webpage());

	    $this->view->layoutName = 'webpage' ;
	    
	    $this->view->render() ;
	    
	    $f->delete() ;
	    
	} else {
	    App::do404('No preview available') ;
	}
	
	App::end () ;
    }
}

?>