<?php


class TrashProjects extends Task {
	
	private $projects ;
	
	function getOptions ()
	{
		global $broker ;
		$params = $broker->sanitizer->POST ;
		
		$this->projects = array () ;
		$options = array () ;
		
		foreach ( $params as $k => $v )
		{
			if ( $this->futil->dirExists ( ROOT.$k ) )
			{
				$this->projects[] = $k ;
				$opt = new Field () ;
				$opt->type='hidden' ;
				$opt->name='project:'.$k;
				$opt->value = $k ;
				$options[] = $opt ;
			}
		}
		
		$opt = new Field () ;
		$opt->setupConfirm ( 'Confirm send projects ' . implode ( ', ' , $this->projects ) . ' to trash' ) ;
		$options[] = $opt ;
		
		return $options ;
	}
	
	function process ()
	{
		if ( $this->futil->dirExists ( DevKit::TRASH_DIR ) == false )
		{
			$this->futil->createDir ( '' , DevKit::TRASH_DIR ) ;
		}
		
		$this->view->setMenuItem ( 'Go to trash' , url() . 'ExploreTrash' , 'trash-full' ) ;
		
		foreach ( $this->params as $k => $v )
		{
			if ( strstr( $k , 'project:' ) !== false )
			{
				
				$trashname = $this->devKit->getTrashNewName ( $v ) ;
		
				if ( $this->futil->moveDir ( ROOT.$v , DevKit::TRASH_DIR . DS . $trashname  ) )
				{
					$this->view->setSuccess ( 'Project ' . $v . ' has been sended to trash.' , false ) ;
				} else {
					$this->view->setError ( 'An error occured during files deletion. Check out authorizations.' ) ;
				}
			}
		}
	}
}
?>