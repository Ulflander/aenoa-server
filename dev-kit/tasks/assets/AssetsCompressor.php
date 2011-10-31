<?php

class AssetsCompressor extends Task {



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
			if ( strpos($file['name'], '.uncompressed.') ||
					!$this->futil->fileExists(dirname($file['path']).DS.$file['filename'].'.uncompressed.'.$file['extension'] ) )
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
			$this->manager->cancel('There is no file to compress.');
		}

		$this->view->setStatus('Files to be compressed: <br /><br />' . implode('<br />' , $files));

		$opt = new Field () ;
		$opt->type = 'label';
		$opt->value = 'This task will compress CSS and JavaScript file contained in the "assets" folder at the root of ' 
			. Config::get(App::APP_NAME) . ' application, and will create uncompressed copies of files using "uncompress" suffix. '
			. 'Javascript compression is always set to safe for this task.';


		$opt2 = new Field () ;
		$opt2->setupConfirm ( 'Confirm you want to compress assets and create some uncompressed assets files' ) ;



		return array ( $opt , $opt2) ;
	}

	function process ()
	{
		foreach( $this->toCompress as $file )
		{
			$filename = '' ;
			if ( strpos($file['name'], '.uncompressed.') )
			{
				$filename = str_replace('.uncompressed.' , '.', $file['name'] ) ;
			} else {

				$this->futil->copy($file['path'], dirname($file['path']) . DS . $file['filename'] . '.uncompressed.' . $file['extension'] ) ;

				$filename = $file['name'] ;
			}

			$this->view->setStatus('Compressing '.$file['name'] . ' to ' . $filename ) ;

			$from = new File ( $file['path'] ) ;
			$f = new File ( setTrailingDS(dirname($file['path'])).$filename ) ;

			if ( $file['extension'] == 'js' )
			{
				$f->write(AeJSCompressor::safeCompressString($from->read()));
			} else {
				$f->write(AeCSSCompressor::compressString($from->read()));
			}

			$f->close () ;
		}

		$this->view->setSuccess('Files compressed') ;
	}
}

?>
