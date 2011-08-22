<?php

class Create extends Task {
	
	
	function getOptions ()
	{
		$this->view->template->title = 'Create a project | Step 1' ;
		
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'Project name' ;
		$opt->name = 'name' ;
		$opt->type = 'input' ;
		$opt->urlize = true ;
		$opt->required = true ;
		$opt->description = 'The name of your brand new project.' ;
		
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Project type' ;
		$opt->type = 'select' ;
		$opt->name = 'type' ;
		$opt->values = array (
			'&_empty' => 'Empty project' ,
			'&_aenoa_plug' => 'Aenoa SDK Plugin' ,
			'&_aenoa' => 'Aenoa Project' ,
			'&_copy' => 'By copying another project',
		) ;
		
		$models = $this->futil->getFilesList ( DK_DEV_KIT.'models' ) ;
		
		foreach ( $models as $file )
		{
			if ( $file['type'] != 'dir' )
			{
				$opt->values[$file['name']] = 'From model ' . $file['name'] ;
			}
		}
		
		$opt->required = true ;
		$opt->description = 'Be free' ;
		
		$options[] = $opt ;
		
		return $options ;
	}
	
	function onSetParams ( $options = array () )
	{
		if ( $this->params['name'] == '' )
		{
			$this->view->setError ( 'Use only A-Z, a-z, 0-9, _ and - chars to name your project.' ) ;
			return false ;
		} else if ($this->devKit->checkProjectName ( $this->params['name'] ) == false )
		{
			$this->view->setError ( 'Dev-kit does not authorize this name: ' .$this->params['name']. '. This may be because this name is the name of a task of a core dev-kit directory.' ) ;
			return false ;
		} else {
			$p = new DevKitProject ( $this->params['name'] ) ;
			if ( $p->valid == true )
			{
				$this->view->setError ( 'The project named '.$p->name.' exists yet. Please choose another project name.' ) ;
				return false ;
			}
		}
		
		$p = new DevKitProject ( DevKit::TRASH_DIR . DS . $this->params['name'] ) ;
		
		if ( $p->valid == true)
		{
			$this->view->setStatus ( 'A project named '. $this->params['name'].' exists in trash.' ) ;
		}
		
		return true ;
	}
	
