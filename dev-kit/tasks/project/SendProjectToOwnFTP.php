<?php

class SendProjectToOwnFTP extends Task
{

	function process ()
	{
		$this->view->render () ;
		
		$file = $this->project->getPackageNameAndVersion() . '.zip' ;
		$file2 = $this->project->getPackageName() . '-changelog-' . $this->project->getCVSVersion() . '.txt' ;
		$ftp = null ;

		// Check packages to update for Aenoa Package
		if ( is_file( ROOT . $this->project->name . DS . 'app' . DS . 'libs' . DS .'ApplicationUpdate.php' ) )
		{
			require_once( ROOT . $this->project->name . DS . 'app' . DS . 'libs' . DS .'ApplicationUpdate.php' ) ;
			
			if ( class_exists('ApplicationUpdate') )
			{
				$ftp = new ApplicationUpdate () ;
			}
		} else {
			$this->view->setError ( 'Dedicated update FTP is not available for this project' ) ;
			return;
		}
		
		if ( $this->futil->fileExists($this->project->name.DS.$file) == false || $this->futil->fileExists($this->project->name.DS.$file2) == false )
		{
			$this->view->setError ( 'The current package to send should be ' . $file . ' but this zip file does not exists.' ) ;
			return;
		} else {
			$this->view->setSuccess ( 'File wil be sended to project dedicated update FTP: ' . $file ) ;
		}
		
		
		if ( is_null($ftp) || !$ftp->isUsable () )
		{
			$this->view->setError ( 'Dedicated FTP is not reachable.' ) ;
			return;
		}
		
		$this->view->setSuccess ( 'Connected to ftp.' ) ;
		
		$this->view->setProgressBar('Sending ' . $file, 'ftp_send', 0 ) ;
		
		$this->view->updateProgressBar('ftp_send',-1,'') ;
		
		if ( $ftp->put ( $file, ROOT.$this->project->name.DS.$file ) && $ftp->put ( $file2, ROOT.$this->project->name.DS.$file2 ) )
		{
			$this->view->updateProgressBar('ftp_send',100,'File sended') ;
			
			$this->view->setSuccess ( 'File sended.' ) ;
			
		} else {
			$this->view->updateProgressBar('ftp_send',100,'File NOT sended') ;
			
			$this->view->setError ( 'File not sended.' ) ;
		}
		
		
	}
	
	
}

?>