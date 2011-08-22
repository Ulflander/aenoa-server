<?php


/**
 * The Task class is the main class of the 
 * 
 */
class CommonSubTask {
	
	private static $_total ;
	
	private static $_current ;
	
	private static $_task ;
	
	private static $_pid ;
	
	private static $_basePID = 0 ;
	
	private static $_mount = null ;
	
	static private function getPID () 
	{
		self::$_basePID ++ ;

		return 'pb-'. self::$_basePID ;
	}
	
	static public function mount ( &$task , $path )
	{
		// Check for mounted volume, only is target is not accessible
		if ( is_dir ( $path ) == false )
		{
			// No volume mounted
			$mounted = false ;
			global $broker ;
			// Target is in Volumes
			$_parh = explode ( '/' , $path ) ;
			if ( $_parh[1] == 'Volumes' && $broker->preferences->get ( 'backupMount' ) != '' )
			{
				$volumeName = $_parh[2] ;
				if ( @mkdir ( DS.'Volumes'.DS. $volumeName ) )
				{
					exec ( 'mount_smbfs //' . $broker->preferences->get ( 'backupMount' ) . ' /Volumes/' . $volumeName ) ;
					if ( is_dir ( DS.'Volumes'.DS. $volumeName ) )
					{
						self::$_mount = $volumeName ;
						$task->view->setSuccess ( 'Volume ' . $volumeName . ' mounted.' ) ;
						$mounted = true ;
					} else {
						$task->view->setError ( 'Volume ' . $volumeName . ' not mounted.' ) ;
					}
				}
			}
			return $mounted ;
		}
		return true ;
	}
	
	static public function umount ( &$task )
	{
		if ( !is_null( self::$_mount) )
		{
			exec ( 'umount /Volumes/' . self::$_mount ) ;
			
			if ( is_dir ( '/Volumes/' . self::$_mount ) )
			{
				$task->view->setError ( 'Volume ' . self::$_mount . ' not unmounted. Unmount it manually.' ) ;
			} else {
				$task->view->setSuccess ( 'Volume ' . self::$_mount . ' unmounted.' ) ;
			}
			
			self::$_mount = null ;
		}
	}
	
	static public function delete ( &$task , $target )
	{
		if ( $task->futil->dirExists ( $target) == false )
		{
			$task->view->setError ( 'Folder ' . basename ( $target ) . ' does not exist.' ) ;
			return false ;
		}
		
		self::$_task = $task ;
		
		self::$_pid = self::getPID () ;
		
		self::$_total = self::$_task->futil->getFilesCount ( $target , true ) ;
		
		self::$_current = 0 ;
	
		self::$_task->view->setProgressBar ( 'Deleting folder ' . basename ( $target ) . '...' , self::$_pid ) ;
		
		$cb = new Callback ( 'update' , new stdClass () , 'CommonSubTask' ) ;
		
		if ( self::$_task->futil->removeDir ( $target , $cb ) )
		{	
			self::$_task->view->updateProgressBar ( self::$_pid , 100  ) ;
			self::$_task->view->setSuccess ( 'Folder ' . basename ( $target ) . ' successfully deleted.' ) ;
			return true ;
		} else {
			self::$_task->view->updateProgressBar ( self::$_pid , 100  ) ;
			self::$_task->view->setError ( 'Some files of folder ' . basename ( $target ) . ' have not been deleted. Check out files authorizations.' ) ;
			return false ;
		}
	}
	
	
	static public function copy ( &$task , $from , $to , $overwrite = false )
	{
		if ( is_dir ( $to ) && $overwrite == false )
		{
			$task->view->setError ( 'Folder ' . basename ( $to ) . ' exists yet.' ) ;
			return false ;
		}
		
		if ( !is_dir ( $to ) )
		{
			@mkdir ( $to ) ;
		}
		
		self::$_task = $task ;
		
		self::$_pid = self::getPID () ;
		
		self::$_total = self::$_task->futil->getFilesCount ( $from , true ) ;
		
		self::$_current = 0 ;
	
		self::$_task->view->setProgressBar ( 'Copying folder ' . basename ( $from ) . '...' , self::$_pid ) ;
		
		$cb = new Callback ( 'update' , new stdClass () , 'CommonSubTask' ) ;
		
		if ( self::$_task->futil->copy ( $from , $to , $cb ) )
		{	
			self::$_task->view->updateProgressBar ( self::$_pid , 100  ) ;
			self::$_task->view->setSuccess ( 'Folder ' . basename ( $from ) . ' successfully copied.' ) ;
			return true ;
		} else {
			self::$_task->view->updateProgressBar ( self::$_pid , 100  ) ;
			self::$_task->view->setError ( 'Some files of folder ' . basename ( $from ) . ' have not been copied. Check out files authorizations.' ) ;
			return false ;
		}
	}
	