	function process ()
	{
		$this->view->template->title = 'Create a project | Step 2' ;
		
		if ( $this->futil->createDir ( '' , $this->params['name'] ) )
		{
			$this->project = new DevKitProject ( $this->params['name']) ;
			
			if ( $this->project->valid == true )
			{
				$this->view->setProject ( $this->project , $this->taskName ) ;
				
				$this->view->setSuccess ( 'Directory '. $this->params['name'].' has been created.' ) ;
			} else {
				$this->view->setError ( 'Directory '. $this->params['name'].' has been created but is not valid.' ) ;
			}
		} else {
			$this->manager->cancel ('An error occured during files creation. Check file authorizations.') ;
		}
		
		$this->view->render () ;
		
		switch ( $this->params['type'] )
		{
			case '&_aenoa_plug':
				$this->view->setSuccess ( 'Project creation done. Redirecting to Aenoa SDK Plugin Deployment.') ;
				$this->view->redirect ( url() . 'AenoaPluginDeployment:' . $this->params['name'] ) ;
				break;
			case '&_empty':
				$this->view->setSuccess ( 'Project creation done. Redirecting to project management.') ;
				$this->view->redirect ( url() . 'ManageProject:' . $this->params['name'] ) ;
				break;
			case '&_aenoa':
				$this->view->setSuccess ( 'Project creation done. Redirecting to Aenoa Deployment.') ;
				$this->view->redirect ( url() . 'AenoaDeployment:' . $this->params['name'] ) ;
				break;
			case '&_copy':
				$this->view->setSuccess ( 'Project creation done. Redirecting to project copy task.') ;
				$this->view->redirect ( url() . 'CopyProjectTo:' . $this->params['name'] ) ;
				break;
			default:
				if ( $this->futil->fileExists (DK_DEV_KIT.'models'.DS.$this->params['type'] ) )
				{
					$fileInfo = $this->futil->getFileInfo ( DK_DEV_KIT.'models'.DS.$this->params['type'] ) ;
					$result = false ;
					switch ( $fileInfo['extension'] )
					{
						case 'zip':
							$this->view->setProgressBar ( 'Starting uncompress ' . $this->params['type'] . ' ZIP archive...' , 'unzip_progress' , 0 ) ;
							if ( $this->unzip ( $fileInfo['path'] , ROOT . $this->params['name'] . DS ) )
							{
								$this->view->setSuccess ( 'ZIP file successfully uncompressed.') ;
								$result = true ;
							}
							break;
					}
					
					$this->checkSpecialCases () ;
					
					if( $result )
					{
			
						$this->view->setSuccess ( 'Project creation done. Redirecting to project.') ;
						$this->view->redirect ( url() . $this->params['name'] ) ;
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
		// Case unzip wordpress: wordpress zip creates a dir 'wordpress' in our brand new project.
		// Let's make disappear it
		if ( $this->futil->dirExists ( ROOT . $this->params['name'] . DS . 'wordpress' ) )
		{
			$this->view->setStatus ( 'Setuping your Wordpress project ... ') ;
			
			if ( $this->futil->rename ( ROOT . $this->params['name'] , ROOT . '.' . $this->params['name']) &&
				$this->futil->moveDir ( ROOT . '.' . $this->params['name'] . DS . 'wordpress' , ROOT . $this->params['name'] ) &&
				$this->futil->removeDir ( ROOT . '.' . $this->params['name'] ) )
				{
					$this->view->setSuccess ( 'Wordpress project has been setuped.') ;
				} else {
					$this->view->setError ( 'An error occured during Wordpress project setup. Please check project manually.') ;
					return false ;
				}
		}
		
		return true ;
	}
	
	
	private function unzip ( $file , $to )
	{
		$needed_dirs = array () ;
		
		$archive = new PclZip($file);
		
		$archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING) ;
		
		// Is the archive valid?
		if ( false == $archive_files )
		{
			return false ;
		}
		
		if ( 0 == count($archive_files) )
		{
			return false ;
		}
		
		// Determine any children directories needed (From within the archive)
		foreach ( $archive_files as $file ) {
			if ( '__MACOSX/' === substr($file['filename'], 0, 9) ) // Skip the OS X-created __MACOSX directory
				continue;
	
			$needed_dirs[] = $to . unsetTrailingSlash( $file['folder'] ? $file['filename'] : dirname($file['filename']) );
		}
		$needed_dirs = array_unique($needed_dirs);
		foreach ( $needed_dirs as $dir ) {
			// Check the parent folders of the folders all exist within the creation array.
			if ( unsetTrailingSlash($to) == $dir ) // Skip over the working directory, We know this exists (or will exist)
				continue;
			if ( strpos($dir, $to) === false ) // If the directory is not within the working directory, Skip it
				continue;
	
			$parent_folder = dirname($dir);
			
			while ( !empty($parent_folder) && unsetTrailingSlash($to) != $parent_folder && !in_array($parent_folder, $needed_dirs) ) {
				$needed_dirs[] = $parent_folder;
				$parent_folder = dirname($parent_folder);
			}
		}
		asort($needed_dirs);
	
		// Create those directories if need be:
		foreach ( $needed_dirs as $_dir ) {
			if ( ! $this->futil->createDir ( dirname($_dir) , basename ( $_dir ) ) ) // Only check to see if the dir exists upon creation failure. Less I/O this way.
				return false;
		}
		unset($needed_dirs);
		
		$total = count ( $archive_files ) ;
		$currentPercent = 0 ;
		$current = 0 ;
	
		// Extract the files from the zip
		foreach ( $archive_files as $file ) {
			if ( $file['folder'] || '__MACOSX/' === substr($file['filename'], 0, 9) ) // Don't extract the OS X-created __MACOSX directory files
			{
				$total -- ;
				continue;
			}
			
			$f = new File ( $to . $file['filename'] , true ) ; 
			$f->write ( $file['content'] ) ;
			$f->close () ;
			
			$current ++ ;
			
			$currentPercent = ceil ( $current * 100 / $total ) ;
			
			$this->view->updateProgressBar ( 'unzip_progress' , $currentPercent , 'Unzipping: ' . $file['filename'] ) ;
		}
		
		$this->view->updateProgressBar ( 'unzip_progress' , 100 , 'Unzipping done.') ;
		
		return true;
	}
}

?>