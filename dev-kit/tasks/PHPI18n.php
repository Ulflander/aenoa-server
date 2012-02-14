<?php

class PHPI18n extends Task
{
	
	function getOptions ()
	{
		$this->requireMemory ( 256 ) ;
		
		$options = array () ;

		$opt = new Field () ;
		$opt->value = 'This task will create .po gettext files by extracting locales from both Aenoa Server and the application, using the xgettext utility (xgettext must be available on system).' ;
		$opt->type = 'label' ;

		$options[] = $opt ;

		$opt = new Field () ;
		$opt->label = 'Folder of xgettext utility' ;
		$opt->name = 'xgettext_root' ;
		$opt->type = 'input' ;
		$opt->required = true ;
		$opt->description = '' ;
		$opt->value = '/Applications/MAMP/Library/bin' ;

		$options[] = $opt ;
		
		$opt = new Field () ;
		$opt->label = 'Languages' ;
		$opt->name = 'languages' ;
		$opt->type = 'input' ;
		$opt->required = true ;
		$opt->description = 'Languages, separated by a comma' ;


		$v = array () ;
		$dirs = $this->futil->getFilesList(AE_APP . 'locale', false);
		foreach ( $dirs as $dir )
		{
			if ( $dir['type'] === 'dir' )
			{
				$v[] = $dir['name'] ;
			}
		}

		$opt->value = implode(',',$v) ;
		
		$options[] = $opt ;
		
		return $options ;
		
	}
	
	
	function onSetParams ( $options = array () )
	{
		$params = App::$sanitizer->POST ;
		
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
		
		$localeDir = ROOT. 'app' . DS . 'locale' . DS ;  
		
		$cmd = '' ;
		
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

		if ( $this->params['xgettext_root'] != '' )
		{
			$cmd .= setTrailingDS($this->params['xgettext_root']) ;
		}
		
		$cmd .= 'xgettext -j --from-code UTF-8 --debug';
		
		$cmd .= ' --package-name='.urlize($this->project->name).' --package-version=  --msgid-bugs-address=dev@aenoa-systems.com' ;
			
		$f->close () ;
		
		
		
		$cmdServer = $cmd . ' -L PHP -p ' . $localeDir . ' -o ' . $file ;
		$extensions = array('php','html','thtml','js','xml','') ;
		
		$tree = $this->futil->getFolderTree(AE_SERVER, false);
		/*
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
			
		}*/
		
		$tree = $this->futil->getFolderTree(ROOT , false);
		
		$cmdServer .= ' ' . ROOT .'*.php' ;
		
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
		
		system($cmdServer, $ret) ;
		
		if ( $ret == 0 )
		{
			$this->view->setSuccess ( 'I18n terms file parsing in PHP project done.') ;
		} else {
			$this->view->setError ($ret) ;
		}
		
	}
	
	
}


?>