<?php

class PHPI18n extends Task
{
	
	private $CMD = '/Applications/MAMP/Library/bin/' ;
	
	function getOptions ()
	{
		$this->requireMemory ( 256 ) ;
		
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'Languages' ;
		$opt->name = 'languages' ;
		$opt->type = 'input' ;
		$opt->required = true ;
		$opt->description = 'Languages, separated by a comma' ;
		$opt->value = 'en_US' ;
		
		$options[] = $opt ;
		
		return $options ;
		
	}
	
	
	function onSetParams ( $options = array () )
	{
		$params = $this->broker->sanitizer->POST ;
		
		if ( $this->hasParam ('languages') == true )
		{
			$languages = explode(',',$this->params['languages']) ;
			foreach ( $languages as $lang )
			{
				$l = trim($lang) ;
				if ( $l != '' && !in_array($l, $this->languages) )
				{
					$this->languages[] = $l ;
				}
			}
			if ( empty( $this->languages) )
			{
				return false ;
			}
		}
		
		return true ;
	}
	
	
	private $languages = array('en_US') ;
	
	function process ()
	{
		$this->view->render () ;
		
		foreach ( $this->languages as $l )
		{
			$this->_process ( $l );
		}
	}
	
	function _process ( $baseLang )
	{
		$this->view->setStatus( 'Creating I18n file for language: ' . $baseLang ) ;
		
		$baseDir = 'LC_MESSAGES' ;
		
		$localeDir = $this->project->path . 'app' . DS . 'locale' . DS ;  
		
		$cmd = $this->CMD ; ;
		
		if ( $this->futil->dirExists( $localeDir ) == false )
		{
			$this->view->setError ( 'No PHP gettext locale directory in your project.') ;
			return ;
		}
	
		if ( $this->futil->dirExists( $localeDir . DS . $baseLang ) == false && !$this->futil->createDir($localeDir, $baseLang) )
		{
			$this->view->setError ( 'Failed to create base lang folder. Please create manually this file and relaunch task : ' . $localeDir . DS . $baseLang ) ;
			return ;
		}
		
		$localeDir = $localeDir.$baseLang . DS ;
		
		if ( $this->futil->dirExists( $localeDir . $baseDir ) == false && !$this->futil->createDir($localeDir, $baseDir) )
		{
			$this->view->setError ( 'Failed to create base lang folder. Please create manually this file and relaunch task : ' . $localeDir . DS . $baseDir ) ;
			return ;
		}
		
		$localeDir = $localeDir.$baseDir . DS;
		$file = 'default.po' ;
		
		$f = new File ( $localeDir . $file , true ) ;
		
		if ( $f->isEmpty() )
		{
		
			$this->view->setSuccess ( 'I18n file created.') ;
			$join = false ;
		} else {
			$this->view->setStatus ( 'I18n file found.') ;
			$join = true ;
		}
		
		$cmd .= 'xgettext -j --from-code UTF-8';
		
		$cmd .= ' --package-name='.urlize($this->project->name).' --package-version=  --msgid-bugs-address=dev@aenoa-systems.com' ;
			
		$f->close () ;
		
		
		
		$cmdServer = $cmd . ' -L PHP -p ' . $localeDir . ' -o ' . $file ;
		$extensions = array('php','html','thtml','js','xml','') ;
		
		$tree = $this->futil->getFolderTree(ROOT . 'aenoa-server' . DS, false);
		
		foreach ( $tree as $t )
		{
			$t_extensions = array () ;
			$files = $this->futil->getFilesList($t);
			foreach( $files as $file )
			{
				if ( ake('extension', $file) && in_array( $file['extension'] , $extensions ) && !in_array ($file['extension'], $t_extensions) )
				{
					$t_extensions[] = $file['extension'] ;
					$cmdServer .= ' ' . $t.DS .'*.' . $file['extension'] ;
				}
			}
			
		}
		
		$tree = $this->futil->getFolderTree(ROOT .$this->project->name . DS, false);
		
		$cmdServer .= ' ' . ROOT . $this->project->name . DS .'*.php' ;
		
		foreach ( $tree as $t )
		{
			$t_extensions = array () ;
			$files = $this->futil->getFilesList($t);
			foreach( $files as $file )
			{
				if ( ake('extension', $file)&& in_array( $file['extension'] , $extensions ) && !in_array ($file['extension'], $t_extensions) )
				{
					$t_extensions[] = $file['extension'] ;
					$cmdServer .= ' ' . $t.DS .'*.' . $file['extension'] ;
				}
			}
		}
		
		$this->view->setSuccess ( 'Here is the system command: ' . $cmdServer ) ;
		
		$ret = exec($cmdServer) ;
		
		if ( $ret == 0 )
		{
			$this->view->setSuccess ( 'I18n terms file parsing in PHP project done.') ;
		} else {
			$this->view->setError ( 'I18n terms file parsing in PHP project NOT done.') ;
		}
		
	}
	
	
}


?>