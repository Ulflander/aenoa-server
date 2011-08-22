<?php

class ZipProjectAsAenoa extends Task
{

	function process ()
	{
		if ( $this->zip () )
		{
			$this->view->setSuccess ( 'Zip file has been created.' ) ;
			
		} else {
			
			$this->view->setWarning ( 'An error occured during Zip creation.' ) ;
			$this->manager->cancel ( 'Following tasks cancelled.' , null, false) ;
		}
		$this->view->render () ;
		
	}
	
	
	
	private function zip ()
	{
		$file = $this->project->path . DS . $this->project->getPackageNameAndVersion() . '.zip' ; 
		
		$f = new File($file,false);
		if ( $f->exists () )
		{
			$f->delete () ;
		}
		
		
		$this->futil->setRoot($this->project->path);
		
		$archive = new PclZip($file);
		
		$futil = new FSUtil(ROOT);
		$res = $archive->create($this->futil->getTree(),'' ,setTrailingDS(dirname(ROOT)).'aenoa-desk'.DS) !== 0 ;
		return $res ;
		
	}
	
}

?>