<?php


class RenameProject extends Task {
    
	var $requireValidProject = true ;
	
	function getOptions ()
	{
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'Project old name: ' . $this->project->name ;
		$opt->type = 'label' ;
				
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Project new name' ;
		$opt->name = 'name' ;
		$opt->type = 'input' ;
		$opt->required = true ;
		$opt->attributes['value'] = $this->project->name ;
		$opt->urlize = true ;
		
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Rename backups' ;
		$opt->name = 'rename_backups' ;
		$opt->type = 'checkbox' ;
		$opt->attributes['value'] = 'true' ;
		
		$options[] = $opt ;
		
		return $options ;
	}
	
	function onSetParams ( $options = array () )
	{
		if ( $this->params['name'] == '' )
		{
			$this->view->setError ( 'Use only A-Z, a-z, 0-9, _ and - chars to name your project.' ) ;
			return false ;
		} else if ( $this->params['name'] == $this->project->name )
		{
			$this->view->setError ( 'You can\'t rename a project with the same name.' ) ;
			return false ;
		} else {
			$p = new DevKitProject ( $this->params['name'] ) ;
			if ( $p->valid == true )
			{
				$this->view->setError ( 'The project named '.$p->name.' exists yet. Please choose another project name.' ) ;
				return false ;
			}
		}
		
		return true ;
	}
	
	function process ()
	{
		if ( $this->futil->rename ( $this->project->path , ROOT . $this->params['name'] ) )
		{
			$oldProject = $this->project ;
			
			$this->project = new DevKitProject ( $this->params['name']) ;
			
			$this->view->removeProjectMenuItem () ;
			
			$this->view->setProject ( $this->project , $this->taskName ) ;
			
			$this->view->setSuccess ( 'Project has been renamed.' ) ;
			
			if ( $this->hasParam ( 'rename_backups' ) && $this->params['rename_backups'] == 'true' ) 
			{
				$rootFiles = $this->futil->getFilesList ( DevKit::BACKUP_DIR ) ;
				$error = false ;
				$count = 0 ;
				
				if ( $rootFiles )
				{
					foreach ( $rootFiles as $file )
					{
						if ( !(empty ( $file ) ) && $file['type'] == 'dir' )
						{
							$arr = explode ( '_' , $file['name'] ) ;
							$add = '' ;
							
							while ( !empty ( $arr ) && $arr[0] == '' )
							{
								$add .= '_' ;
								array_shift( $arr ) ;
							}
							
							if ( empty ( $arr ) )
							{
								continue;
							} else {
								$arr[0] = $add . $arr[0] ;
							}
							
							if ( $arr[0] == $oldProject->name )
							{
								array_shift($arr) ;
								$newname = $this->params['name'] . '_'. implode ( '_' , $arr ) ;
								
								if ( $this->futil->rename ( DevKit::BACKUP_DIR .DS. $file['name'] , DevKit::BACKUP_DIR .DS. $newname ) == false )
								{
									$error = true ;
								}
								$count ++ ;
							}
						}
					}
				}
				
				if ( $count > 0 )
				{
					if ( $error == false )
					{
						$this->view->setSuccess ( 'Backups have been renamed.' ) ;
					} else {
						$this->view->setError ( 'There has been a probleme renaming backups.' ) ;
					}
				}
			}
			
		} else {
			$this->view->setError ( 'An unknown error occured. Please try again.' ) ;
		}
	}
}
?>