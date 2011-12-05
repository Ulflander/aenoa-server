<?php

class BackupList extends Task {

	function process()
	{

		// Create backups folder if needed
		if ($this->futil->dirExists(AE_APP_BACKUP) == false) {
			$this->futil->createDir(dirname(AE_APP_BACKUP), basename(AE_APP_BACKUP));
		}

		// Get list of backups
		$folders = $this->futil->getDirsList(AE_APP_BACKUP) ;
		$backups = array () ;
		
		foreach ( $folders as $folder )
		{
			if ( $this->futil->fileExists($folder['path'] . '.backuptag') )
			{
				$date = $folder['name'] ;
				$date = substr($date,0, 4) 
						. '/' . substr($date,5, 2) 
						. '/' . substr($date,8, 2) 
						. ' at ' . substr($date,11, 2) 
						. ':' . substr($date,14, 2) ;
				
				$backups[] = array (
					'tag' => trim ( File::sread($folder['path'] . '.backuptag') ),
					'name' => $folder['name'] ,
					'date' => $date 
				) ;
			}
		}
		
		// Last, we create view

		
		if ( empty ( $backups ) )
		{
			$this->view->setWarning(_('No backup available')) ;
			return;
		}
		
		// We render Task common view
		$this->view->render () ;

		// We create the template to render BackupList custom view
		$tpl = new Template ( DK_TEMPLATES . 'html' . DS . 'BackupList.thtml') ;
		
		$tpl->set ( 'backups' , $backups ) ;

		// We append rendering of template to task view
		$this->view->appendContent($tpl->render(false)) ;

		// And we end, only to avoid tasks end message:
		// otherwise we would have a message shown on top of the custom template
		App::end () ;
	}

}

?>
