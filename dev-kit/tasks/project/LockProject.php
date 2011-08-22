<?php

class LockProject extends Task {

	var $requireProject = true ;
	
	var $requireValidProject = true ;
	
	
	function process ()
	{
		if ( $this->futil->fileExists ( $this->project->name . DS . '.locked' ) )
		{
			$this->view->setError ( 'This project is yet locked.' ) ;
			return;
		}
		
		$f = new File ( ROOT . $this->project->name . DS . '.locked' , true ) ;
		
		if ( $f->open () && $f->close () )
		{
			$this->view->setSuccess ( 'This project is now locked.' ) ;
		} else {
			$this->view->setError ( 'This project has not been locked. Check out files authorizations.' ) ;
			return;
		}
		
		$this->project->refresh () ;
	}
	
}
?>