	static public function update ( $fileCount , $lastFileCopied )
	{
		self::$_current += $fileCount ;
		
		self::$_task->view->updateProgressBar ( self::$_pid , ceil ( self::$_current * 100 / self::$_total )- 1, $lastFileCopied ) ;
	}
	

	static public function download ( &$task , $url , $to , $overwrite = false )
	{
		$to = setTrailingDS ( $to ) ;
		
		if ( is_file ( $to . basename ( $url ) ) && $overwrite == false )
		{
			$task->view->setError ( 'File ' . $to . basename ( $url ) . ' exists yet.' ) ;
			return false ;
		} 
		
		if ( !is_dir ( $to ) )
		{
			@mkdir ( $to ) ;
		}
		
		self::$_task = $task ;
		
		self::$_pid = self::getPID () ;
	
		self::$_task->view->setProgressBar ( 'Downloading file ' . basename ( $url ) . '...' , self::$_pid ) ;
		
		$range = 0 ;
		$totalBytes = 0 ;
		$currentBytes = 0 ;
		$__headers = get_headers ( $url, 1 );
    
	    $url_stuff = parse_url($url);
	    $port = isset($url_stuff['port']) ? $url_stuff['port'] : 80;
	   
	    $fp = @fsockopen($url_stuff['host'], $port);
	   
	    if (!$fp)
	        return false;
	   
	    $query  = 'GET '.$url_stuff['path'].'?'.@$url_stuff['query']." HTTP/1.1\r\n";
	    $query .= 'Host: '.$url_stuff['host']."\r\n";
	    $query .= 'Connection: close'."\r\n";
	    $query .= 'Cache-Control: no'."\r\n";
	    $query .= 'Accept-Ranges: bytes'."\r\n";
	    if ($range != 0)
	        $query .= 'Range: bytes='.$range.'-'."\r\n"; // -500
	    //$query .= 'Referer: http:/...'."\r\n";
	    //$query .= 'User-Agent: myphp'."\r\n";
	    $query .= "\r\n";
	   
	    fwrite($fp, $query);
	   
	    $chunksize = 1*(1024*1024);
	    $headersfound = false;
	    $buffer = '' ;
		
	    while (!feof($fp) && !$headersfound) {
	        $buffer .= @fread($fp, 1);
	        if (preg_match('/HTTP\/[0-9]\.[0-9][ ]+([0-9]{3}).*\r\n/', $buffer, $matches)) {
	            $headers['HTTP'] = $matches[1];
	            $buffer = '';
	        } else if (preg_match('/([^:][A-Za-z_-]+):[ ]+(.*)\r\n/', $buffer, $matches)) {
	            $headers[$matches[1]] = $matches[2];
	            $buffer = '';
	        } else if (preg_match('/^\r\n/', $buffer)) {
	            $headersfound = true;
	            $buffer = '';
	        }
	
	        if (strlen($buffer) >= $chunksize)
	            return false;
	    }
	
	    if (preg_match('/4[0-9]{2}/', $headers['HTTP']))
	        return false;
	    else if (preg_match('/3[0-9]{2}/', $headers['HTTP']) && !empty($headers['Location'])) {
	        $url = $headers['Location'];
	        return http_get($url, $range);
	    }
		
	    $track = false ;
	    if ( array_key_exists('Content-Length' , $headers ) )
	    {
		    $l = $headers['Content-Length'] ;
		    $i = 0 ;
		    $track = true ;
	    } else {
			self::$_task->view->updateProgressBar ( self::$_pid , -1 , 'Download tracking unavailable. Please wait....' ) ;
	    }
	    
	   	$content = '' ;
	    while (!feof($fp) && $headersfound) {
	        $content .= @fread($fp, $chunksize);
	        
			if ( $track ) 
			{
				$i ++ ;
				if ( $i == 3 )
				{
					$i = 0;
					self::$_task->view->updateProgressBar ( self::$_pid , @ftell ( $fp ) * 100 / $l ) ;
				}
			}
	    }
	    
	    self::$_task->view->updateProgressBar ( self::$_pid , 100 ) ;
	    
	    $status = fclose($fp);
	
	    $f = new File ( $to . basename ( $url ) , true ) ;
	    
		if ( $status && $f->exists () && $f->write ( $content ) && $f->close () )
		{
			self::$_task->view->setSuccess ( 'File ' . basename ( $url ) . ' successfully downloaded.' ) ;
			return true ;
		} else {
			self::$_task->view->setError ( 'File ' . basename ( $url ) . ' has not been copied. Check out files authorizations.' ) ;
			return false ;
		}
	}
	
	
	static public function zip ( &$task , $folder , $zipname , $to , $overwrite = false )
	{
		$zippath = unsetTrailingDS ( $to ) . DS . $zipname ;
		
		self::$_task = $task ;
		
		self::$_pid = self::getPID () ;
		
		if ( is_file ( $zippath ) && $overwrite == false ) 
		{
			$task->view->setError ( 'File ' . $zippath . ' exists yet.' ) ;
			return;
		} else {
			self::$_task->futil->removeFile ( $zippath ) ;
		}
		
		$archive = new PclZip($zippath);
		$v_list = $archive->create($folder);
	
		if ( $v_list != 0 )
		{	
			self::$_task->view->setSuccess ( 'Folder ' . basename ( $folder ) . ' successfully zipped.' ) ;
			return true ;
		} else {
			self::$_task->view->setError ( 'Some files of folder ' . basename ( $folder ) . ' have not been zipped. Check out files authorizations.' ) ;
			return false ;
		}
	}
	
