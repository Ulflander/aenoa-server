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

		foreach ( $folders as $folder )
		{
			
		}
		
		// Last, we create view

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
