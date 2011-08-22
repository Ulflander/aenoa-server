<?php

class UnlockProject extends Task {

	var $requireProject = true ;
	
	var $requireValidProject = false ;
	
	
	function process ()
	{
		if ( $this->futil->fileExists ( $this->project->name . DS . '.locked' ) == false )
		{
			$this->view->setError ( 'This project is yet locked.' ) ;
			return;
		}
		
		if ( $this->futil->removeFile ( $this->project->name . DS . '.locked' ) )
		{
			$this->view->setSuccess ( 'This project is now unlocked.' ) ;
			$this->project->refresh () ;
		} else {
			$this->view->setError ( 'This project has not been unlocked. Check out files authorizations.' ) ;
			return;
		}
	}
	
}
?>