	static public function ftpSend ( &$task , $ftp_ids , $directory , $filesToSend , $overwrite = false )
	{
		self::$_task = $task ;
		
		self::$_pid = self::getPID () ;
	
		$ftp = new Ftp ( $ftp_ids['host'] , $ftp_ids['login'] , $ftp_ids['pass'] ) ;
		
		if ( $ftp->isUsable () == false )
		{
			self::$_task->view->setError ( 'Ftp cannot connect to ' . $ftp_ids['host'] . '. Check out your remote connection or given host.' ) ;
			return;
		}
		
		
		self::$_task->view->setProgressBar ( 'Send files on ' . $ftp_ids['host'] . '...' , self::$_pid ) ;
		
		$l = count ( $filesToSend ) ;
		$current = 0 ;
		$errors = 0 ;
		
		if ( $ftp->cd ( $directory ) )
		{
			self::$_task->view->setSuccess ( 'Ftp ls:' . $directory ) ;
		} else if ( $ftp->mkdir ( $directory ) == false || $ftp->cd ( $directory ) == false )
		{
			self::$_task->view->setError ( 'Ftp ls failed, ' . $directory . ' does not exists.') ;
			unset ( $ftp ) ;
			return;
		} else {
			self::$_task->view->setSuccess ( 'Ftp folder created:' . $directory ) ;
		}
		
		foreach ( $filesToSend as $file )
		{
			$current ++ ;
			
			$currentPercent = ceil ( $current * 100 / $l ) ;
			
			self::$_task->view->updateProgressBar ( self::$_pid , $currentPercent , 'Sending: ' . $file ) ;
			
			if ( $ftp->put ( basename ( $file ) , $file , FTP_ASCII) == false )
			{
				$errors ++ ;
			}
			
		}
		
		if ( $errors == 0 )
		{
			self::$_task->view->setSuccess ( 'Ftp transfer all done.') ;
			
			unset ( $ftp ) ;
			
			return true ;
		} else {
			self::$_task->view->setSuccess ( 'Ftp transfer failed: ' . $errors . ' file(s) or folder(s) has not been sended. Check out ftp rights for your login and password.') ;
			
			unset ( $ftp ) ;
			
			return false ;
		}
	}

	
	static public function extract ( &$task , $file , $to , $overwrite = false )
	{
		if ( is_dir ( $to ) )
		{
			if ( $overwrite == false )
			{
				self::$_task->view->setError ( 'Folder ' . basename ( $to ) . ' exists yet.' ) ;
				return false ;
			} else {
				self::delete ( $task , $to ) ;
			}
		}
		
		if ( !is_dir ( $to ) )
		{
			@mkdir ( $to ) ;
		}
		
		self::$_task = $task ;
		
		self::$_pid = self::getPID () ;
	
		$needed_dirs = array () ;
		
		$archive = new PclZip($file);
		
		$archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING) ;
		
