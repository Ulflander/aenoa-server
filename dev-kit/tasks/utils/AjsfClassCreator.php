<?php


class AjsfClassCreator extends Task {
    
	
	// Let's process search
	function process ()
	{
	    
		
		$template = new Template ( 'html/AjsfClassCreator.thtml' ) ;
		$this->view->avoidMessages = true ;
		$this->view = $template ;
		$this->view->layoutName = 'layout-backend' ;
		
	//	$this->view->appendContent ( $template->render ( false ) ) ;
		
		$this->view->render () ;
		
		App::end () ;
	}
	



}







?>