<?php


class TrashProject extends Task {
	
	function getOptions ()
	{
		$opt = new Field () ;
		$opt->setupConfirm ( 'Confirm send project <u>' . $this->project->name . '</u> to trash' ) ;
		
		return array ( $opt ) ;
	}
	
	function process ()
	{
		if ( $this->futil->dirExists ( DevKit::TRASH_DIR ) == false )
		{
			$this->futil->createDir ( '' , DevKit::TRASH_DIR ) ;
		}
		
		$trashname = $this->devKit->getTrashNewName ( $this->project->name ) ;
		
		$this->view->setMenuItem ( 'Go to trash' , url() . 'ExploreTrash' , 'trash-full' ) ;
		
		if ( $this->futil->moveDir ( $this->project->name , DevKit::TRASH_DIR . DS . $trashname  ) )
		{
			$this->project = new DevKitProject ( $this->project->name ) ;
			
			$this->view->removeProjectMenuItem () ;
			
			$this->view->setProject ( $this->project , $this->taskName ) ;
			
			$this->view->setSuccess ( 'Project has been sended to trash.' ) ;
		} else {
			$this->view->setError ( 'An error occured during files deletion. Check out authorizations.' ) ;
		}
	}
}
?>