		// Is the archive valid?
		if ( false == $archive_files )
		{
			self::$_task->view->setError ( 'Archive ' . basename ( $file ) . ' is not valid.' ) ;
			return false ;
		}
		
		if ( 0 == count($archive_files) )
		{
			self::$_task->view->setError ( 'Archive ' . basename ( $file ) . ' is empty.' ) ;
			return false ;
		}
		
		// Determine any children directories needed (From within the archive)
		foreach ( $archive_files as $_file ) {
			if ( '__MACOSX/' === substr($_file['filename'], 0, 9) ) // Skip the OS X-created __MACOSX directory
				continue;
	
			$needed_dirs[] = $to . unsetTrailingSlash( $_file['folder'] ? $_file['filename'] : dirname($_file['filename']) );
		}
		$needed_dirs = array_unique($needed_dirs);
		foreach ( $needed_dirs as $dir ) {
			// Check the parent folders of the folders all exist within the creation array.
			if ( unsetTrailingSlash($to) == $dir ) // Skip over the working directory, We know this exists (or will exist)
				continue;
			if ( strpos($dir, $to) === false ) // If the directory is not within the working directory, Skip it
				continue;
	
			$parent_folder = dirname($dir);
			
			while ( !empty($parent_folder) && unsetTrailingSlash($to) != $parent_folder && !in_array($parent_folder, $needed_dirs) ) {
				$needed_dirs[] = $parent_folder;
				$parent_folder = dirname($parent_folder);
			}
		}
		asort($needed_dirs);
	
		// Create those directories if need be:
		foreach ( $needed_dirs as $_dir ) {
			if ( ! self::$_task->futil->createDir ( dirname($_dir) , basename ( $_dir ) ) )
			{ // Only check to see if the dir exists upon creation failure. Less I/O this way.
				self::$_task->view->setError ( 'Extraction has detected file I/O errors.' ) ;
				return false;
			}
		}
		unset($needed_dirs);
		
		$total = count ( $archive_files ) ;
		$currentPercent = 0 ;
		$current = 0 ;
		
		
		self::$_task->view->setProgressBar ( 'Extracting file ' . basename ( $file ) . '...' , self::$_pid ) ;
		
	
		// Extract the files from the zip
		foreach ( $archive_files as $_file ) {
			if ( $_file['folder'] || '__MACOSX/' === substr($_file['filename'], 0, 9) ) // Don't extract the OS X-created __MACOSX directory files
			{
				$total -- ;
				continue;
			}
			
			$f = new File ( $to . $_file['filename'] , true ) ; 
			$f->write ( $_file['content'] ) ;
			$f->close () ;
			
			$current ++ ;
			
			$currentPercent = ceil ( $current * 100 / $total ) ;
			
			self::$_task->view->updateProgressBar ( self::$_pid , $currentPercent , 'Unzipping: ' . $_file['filename'] ) ;
		}
		
		self::$_task->view->updateProgressBar ( self::$_pid , 100 ) ;
		self::$_task->view->setSuccess ( 'Extraction of ' . $file . ' done.') ;
		
		return true;
	}
}
?>