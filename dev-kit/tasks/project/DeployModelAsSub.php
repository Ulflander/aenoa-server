<?php

class DeployModelAsSub extends Task
{
	
	var $requireValidProject = true ;
	
	function getOptions ()
	{
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'You will deploy a model in project ' . $this->project->name ;
		$opt->type = 'label' ;
		
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Sub folder name' ;
		$opt->name = 'name' ;
		$opt->type = 'input' ;
		$opt->urlize = true ;
		$opt->required = true ;
		$opt->description = 'The name of the folder where to extract the model.' ;
		
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Project type' ;
		$opt->type = 'select' ;
		$opt->name = 'type' ;
		$opt->values = array () ;
		
		$models = $this->futil->getFilesList ( DK_DEV_KIT.'models' ) ;
		
		foreach ( $models as $file )
		{
			if ( $file['type'] != 'dir' )
			{
				$opt->values[$file['name']] = $file['filename'] ;
			}
		}
		
		$opt->required = true ;
		
		$options[] = $opt ;
		
		return $options ;
	}
	
	function onSetParams ( $options = array () )
	{
		if ( $this->params['name'] == '' )
		{
			$this->view->setError ( 'Use only A-Z, a-z, 0-9, _ and - chars to name your project.' ) ;
		} else if ($this->futil->dirExists ( $this->project->name . DS . $this->params['name'] )  )
		{
			$this->view->setError ( 'A folder with that name yet exists in project root folder.' ) ;
		} else {
			return true ;
		}
		
		return false ;
	}
	
	function process ()
	{
		if ( $this->futil->createDir ( ROOT.$this->project->name , $this->params['name'] ) )
		{
			$this->view->setSuccess ( 'Subfolder created.') ;
		} else {
			$this->manager->cancel ('An error occured during files creation. Check file authorizations.') ;
		}
		
		$this->view->render () ;
		
		switch ( $this->params['type'] )
		{
			default:
				if ( $this->futil->fileExists (DK_DEV_KIT.'models'.DS.$this->params['type'] ) )
				{
					$fileInfo = $this->futil->getFileInfo ( DK_DEV_KIT.'models'.DS.$this->params['type'] ) ;
					$result = false ;
					switch ( $fileInfo['extension'] )
					{
						case 'zip':
							$this->view->setProgressBar ( 'Starting uncompress ' . $this->params['type'] . ' ZIP archive...' , 'unzip_progress' , 0 ) ;
							if ( $this->unzip ( $fileInfo['path'] , ROOT .$this->project->name . DS .  $this->params['name'] . DS ) )
							{
								$this->view->setSuccess ( 'ZIP file successfully uncompressed.') ;
								$result = true ;
							}
							break;
					}
					
					$this->checkSpecialCases () ;
					
					if( $result )
					{
			
						$this->view->setSuccess ( 'Sub project deployed. Redirecting to sub project.') ;
						$this->view->redirect ( url() . $this->project->name . '/' . $this->params['name'] ) ;
					} else {
						
						$this->view->setError ( 'Model has not been properly uncompressed.') ;
					}
				} else {
					$this->view->setError ( 'Project creation done but model does not exists. Redirecting to project management.') ;
				}
				
			break;
			
		}
	}
	
	private function checkSpecialCases ()
	{
		
		$path = ROOT . $this->project->name . DS ;
		
		// Case unzip wordpress: wordpress zip creates a dir 'wordpress' in our brand new project.
		// Let's make disappear it
		if ( $this->futil->dirExists ( $path . $this->params['name'] . DS . 'wordpress' ) )
		{
			$this->view->setStatus ( 'Setuping your Wordpress project ... ') ;
			
			if ( $this->futil->rename ( $path . $this->params['name'] , $path . '.' . $this->params['name']) &&
				$this->futil->moveDir ( $path . '.' . $this->params['name'] . DS . 'wordpress' , $path . $this->params['name'] ) &&
				$this->futil->removeDir ( $path . '.' . $this->params['name'] ) )
				{
					$this->view->setSuccess ( 'Wordpress project has been setuped.') ;
				} else {
					$this->view->setError ( 'An error occured during Wordpress project setup. Please check project manually.') ;
					return false ;
				}
		}
		
		return true ;
	}
	
	
}

?>