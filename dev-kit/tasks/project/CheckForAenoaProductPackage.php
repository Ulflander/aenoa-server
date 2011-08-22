<?php


class CheckForAenoaProductPackage extends Task {
	
	var $requireProject = true ;
	
	function getOptions ()
	{
		$this->view->setStatus ( 'Please provide a valid version: 1-1-1 (only numbers, separated by an hyphen)' ) ;
		
		$this->view->setStatus ( 'Current version is <strong>'.$this->project->getCVSVersion().'</strong>' ) ;
			
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'Project new version' ;
		$opt->name = 'version' ;
		$opt->type = 'input' ;
		$opt->required = true ;
		$opt->urlize = true ;
		
		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Project changelog since last version' ;
		$opt->name = 'changelog' ;
		$opt->type = 'textfield' ;
		$opt->required = true ;
		
		$options[] = $opt ;
		
		return $options ;
	}
	
	function onSetParams ( $options = array () )
	{
		// TODO: check with a regexp for a valid version 00-00-00
		if ( $this->params['version'] == '' )
		{
			$this->view->setError ( 'Please provide a valid version.' ) ;
			return false ;
		}
		
		return true ;
	}
	
	
	function process ()
	{
		$error = false ;
		
		$this->view->render () ;
		
		/** 
		 * Check project validity
		 * 
		 */
		if ( $this->project->valid == false )
		{
			$this->view->setError ( 'Project is not valid.' ) ;
			$error = true ;
		} else {
			$this->view->setSuccess('Project is valid');
		}
		
		
		
		
		/** 
		 * SPECIAL CASE
		 * If package is Aenoa Server, we check debug mode, and we AUTO disable it
		 * 
		 */
		$debug = false ;
		if ( $this->project->name == 'aenoa-server' )
		{
			$bf = new File (AE_SERVER.'bootstrap.php',false);
			
			if($bf->exists() )
			{
				$str = $bf->read() ;
				
				preg_match_all('/(define[\s]{0,}\([\s]{0,}\'DEBUG\'[\s]{0,},[\s]{0,}true[\s]{0,}\))/', $str,$matches ) ;
				
				if(!empty($matches[0]))
				{
					$this->view->setWarning(' Aenoa Server IS IN DEBUG MODE. Bootstrap is beeing modified.' );
					
					$futil2 = new FSUtil(AE_SERVER);
					if ( $futil2->copy(AE_SERVER.'bootstrap.php',AE_SERVER.'debug-bootstrap.php')) 
					{
					
						$str = preg_replace('/(define[\s]{0,}\([\s]{0,}\'DEBUG\'[\s]{0,},[\s]{0,}true[\s]{0,}\))/','define ( \'DEBUG\' , false )',$str,1) ;
						
						if ( $bf->write($str) )
						{
							$this->view->setSuccess('Aenoa Server is no more in DEBUG mode');
							$debug = true ;
						} else {
							$this->view->setStatus('Failure on writing new bootstrap content.', 'error' );
							$futil2->removeFile(AE_SERVER.'debug-bootstrap.php') ;
							$error = true ;
						}
						$bf->close () ;
					} else {
						
						$this->view->setStatus('Failure on making a backup copy of bootstrap. Please set DEBUG mode to false manually.', 'error' );
						$error = true ;
					}
				} else {
					
					$this->view->setStatus(' Aenoa Server is not in debug mode. Bootstrap has not been modified.' );
				}
			}
		}
		
		
		/** 
		 * To be considered as aenoa package, a projects HAVE to be shared using CVS
		 * 
		 */
		$tag = $this->project->getCVSTag() ;
		if ( $this->project->isCVSEnable() == false )
		{
			$this->view->setError ( 'This project is not using CVS. CVS is required in order to package project as Aenoa Project.' ) ;
			$error = true ;
		} else if ( $tag == '' )
		{
			$this->view->setError ( 'This project is using CVS but has no Tag associated with it. Aenoa packages require a valid CVS Tag.' ) ;
			$error = true ;
		} else {
			$this->view->setSuccess('Project is using CVS, and has a valid CVS Tag');
		}
		
		
		/** 
		 * Retrieve the project old and new versions
		 * 
		 */
		$f = new PHPPrefsFile($this->project->name . DS . 'version.php', false) ;
		$versionFromTag = trim($this->params['version']) ;
		$version = trim($this->project->getCVSVersion()) ;
		
		$this->view->setStatus('New version: ' . $versionFromTag );
		$this->view->setStatus('Older version of project found in version.php: ' . $version );
		
		
		/** 
		 * Check version coherence
		 * 
		 */
		$vc = version_comp(str_replace('-','.',$versionFromTag),str_replace('-','.',$version));
		if ( $vc < 0 )
		{
			$this->view->setError ( 'THERE IS A VERSION PROBLEM: new project version ('.$versionFromTag.') is lower than project older version ('.$version.'). Please manually solve this problem.' ) ;
			$error = true ;
		} else {
			$this->view->setSuccess('Package version will be updated from ' . $version . ' to ' . $versionFromTag);
		}
		
		
		/** 
		 * Delete old version archive if exists
		 * 
		 */
		if ( $this->futil->fileExists ( $this->project->name . DS . $this->project->getPackageName() .'-'. $version .'.zip' ) == true )
		{
			$f2 = new File($this->project->name . DS . $this->project->getPackageName() .'-'. $version .'.zip', false) ;
			if($f2->delete())
			{
				$this->view->setWarning ( 'The older package file has been removed.' ) ;
			} else {
				$this->view->setError ( 'Please delete manually the file ' .$this->project->getPackageName() .'-'. $version .'.zip in the root folder of your project before packaging the new version.' ) ;
				$error = true ;
			}
		}
		
		
		/** 
		 * Update version in version.php
		 * Update changelog
		 */
		if ( $error == false )
		{
			$f->set('version', $versionFromTag);
			if ( $f->flush() )
			{
				$this->view->setSuccess('Package version has been updated from ' . $version . ' to ' . $versionFromTag);
			} else {
				$this->view->setError ( 'Package version file has NOT been updated. File access problem ?' ) ;
				$error = true ;
			}
			
			$f->close () ;
			$f = new File($this->project->name . DS . $this->project->getPackageName().'-changelog-'.$version.'.txt',true);
			$f->prepend("\n\n---------------\n".$this->project->name.' version ' . $versionFromTag . ' generated on ' . date('Y/m/d h:i',time()) . "\n\n" . $this->params['changelog']. "\n\n");
			$f->rename($this->project->name . DS . $this->project->getPackageName().'-changelog-'.$versionFromTag.'.txt');
			$f->close () ;
		}
		
		
		/** 
		 * End
		 * 
		 */
		if ( $error == true )
		{
			$this->view->setWarning ( 'Please check below: some errors have been found.' ) ;
			$this->manager->cancel ( 'Following tasks cancelled.' , null, false) ;
		} else {
			$this->view->setSuccess('This product is ready to be packaged.');
		}
	}
	
}
?>