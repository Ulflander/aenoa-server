<?php

class ZipProject extends Task
{
	function getOptions ()
	{
		$options = array () ;
		
		$opt = new Field () ;
		$opt->label = 'Zip filename' ;
		$opt->name = 'name' ;
		$opt->type = 'input' ;
		$opt->urlize = true ;
		$opt->required = true ;
		$opt->description = 'The name of your brand new project.' ;
		
		$options[] = $opt ;
		
		return $options ;
	}
	
	function onSetParams ( $options = array () )
	{
		if ( $this->params['name'] == '' )
		{
			$this->view->setError ( 'Use only A-Z, a-z, 0-9, _ and - chars to name your project.' ) ;
			return false ;
		}
		return true ;
	}
	

	function process ()
	{
		$this->view->render () ;
		
		$this->zip () ;
	}
	
	private function zip ()
	{
		$file = $this->project->path . DS . $this->params['name'] . '.zip' ; 
		
		$f = new File($file,false);
		if ( $f->exists () )
		{
			$f->delete () ;
		}
		
		
		$this->futil->setRoot($this->project->path);
		
		$archive = new PclZip($file);
		$archive->create($this->futil->getTree(),$this->params['name'],ROOT) ;
		
		
		
		
		return true;
	}
	
	
}

?>