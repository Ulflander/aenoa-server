<?php


class EditTask extends Task {
    
	public $requireProject = false ;
	
	private $editTaskName ;
	
	
	public function getOptions ()
	{
		$options = array () ;
		
		if ( !is_null($this->project) && $this->devKit->isTask ( $this->project->name , false ) == true ) 
		{
			$this->editTaskName =  $this->devKit->getTrueTaskName ( $this->project->name ) ;
		}
		
		if ( is_null ( $this->editTaskName ) )
		{
			$opt = new Field () ;
			$opt->label = 'Task name' ;
			$opt->name = 'taskname' ;
			$opt->type = 'input' ;
			$opt->required = true ;
			
		} else {
			
			$file = new File ( $this->devKit->getTaskPath ( $this->editTaskName ) . $this->editTaskName . '.php' ) ;
			
			if ( $file->exists () == false )
			{
				$this->manager->cancel ( 'File for task ' . $this->editTaskName . ' not found.' ) ;
			}
			
			$this->view->setMenuItem ( 'Return to task' , url() . $this->editTaskName , 'tasks' ) ;
			$opt = new Field () ;
			$opt->label = 'Task content' ;
			$opt->name = 'task' ;
			$opt->type = 'textfield_code' ;
			$opt->value = str_replace ( "\t" , '    ', $file->read () ) ;
			$opt->attributes['class'] = 'code' ;
			$opt->required = true ;
			
			$this->view->template->addJS ( 'dev-kit/assets/js/code-mirror.js' ) ;
			
			$file->close () ;
		}
		
		$options[] = $opt ;
		
		return $options ;
	}
	
	public function onSetParams ( $options = array () )
	{
		if ( $this->hasParam ( 'taskname' ) )
		{
			if ( $this->devKit->isTask ( $this->params['taskname'] , false ) == false )
			{
				$this->view->setError ( 'Task '.$this->params['taskname'].' is not a task' ) ;
				return false ;
			} else {
				$this->manager->redirect ( url() . 'EditTask:' . $this->params['taskname'] , 0 ) ;
			}
			
			
		}
		return true ;
	}
	
	public function process ()
	{
		if ( $this->hasParam ( 'task' ) )
		{
			$file = new File ( $this->devKit->getTaskPath ( $this->editTaskName ) . $this->editTaskName . '.php' ) ;
			
			if ( $file->exists () == false )
			{
				$this->manager->cancel ( 'File for task ' . $this->editTaskName . ' not found.' ) ;
			}
			
			if ( $file->write ( str_replace ( array("\'","\\\"",'    ') , array("'","\"","\t") , $this->params['task'] ) ) )
			{
				$this->view->setSuccess ( 'Task saved.' ) ;
			} else {
				$this->view->setError ( 'Task not saved. Check out files authorizations.' ) ;
			}
			
			$file->close () ;
			
		}
	}
}
?>