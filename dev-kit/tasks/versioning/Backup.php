<?php

class Backup extends Task {

	function getOptions() {

		$opt = new Field() ;
		$opt->name = 'tag' ;
		$opt->label = _('Backup tag') ;
		$opt->required = true ;

		return array (
			$opt
		);
	}

	function process()
	{
		// For now, backup is totally successfull
		$all_ok = true ;

		// Create the backup folder name
		$backup = str_replace(array(':','-', ' '), array ('_','_','_'), mysql_date() );

		// Path where backups are stored
		$path = AE_APP_BACKUP;

		// Create backups folder if needed
		if ($this->futil->dirExists($path) == false) {
			$this->futil->createDir(dirname($path), basename($path));
		}

		// Check if backup exists yet (in case two admins make a backup at the same time..
		if ( $this->futil->dirExists($path . DS . $backup) )
		{
			$this->view->setError(_('Backup not done: unable to create backup base folder'));
			$this->manager->cancel() ;
		}

		// Create backup folder
		if ( !$this->futil->createDir($path, $backup) )
		{
			$this->view->setError(_('Backup not done: unable to create backup base folder'));
		}

		// Root of current backup
		$path .= DS . $backup;

		// Tag filename
		$tag_filename = '.backuptag' ;

		// Set the backup tag
		$f = new File ( $path .DS . $tag_filename , true ) ;


		// Copy "app"
		if ( !$this->futil->createDir($path, 'app') )
		{
			$this->view->setError(_('Backup not done: unable to create backup app folder'));
			return;
		}

		if (!$this->futil->copy(AE_APP, $path. DS . 'app')) {
			$this->view->setError(_('Backup not done: copy of files failed'));
			return;
		} else {
			$this->view->setStatus(_('Copying app folder...'), true);
		}

		// Check for some custom backup folders for this application
		// This should be store in a "backup" file in "app" folder, as others conf files
		// This should be filled with paths to folders or files, separated by a new line, with one path for each line
		$f = new File ( AE_APP . 'backup', false ) ;

		// Custom paths to backup
		$paths = array () ;

		// If file exists, extract content
		if ( $f->exists() )
		{
			$paths = explode("\n", $f->read() ) ;
			$f->close () ;
		}

		// We backup each path
		foreach ( $paths as $p )
		{

			// In case path exists
			if ( $this->futil->dirExists(ROOT . $p) || $this->futil->fileExists(ROOT.$p) )
			{
				// Create root of the folder to backup
				if ( strpos($p, DS) !== false && !$this->futil->createDirs( $path . DS, dirname($p) ) )
				{
					$this->view->setError(sprintf(_('Backup not done: creation of %s folder(s) failed'), dirname($p)));
				}

				// Copy folder
				if ( !$this->futil->copy(ROOT . $p, $path . DS . $p ) )
				{
					$this->view->setError(sprintf(_('Backup of custom folder %s failed'), $p) ) ;
					$all_ok = false ;
				} else {
					$this->view->setStatus(sprintf(_('Copying %s...'), $p), true);
				}

			}
		}


		switch ( $all_ok )
		{
			case true:
				$this->view->setSuccess(sprintf(_('Backup done and named %s'), $backup));
				break;
			default:
				$this->view->setWarning(sprintf(_('Backup named %s has failed for some parts, refer to previous error messages to solve the problem.'), $backup));
		}

	}

}

?>
