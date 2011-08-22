<?php

class ZipProjectAsApp extends Task
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
		$root = ROOT . $this->project->name . DS ;
		
		$appPath = 'app' ;
		
		$assetsPath = 'assets' ;
		
		
		$tree = array ( $this->project->path.'index.php') ;
		
		$this->futil->CVSAsHidden = true ;
		
		$tree = $this->futil->getTree($appPath, false, $tree );
		
		
		$tree = $this->futil->getTree($assetsPath, false,$tree );
		
		$optionals = array ( 'version.php' ); 
		
		foreach( $optionals as $file )
		{
			if ( $this->futil->fileExists($file) )
			{
				$tree[] = $this->project->path.$file ;
			}
		}
		
		$res = $archive->create($tree ,'',setTrailingDS(dirname(ROOT)).'aenoa-desk'.DS) !== 0 ;
		return $res ;
		
	}
	
}

?>