<?php


class CleanFiles extends Task {
    
	var $requireValidProject = true ;
	
	private $patterns = array () ;
	
	private $clean = array () ;
	
	function getOptions ()
	{
		$this->requireMemory ( 256 ) ;
		
		$this->manager->shouldBackup = true ;
		
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'Files names to clean from project directory' ;
		$opt->name = 'patterns' ;
		$opt->type = 'textfield' ;
		$opt->required = true ;
		$opt->description = 'You can insert here a complete filename (e.g. ".DS_Store") or a partial filename (e.g. ".tmp_*", the * char represents the missing part of filename)' ;
		$opt->value = ".DS_Store\nThumbs.db\n.tmp_*\n.localized\n" ;
		
		$options[] = $opt ;
		
		if ( $this->hasParam ( 'patterns' ) )
		{
			$opt2 = new Field () ;
			$opt2->setupConfirm ( 'You have to confirm deletion of files, check out below the list of files that will be deleted' ) ;
			
			$options[] = $opt2 ;
			
			$opt->attributes['readonly'] = 'readonly' ;
			$opt->attributes['class'] = 'readonly' ;
			$opt->value = $this->params['patterns'] ;
		
			
		}
		
		return $options ;
		
	}
	
	
	function onSetParams ( $options = array () )
	{
		$params = App::getSanitizer()->POST ;
		
		if ( $this->hasParam ('patterns') == true && ( array_key_exists ('confirm', $params) == false || $params['confirm'] == 'cancelled' ) )
		{
			$this->__formatPatterns( $this->params['patterns'] ) ;
			
			$this->futil->applyCallback ( '_processCleaning' , $this , $this->project->name , true ) ;
			
			if ( count ( $this->clean ) == 0 )
			{
				$this->view->setWarning ( 'There is no file to clean with these patterns' ) ;
			}  else {
				$this->view->setStatus ( $this->clean , true ) ;
			}
		
			$this->manager->retrieveOptions () ;
			
		
			return false ;
		}
		
		return true ;
	}
	
	
	private function __formatPatterns ( $str )
	{
		$_p = explode ( "\n" , dec2n ( $this->params['patterns'] ) ) ;
		
		$this->patterns = array () ;
		
		foreach ( $_p as &$pattern )
		{
			$pattern = trim($pattern) ;
			
			if ( substr_count ( $pattern , '*' ) > 0 )
			{
				$pattern =  '/' . str_replace ( "\\*" , '(.*)' , preg_quote ( $pattern ) ) . '/' ;
			}
			
			if ( $pattern !== '' )
			{
				$this->patterns[] = $pattern ;
			}
		}
	}
	
	function process ()
	{
		$this->__formatPatterns( $this->params['patterns'] ) ;
			
		$this->view->setStatus ( array_merge ( array ( 'Here are the patterns:' , '&nbsp;' ) , $this->patterns ) ) ;
		
		$this->futil->applyCallback ( '_processCleaning' , $this , $this->project->name , true ) ;
		
		if ( count ( $this->clean  ) == 0 )
		{
			$this->manager->cancel ( 'There is no file to clean' ) ;
		}
		
		$this->view->setStatus ( $this->clean ) ;
		
		$this->view->setStatus ( count ( $this->clean ) . ' file(s) will be deleted' ) ;
		
		$errors = array () ;
		
		foreach ( $this->clean as $file )
		{
			if ( $this->futil->removeFile ( $file ) )
			{
				$this->view->setSuccess ( $file . ' deleted' , true ) ;
			} else {
				$this->view->setError ( $file .' not deleted' , true ) ;
				$errors[] = $file ;
			}
		}
		
		if ( !empty ( $errors ) )
		{
			$this->view->setStatus ( $errors , true ) ;
			$this->view->setError ( 'File(s) below have not been deleted' ) ;
		} else {
			$this->view->setSuccess ( 'All files have been deleted' ) ;
		}
	}
	
	function _processCleaning ( $path )
	{
		if ( is_file ( $path ) )
		{
			$f = basename ( $path ) ;
			foreach ( $this->patterns as $pattern )
			{
				if ( substr_count ( $pattern , '*' ) > 0 )
				{
					if ( preg_match ($pattern , $f ) > 0 )
					{
						$this->clean[] = $path ;
					}
				} else if ( $pattern == $f )
				{
					$this->clean[] = $path ;
				}
			}
		}
	}
}










































?>