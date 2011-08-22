<?php


class CreateTask extends Task {
    
	public $requireProject = false ;
	
	
	public function getOptions ()
	{
		$options = array () ;
		

		$opt = new Field () ;
		$opt->fieldset = 'Task basics' ;
		$opt->label = 'Task name' ;
		$opt->name = 'taskName' ;
		$opt->type = 'input' ;
		$opt->required = true ;
		
		$options[] = $opt ;
		
		$dirs = array (
			'.' => 'Root task' 
		) ;
		
		$taskdirs = $this->futil->getDirsList ( DK_TASKS , false ) ;
		
		foreach ( $taskdirs as $k => $v )
		{
			$dirs[$v['name']] = $v['name'] ;
		}
		
		$opt = new Field () ;
		$opt->label = 'Task folder' ;
		$opt->name = 'taskfolder' ;
		$opt->type = 'select' ;
		$opt->values = $dirs ;
		$opt->required = true ;
		
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->fieldset = 'Required project for task' ;
		$opt->label = 'Task require project';
		$opt->name = 'requireProject' ;
		$opt->type = 'radio' ;
		$opt->values = array ( 'true' => 'Yes' , 'false' => 'No' ) ;
		$opt->required = true ;
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Task require a valid project';
		$opt->name = 'requireValidProject' ;
		$opt->type = 'radio' ;
		$opt->values = array ( 'true' => 'Yes' , 'false' => 'No' ) ;
		$opt->required = true ;
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->fieldset = 'Required project types for task' ;
		$opt->type = 'label' ;
		$options[] = $opt ;
		
		
		$types = DevKitProjectType::getAll () ;
		foreach ( $types as $type => $typeName)
		{
			$opt = new Field () ;
			$opt->label = 'Accept project type: ' . $typeName;
			$opt->name = 'accept-' . $type ;
			$opt->type = 'checkbox' ;
			$options[] = $opt ;
		}
		
		return $options ;
	}
	
	public function onSetParams ( $options = array () )
	{
		if ( $this->hasParam ( 'taskName' ) )
		{
			if ( $this->devKit->isTask ( $this->params['taskName'] , false ) == true )
			{
				$this->view->setError ( 'Task '.$this->params['taskName'].' is yet a task' ) ;
				return false ;
			}
			
		}
		return true ;
	}
	
	public function process ()
	{
		if ( $this->futil->fileExists ( DK_TASKS . $this->params['taskfolder'] .DS . $this->params['taskName'] . '.php' ) == false )
		{
			$requireTypes = 'array(' ;
			foreach ( $this->params as $k => $v )
			{
				if ( substr ( $k , 0, 7 ) == 'accept-' )
				{
					$requireTypes .= 'DevKitProjectType::' . strtoupper ( str_replace ( ' ' , '_' , substr ( $k , 7 ) ) ) . ', ' ; 
				}
			}
			$requireTypes .= ')' ;
			
			
			$templ = new Template ( 'php/Task.thtml' ) ;
			if ( strlen ( $requireTypes ) > 8 )
			{
				$templ->set ( 'requireTypes', $requireTypes ) ;
			}
			$templ->setAll ( $this->params ) ;
			$taskFile = "<?php\n" .$templ->render ( false ) . "\n?>" ;
			
			$file = new File ( DK_TASKS . $this->params['taskfolder'] .DS . $this->params['taskName'].'.php' , true ) ;
			
			if ( $file->write ( $taskFile ) && $file->close () )
			{
				$this->view->setSuccess ( 'Task PHP file has been created. Go to <a href="'
						.url().'EditTask:'.$this->params['taskName'].'">EditTask</a> to edit your new task,'
						. 'or <a href="'.url().'CreateTask">create another task</a>' ) ;
			} else {
				$this->view->setError ( 'An error occured when writing PHP file.' ) ;
	
			}
		
		} else {
			$this->view->setError ( 'A Task PHP file has been found in ' . $this->params['taskfolder'] . '. PHP file deployment has been stopped.' ) ;
		}
	}
}
?>