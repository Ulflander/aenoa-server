<?php


class RunTask extends Task {
	
	
	
	function init ()
	{
		
		$this->view->setMenuItem ( 'TaskCenter' , url().'TaskCenter' , 'tasks' ) ;
		
	}
	
	function getOptions ()
	{
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'Task name' ;
		$opt->name = 'taskname' ;
		$opt->type = 'input' ;	
		$opt->description = 'You can run many tasks separating task names by a slash.' ;
		$opt->required = true ;
		
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Project' ;
		$opt->name = 'project' ;
		$opt->type = 'input' ;	
		$opt->required = false ;
		
		if ( !is_null ( $this->project ) && $this->project->valid )
		{
			$opt->attributes['value'] = $this->project->name ;
		}
		
		$options[] = $opt ;
		
		return $options ;
	}
	
	function onSetParams ( $options = array () )
	{
		if ( $this->hasParam ( 'project' ) )
		{
			$p = new DevKitProject ( $this->params['project'] ) ;
			if ( $p->valid == false )
			{
				$this->view->setError ( 'Project does not exists.' ) ;
				return false ;
			}
		}
		return true ;
	}
	
	function process ()
	{
			
		if ( $this->hasParam ( 'project' ) )
		{
			$task = explode ( '/' ,$this->params['taskname'] );
			$task = implode ( ':' . $this->params['project'] . '/' , $task ) ;
			$task .= ':' . $this->params['project'] ;
		} else {
			$task = $this->params['taskname'] ;
		}
		
		
		
		$this->view->setStatus ( 'Going to next task in 2 seconds...' ) ;
		$this->manager->redirect ( url() . $task , 1000 ) ;
		$this->view->avoidEndMessage = true ;
	}
}
?>