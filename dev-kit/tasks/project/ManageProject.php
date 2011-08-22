<?php


class ManageProject extends Task {
	
	var $requireProject = true ;
	
	
	function process ()
	{
		if ( $this->project->valid == false )
		{
			$this->view->setError ( 'Project is not valid.' ) ;
			return;
		}
		
		$template = new Template ( 'html/ManageProject.thtml' ) ;
		$template->set ( 'project' , $this->project ) ;
		
		$plugins = $this->devKit->getFilteredPlugins ( true , $this->project->type ) ;
		$template->set ( 'plugins' , $plugins ) ;
		
		$projectData = array () ;
		$projectData['Type'] = $this->project->type ;
	
		if ( $this->project->isCVSEnable () == true )
		{
			$projectData['CVS enable'] = 'yes' ;
			$projectData['CVS tag'] = $this->project->getCVSTag () ;
		}
		
		
		
		$template->set ( 'projectData' , $projectData ) ;
		
		$this->view->setTitle ( 'ManageProject: ' . $this->project->name ) ;
		$this->view->avoidMessages = true ;
		$this->view->appendContent ( $template->render ( false ) ) ;
		
		$this->view->render () ;
		$this->view->hideIndicator () ;
		
		$this->view->appendContent ( $this->futil->getDirSize ( $this->project->name ) . ' Mo' , 'sidebar_size' ) ;
		
		$this->view->appendContent ( $this->futil->getFilesCount ( $this->project->name , true) . ' files' , 'sidebar_files' ) ;
	}
	
}
?>