<?php

class RevertAssetsCompressor extends Task {



	function beforeEnd() {
		
	}

	function getOptions ()
	{
		$this->manager->shouldBackup = true ;

		if ( !$this->futil->dirExists(ROOT.'assets') || !$this->futil->dirExists(ROOT.'static') )
		{
			$this->manager->cancel('There is no "assets" nor "static" folder at root of application.');
		}

		$this->toCompress = array () ;
		$to = array () ;

		$folders = array ( 'assets', 'static' ) ;
		
		foreach ( $folders as $folder )
		{

			$files = $this->futil->getTree(ROOT.$folder);

			foreach($files as $file)
			{
				$file = $this->futil->getFileInfo($file);
				if ( $file['type'] == 'file' && ($file['extension'] == 'css' || $file['extension'] == 'js' ) )
				{
					$to[] = $file ;
				}
			}
		}

		foreach ( $to as $file )
		{
			if ( strpos($file['name'], '.uncompressed.') )
			{
				$this->toCompress[] = $file ;
			}
		}

		$files = array();
		foreach( $this->toCompress as $file )
		{
			array_push($files, str_replace(ROOT,'',$file['path'])) ;
		}

		if ( count($this->toCompress) == 0 )
		{
			$this->manager->cancel('There is no file to uncompress.');
		}
		$this->view->setStatus('Files to be uncompressed: <br /><br />' . implode('<br />' , $files));

		$opt = new Field () ;
		$opt->type = 'label';
		$opt->value = 'This task will remove compressed CSS and JavaScript file contained in the "assets" folder at the root of ' . Config::get(App::APP_NAME) . ' application, and will recover uncompressed copies of files. ' ;


		$opt2 = new Field () ;
		$opt2->setupConfirm ( 'Confirm you want to uncompress assets' ) ;



		return array ( $opt , $opt2) ;
	}

	function process ()
	{
		foreach( $this->toCompress as $file )
		{
			$filename = str_replace('.uncompressed.' , '.', $file['name'] ) ;

			$this->futil->copy($file['path'], dirname($file['path']) . DS . $filename ) ;

			$this->view->setStatus('Uncompressing '.$file['name'] . ' to ' . $filename ) ;

			$this->futil->removeFile($file['path']);
		}

		$this->view->setSuccess('Files uncompressed') ;
	}
}

?>
