<?php

class GenerateDocumentation extends Task {
	
	
	function process ()
	{
		// Setup documentation root dir
		if ( !$this->futil->dirExists(ROOT.'documentations'.DS.$this->project->name) )
		{
			$this->futil->createDir ( ROOT.'documentations' , $this->project->name );
		}
		
		// Setup documentation config dir
		if ( !$this->futil->dirExists(ROOT.'documentations'.DS.$this->project->name.'-nd') )
		{
			$this->futil->createDir ( ROOT.'documentations' , $this->project->name.'-nd' );
		}
		
		$this->futil->copy( ROOT.'documentations'.DS.'natural-aenoa.css', ROOT.'documentations'.DS.$this->project->name.'-nd'.DS.'natural-aenoa.css' );
		
		$n = ucwords( str_replace('-',' ',$this->project->name) ) ;
		
		if ( $this->project->isCVSEnable() )
		{
			$n .= ' ' . str_replace('-','.',$this->project->getCVSVersion()) ;
		}
		
		$cmd = '/Developer/NaturalDocs/NaturalDocs -s natural-aenoa -at '.$n.' -r -oft -i '.$this->project->path.' -o HTML '.ROOT.'documentations'.DS.$this->project->name.'/ -p '.ROOT.'documentations'.DS.$this->project->name.'-nd/' ;
		
		$this->view->setStatus('Running command: '. $cmd);
		
		exec ($cmd, $output, $ret ) ;
		
		$this->view->setStatus ( '<ul><li>' . implode('</li><li>', $output) . '</li></ul>') ;
		
		if ( $ret == 0 )
		{
			$this->view->setSuccess ( 'Documentation generated: <a href="'.url().'/documentations/'.$this->project->name.'/">' . $this->project->name . ' documentation</a>') ;
		} else {
			$this->view->setError ( 'Documentation NOT generated: ' . $this->project->name . ' / Returned: ' . $ret ) ;
		}
		
	}
	
}

?>