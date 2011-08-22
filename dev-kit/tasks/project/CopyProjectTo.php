<?php


class CopyProjectTo extends Task {
	
	
	public $requireProject = true ;
	
	private $aenoaBackupPath ;

	
	function getOptions ()
	{
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'Project will be copied to ' . $this->project->name ;
		$opt->type = 'label' ;
		
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Project to copy' ;
		$opt->type = 'select' ;
		$opt->name = 'project' ;
		$opt->values = array () ;
		
		foreach ( $this->devKit->getAllProjects () as $name => $project )
		{
			$opt->values[$name] = $name ;
		}
		$opt->required = true ;
		
		$options[] = $opt ;
		
		return $options ;
	}
	
	
	private $total ;
	
	private $current = 0 ;
	
	
    function process () 
	{
		$this->copyPath = ROOT . $this->params['project'] ;
		
		if ( $this->project->valid == false )
		{
			$this->manage->cancel ( 'Copy destination must exists.' ); 
		}
		
		$this->view->render () ;
		
		$this->view->setProgressBar ( 'Copy of ' . $this->params['project'] . ' to ' . $this->project->name . '...' , 'progress' ) ;
		
		$this->total = $this->futil->getFilesCount ($this->copyPath , true) ;
		
		$callback = new Callback ( 'updateProgress' , $this );
		
		if ( $this->futil->copy ( $this->copyPath , ROOT. $this->project->name , $callback ) == false )
		{
			$this->view->setError ( 'Copy of ' . $this->project->name . ' to ' . $this->params['project'] . ' failed.' ) ;
		} else {
			$this->view->setSuccess ( 'Copy of ' . $this->project->name .' to ' . $this->params['project'] . ' done. Redirecting to project management.' ) ;
			$this->view->redirect( url() . 'ManageProject:' . $this->project->name ) ;
		}
	}
	
	function updateProgress ( $fileCount , $lastFileCopied )
	{
		$this->current += $fileCount ;
		
		$this->view->updateProgressBar ( 'progress' , ceil ( $this->current * 100 / $this->total ) , 'Copying: ' . $lastFileCopied ) ;
	}
}
?>




























