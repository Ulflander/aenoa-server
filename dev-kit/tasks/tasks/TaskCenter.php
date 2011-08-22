<?php


class TaskCenter extends Task {
	
	
    function process () 
	{
		$this->view->setTitle ( 'TaskCenter' ) ;
		
		$template = new Template ( 'html/TaskCenter.thtml' ) ;
		$template->set ( 'project' , $this->project ) ;
		
		$this->view->setMenuItem ( 'Run a task' , url() . 'RunTask' , 'tasks' ) ;
		$this->view->setMenuItem ( 'Explore tasks' , url() . 'ExploreTasks' , 'tasks' ) ;
		$this->view->setMenuItem ( 'Edit a task' , url() . 'EditTask' , 'tasks' ) ;
		$this->view->setMenuItem ( 'Manage dev-kit' , url() . 'ManageProject:dev-kit' , 'tasks' ) ;
		
		$template->set ( 'plugins' , $this->devKit->getFilteredPlugins ( false ) ) ;
		
		$this->view->avoidMessages = true ;
		$this->view->template->addCSS ( 'dev-kit/assets/css/dev-kit-task-center.css' ) ;
		$this->view->template->addCSS ( 'dev-kit/assets/css/dev-kit-project.css' ) ;
		$this->view->appendContent ( $template->render ( false ) ) ;
	}
}
?>