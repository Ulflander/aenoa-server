<?php

/**
 * FSUtil is an utility library to read and edit the file system
 */
class FSUtil
{
	
	/**
	 * Error codes registered by FSUtil
	 * @private
	 */
	private $errors = array () ;
	
	private $root = '' ;
	
	
	/**
	 * Video type of file
	 */
	const VIDEO = 'video' ;
	
	/**
	 * Image type of file
	 */
	const IMAGE = 'image' ;
	
	/**
	 * Sound type of file
	 */
	const SOUND = 'sound' ;
	
	/**
	 * Flash type of file
	 */
	const FLASH = 'flash' ;
	
	/**
	 * Text type of file
	 */
	const TEXT = 'text' ;
	
	/**
	 * PDF type of file
	 */
	const PDF = 'pdf' ;
	
	/**
	 * Others type of file
	 */
	const FILE = 'file' ;
	
	/**
	 * Consider CVS folders as hidden files	
	 * @var boolean
	 */
	public $CVSAsHidden = true ;
	
	/**
	 * Constructor
	 * 
	 * @param object $maxSize Total size authorized for root path
	 * @param object $root Root path
	 * @return 
	 */
	function __construct ( $root , $maxSize = 0 )
	{
		$this->max_size = $maxSize ;
		
		$this->setRoot( $root );
	}
	
	
	/**
	 * Set root for file operations for this FSUtil instance
	 * 
	 * @param string $root Path to the root directory
	 */
	function setRoot ( $root )
	{
		$this->root = $root ;
	}
	
	/**
	 * Get root for file operations for this FSUtil instance
	 * 
	 * @param object $root Path to the root directory
	 */
	function getRoot ()
	{
		return $this->root;
	}
	
	/**
	 * Returns errors and empty errors array.
	 * 
	 * Check FSUtil::getErrorMessage() method to get a message corresponding to error codes returned by getErrors method.
	 * 
	 * @return array Array of errors code.
	 */
	function getErrors ()
	{
		$err = $this->errors ;
		
		$this->errors = array () ;
		
		return $err ;
	}
	
	/**
	 * Return a message corresponding to an error code.
	 * 
	 * @param int $errorCode
	 * @return string Message corresponding to code
	 */
	function getErrorMessage ( $errorCode )
	{
		switch ( $errorCode )
		{
			case 1001: return 'File or directory not removed. Check authorizations.' ;
			case 1002: return 'Path not found in local filesystem.' ;
			case 1003: return 'Directory not created. Check authorizations.' ;
			case 1004: return 'Upload failed : no more free space. Contact administrator.' ;
			case 1005: return 'Upload failed : bad file extension; or filename is "index".' ;
			case 1006: return 'Upload failed : uploaded file not moved.' ;
			case 1007: return 'Attempt to use file system out of the Aenoa root directories.' ;
			case 1008: return 'Directory not moved. Check authorizations.' ;
		}
	}
	
	/**
	 * Check if FSUtil has any error
	 * 
	 * @return boolean True if FSUtil has error, false otherwise
	 */
	function hasError ()
	{
		return ( count ( $this->errors ) > 0 ) ;
	}
	
	/**
	 * Sanitize, checks and return the path 
	 * 
	 * @param string $path The path to complete
	 * @param bool $shouldExist Should the asked path really exist or not (default: true)
	 * @return The path as string if found in filesystem, false otherwise. Test return value with === statement.
	 */
	function getPath ( $path = '.' , $shouldExist = true )
	{
		if (  $this->root != '' && ( $path == '' || substr_count( $path, $this->root ) == 0 ) )
		{
			if ( is_link ( $this->root . $path ) )
			{
				$path = $this->root.$path ;
			} else {
				$path = realpath( $this->root . $path ) ;
			}
		}
		
		if ( $path == '' )
		{
			$this->errors[] = 1007 ;
			return false ;
		}
		
		if ( is_dir ( $path ) )
		{
			$path = setTrailingDS($path) ;
		}
		
		if ($shouldExist == true && !is_file ( $path ) && !is_dir( $path ) && !is_link ( $path ) )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		return $path ;
	}
	
	/**
	 * Returns a list of paths of all files and dirs conained in $path
	 * 
	 * @param string $path Path where find files and dirs
	 * @return An indexed array if path found, false otherwise.
	 */
	function getTree ( $path = '.' , $returnHidden = false , $result = null )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		if ( is_null( $result ) ) 
		{
			$result = array () ;
		}
		
		$files = scandir ( $path ) ;
		foreach ( $files as $f )
		{
			if ($f != '.' && $f != '..' )
			{
				if ( $returnHidden == false && ( (substr($f, 0 , 1) == '.' || ( $this->CVSAsHidden && $f == 'CVS' ) ) ) )
				{
					continue;
				}
				
				$result[] = $path.$f ;
				
				if ( @is_dir($path.$f) )
				{
					$result = $this->getTree($path.$f.DS, $returnHidden, $result ) ;
				}
			}
		}
		
		return $result ;
	}
	
	/**
	 * Returns a list of paths of all dirs conained in $path
	 * 
	 * @param string $path Path where find files and dirs
	 * @return An indexed array if path found, false otherwise.
	 */
	function getFolderTree ( $path = '.' , $returnHidden = false , $result = null )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		if ( is_null( $result ) ) 
		{
			$result = array () ;
		}
		
		$files = scandir ( $path ) ;
		foreach ( $files as $f )
		{
			if ($f != '.' && $f != '..' )
			{
				if ( $returnHidden == false && ( (substr($f, 0 , 1) == '.' || ( $this->CVSAsHidden && $f == 'CVS' ) ) ) )
				{
					continue;
				}
				
				if ( @is_dir($path.$f) )
				{
					$result[] = $path.$f ;
				
					$result = $this->getFolderTree($path.$f.DS, $returnHidden, $result ) ;
				}
			}
		}
		
		return $result ;
	}
	
	/**
	 * Returns an array containing directories given path.
	 * 
	 * @param string $path Path where find files and dirs
	 * @return An indexed array if path found, false otherwise.
	 */
	function getDirsList ( $path = '.' , $returnHidden = true )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		$array = array () ;
		$i = 0 ;
		$files = scandir ( $path ) ;
		foreach ( $files as $f )
		{
			if ($f != '.' && $f != '..' && @is_dir ( $path.DS.$f ) )
			{
				if ( $returnHidden == false && (substr($f, 0 , 1) == '.' || ( $this->CVSAsHidden && $f == 'CVS' ) ))
				{
					continue;
				}
				
				$array[$i] = array () ;
				$array[$i]['name'] = $f ;
				$array[$i]['path'] = $path.$f . DS ;
				$array[$i]['type'] = 'dir' ;
				$i ++ ;
			}
		}
		return $array ;
	}
	
	/**
	 * Returns the last file contained in directory (based on alphanum order)
	 * 
	 * @param string $path Path where find files and dirs
	 * @param bool $invert Set to true to reverse file order, and then retrieve the first file
	 * @return Return path to the file.
	 */
	function getLastFile ( $path = '.' , $invert = false )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		$files = scandir ( $path ) ;
		
		if ( $invert === false )
		{
			return $path . DS . array_pop ( $files ) ;
		} else {
			return $path . DS . array_shift ( $files ) ;
		}
	}
	
	/**
	 * Returns an array containing directories and files contained in given path.
	 * 
	 * @param string $path Path where find files and dirs
	 * @param bool $returnHidden Set to false to skip hidden files
	 * @param bool $invert Invert the returned array
	 * @return An indexed array if path found, false otherwise.
	 */
	function getFilesList ( $path = '.' , $returnHidden = true , $invert = false )
	{
		$path = $this->getPath($path) ;
		if ( $path === false || !is_dir ( $path ) ) 
		{
			
			$this->errors[] = 1002 ;
			return false ;
		}
		
			
		$array = array () ;
		$i = 0 ;
		$files = scandir ( $path ) ;
		
		foreach ( $files as $f )
		{
			if ($f != '.' && $f != '..' )
			{
				if ( $returnHidden == false && (substr($f, 0 , 1) == '.' || ( $this->CVSAsHidden && $f == 'CVS' ) ))
				{
					continue;
				}
				
				$array[$i] = $this->getFileInfo ( setTrailingDS($path) . $f ) ;
				
				$i ++ ;
			}
		}
		
		if ( $invert === true )
		{
			$array = array_reverse( $array ) ;
		}
		
		return $array ;
	}


	
	/**
	 * Returns info about a file
	 */
	public function getFileInfo ( $path )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		$arr = array () ;
		$arr['name'] = basename ($path) ;
		$arr['path'] = $path ;
		
		if ( @is_dir ( $path ) )
		{
			$arr['type'] = 'dir' ;
			$arr['path'] = setTrailingDS ( $arr['path'] ) ;
		} else {
			$ext = $this->getExtension ( $arr['name'] ) ;
			$arr['filename'] = $this->getFilename ( $arr['name'] ) ;
			$arr['size'] = $this->getSize( $path ) ;
			$arr['lastModification'] = $this->getLastModif ( $path ) ;
			$arr['extension'] = $ext ;
			$arr['type'] = $this->getType( $ext ) ;
		}
		return $arr ;
	}
	
	/**
	 * Returns the count of contained directories and files contained in given path.
	 * 
	 * @param string $path Path where find files and dirs
	 * @return An indexed array if path found, false otherwise.
	 */
	function getFilesCount ( $path = '.' , $recursive = false )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		$files = @scandir ( $path ) ;
		if ( $files )
		{
			$i = 0 ;
			foreach ( $files as $f )
			{
				if ($f != '.' && $f != '..' )
				{
					if ( $recursive === true && ( is_dir ( $path .DS. $f ) || is_link ( $path.DS.$f) ) )
					{
						$i += $this->getFilesCount ( $path.DS.$f , true ) ;
					}
					
					$i ++ ;
				}
			}
			return $i ;
		}
		
		return 0;
	}
	
	
	/**
	 * Apply a callback method on all sub elements in a given directory
	 * 
	 * The value returned to callback is the complete path to the sub element.
	 * 
	 * Symbolic links will NOT be processed
	 * 
	 * @param string $callback
	 * @param object $thisObj
	 * @param string $path [optional]
	 * @param bool $recursive [optional]
	 * @return bool True if callback done, false otherwise
	 */
	function applyCallback ( $callback , $thisObj , $path = '' , $recursive = false )
	{
		if ( is_null ( $callback ) 
			|| is_null ( $thisObj ) 
			|| @method_exists ( $thisObj , $callback ) == false 
			|| ( $path = $this->getPath($path) ) == false )
		{
			return false ;
		}
		
		$files = $this->getFilesList ( $path ) ;
		$then = array () ;
		
		foreach ( $files as $file )
		{
			if ( is_link ( $file['path'] ) ) 
			{
				continue;
			}
			
			$this->__applyCallback ( $callback , $thisObj , $file['path'] ) ;
			
			if ( $recursive == true && $file['type'] == 'dir' )
			{
				$then[] = $file['path'] ;
			}
		}
		
		foreach ( $then as $__path ) 
		{
			$this->applyCallback ( $callback , $thisObj , $__path , true ) ;
		}
		
		return true ;
	}
	
	/**
	 * @private
	 */
	private function __applyCallback ( $callback , $thisObj , $value )
	{
		$thisObj->$callback ( $value ) ;
	}
	
	
	
	/**
	 * Check if a directory contains given sub directories
	 * 
	 * @param string $path The path
	 * @param array $subdirs [optional]
	 * @return True if all subdirs exists in given path, false otherwise
	 */
	function hasSubdirs ( $path , $subdirs = array () ) 
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		if ( !is_dir ( $path ) )
		{
			return false ;
		}
		
		foreach ( $subdirs as $subdir )
		{
			if ( !is_dir ( $path . DS . $subdir ) )
			{
				return false ;
			}
		}
		
		return true ;
	}
	
	/**
	 * Return true if a directory exists and is strictly a directory
	 * 
	 * @param string $path
	 * @return True if $path is a directory, false otherwise
	 */
	function dirExists ( $path )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		return @is_dir ( $path ) ;
		
	}
	
	
	/**
	 * Return true if a file exists and is strictly a file
	 * 
	 * @param string $path
	 * @return True if $path is a file, false otherwise
	 */
	function fileExists ( $path )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}

		return file_exists ( $path ) && is_file ( realpath ( $path ) ) ;
	}
	
	
	/**
	 * Creates a symbolic link to a target (directory or file)
	 * 
	 * @param string $path Path to the link to create
	 * @param string $target Path to the target (the target must exists and must be a dir or a file)
	 * @return True of link is created, false otherwise
	 */
	function createLink ( $path , $target )
	{
		$path = $this->getPath($path, false) ;
		
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		return (@is_dir ( $target ) || @is_file ( $target ) ) && @symlink ( $target , $path ) ;
	}
	
	/**
	 * Return true if path is a valid symbolic link
	 * 
	 * @param object $path
	 * @return 
	 */
	function isLink ( $path )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		return ( @is_link ( $path ) ) ;
	}
	
	
	
	
	/**
	 * Remove a file
	 * 
	 * @param string $path Path to a file
	 * @return True if file removed, false otherwise
	 */
	function removeFile ( $path )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		if ( ( is_file ( $path ) || is_link ( $path ) ) && $path != 'index.html' )
		{
			if ( @chmod ( $path , 0777 ) &&
					@unlink ( $path ) ) 
			{
				return true ;
			} else {
				$this->errors[] = 1001 ;
				return false ;
			}
		}
	}

	/**
	 * Move a directory
	 * 
	 * @param string $path Path to a directory
	 * @param string $to Path where move the directory
	 * @return True if directory moved, false otherwise
	 */
	function moveDir ( $path , $to )
	{
		if ( ($path = $this->getPath($path)) === false || (!is_dir($to)) === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		if ( @rename ( $path , $to ) )
		{
			return true ;
		} else {
			$this->errors[] = 1008 ;
			return false ;
		}
		
	}

	/**
	 * Check if a file is an hidden file
	 * 
	 * @param string $filename The file name to check
	 * @return True if file is hidden file, false otherwise
	 */
	function isHiddenFile ( $filename )
	{
		return (substr($filename,0,1) == '.' ) ;
	}

	/**
	 * Rename a directory or file in another one
	 * 
	 * @param string $path Path to a directory
	 * @param string $to Path where copy the directory
	 * @return True if directory moved, false otherwise
	 */
	function rename ( $path , $to )
	{
		if ( ($path = $this->getPath($path)) === false || ($to = $this->getPath($to,false)) === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		if ( @rename ( $path , $to ) )
		{
			return true ;
		} else {
			$this->errors[] = 1008 ;
			return false ;
		}
	}

	/**
	 * Copy a directory or file in another one
	 * 
	 * @param string $path Path to a directory
	 * @param string $to Path where copy the directory
	 * @param Callback $callback A callback object. Two args returned to callback: $fileCount and $lastFileCopied
	 * @return True if directory moved, false otherwise
	 */
	function copy ( $path , $to , $callback = null , $options=array('folderPermission'=>0755,'filePermission'=>0755) )
	{
		$i = 0 ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		$result=true;
   
        if (is_file($path)) {
        	if ($to[strlen($to)-1]=='/') {
                if (!file_exists($to)) {
                    cmfcDirectory::makeAll($to,$options['folderPermission'],true);
                }
                $__dest=$to."/".basename($source);
            } else {
                $__dest=$to;
            }
           if ( !@copy($path, $__dest) )
		   {
		   		$result = false ;
		   }
           @chmod($__dest,$options['filePermission']);
           
        } elseif(is_dir($path)) {
            if ($to[strlen($to)-1]=='/') {
                if ($path[strlen($path)-1]=='/') {
                    //Copy only contents
                } else {
                    //Change parent itself and its contents
                    $to=$to.basename($path);
                    @mkdir($to);
                    @chmod($to,$options['filePermission']);
                }
            } else {
                if ($path[strlen($path)-1]=='/') {
                    //Copy parent directory with new name and all its content
                    @mkdir($to,$options['folderPermission']);
                    @chmod($to,$options['filePermission']);
                } else {
                    //Copy parent directory with new name and all its content
                    @mkdir($to,$options['folderPermission']);
                    @chmod($to,$options['filePermission']);
                }
            }

            $dirHandle=opendir($path);
            while($file=readdir($dirHandle))
            {
                if($file!="." && $file!="..")
                {
                     if(!is_dir($path."/".$file)) {
                        $__dest=$to."/".$file;
                    } else {
                        $__dest=$to."/".$file;
                    }
                    if ( !$this->copy($path."/".$file, $__dest, $callback , $options) )
					{
						$result = false ;
					} else if ( $callback != null)
					{
						$callback->apply ( 1 , $file ) ;
					}
                }
            }
            closedir($dirHandle);
           
        } else {
            return false ;
        }
		
        return $result; 
	}
	
	/**
	 * Remove a directory
	 * 
	 * @param string $path Path to a directory
	 * @return True if directory removed, false otherwise
	 */
	function removeDir ( $path , $callback = null )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		@chmod ( $path , 0777 ) ;
		
		if ($handle = @opendir($path)) {
			while($name=@readdir($handle))
			{
				$_p = $path. DS .$name  ;
				if ($name==='.' or $name==='..')
				{
					continue;
				} else if ( is_file ( $_p ) || is_link ( $_p ) )
				{
					@chmod ( $_p , 0777 ) ;
					@unlink( $_p ) ;
					if ( !is_null ( $callback ) )
					{
						$callback->apply ( 1 , basename($_p) );
					}
				} else if( is_dir( $_p ) )
				{
					$this->removeDir( $_p );
				}
			}
			
			@closedir($handle);
		}
		
		if ( !@rmdir($path) ) 
		{
			$this->errors[] = 1001 ;
			return false ;
		} else {
			return true ;
		}
		
	}
	

	/**
	 * Creates a directory
	 * 
	 * @param string $path Path to a directory
	 * @param string $name Name of the new directory
	 * @return True if directory created, false otherwise
	 */
	function createDir ( $path, $name )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		if ( is_dir( $path . '/' . $name ) || mkdir ( $path . '/' . $name ) )
		{
			return true ;
		} else {
			$this->errors[] = 1003 ;
			return false ;
		}
	}
	


	/**
	 * Creates a path of directories
	 *
	 * @param string $root Root path
	 * @param string $path Path of new directories, such as some/folders/names
	 * @return True if directorie created, false otherwise
	 */
	function createDirs ( $root, $path )
	{
		$root = $this->getPath($root) ;
		if ( $root === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}

		$path = explode(DS,$path) ;

		while ( !empty($path) )
		{
			$dir = array_shift($path);
			if ( $dir == '' || !$this->createDir($root, $dir))
			{
				return false ;
			}

			$root = setTrailingDS($root) . $dir ;
		}
		return true ;
	}


	
	/**
	 * Runs the $_FILES global array and try to upload given files
	 * 
	 * @param string $path Path to a directory
	 * @param string $name Name of the new directory
	 * @return True if directory created, false otherwise
	 */
	function upload ( $path )
	{
		if ( !$this->check_size () ) {
			$this->errors[] = 1004 ;
			return false;
		}
	
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		$result = false ;
	
		foreach ( $_FILES as $key => $value )
		{
			$name = $value['name']  ;
			
			$nname = $this->check_filename ( $name ) ;
			
			$ext = explode( '.' , $name  ) ;

			$last = count ( $ext ) - 1 ;

			if ( $ext[0] == 'index' || $ext[$last] == 'php' || $ext[$last] == 'php3' || $ext[$last] == 'php4' || $ext[$last] == 'php5' || $ext[$last] == 'htm' || $ext[$last] == 'html' )
			{

				$this->errors[] = 1005 ;
				return false;
			} else {

				if ( is_uploaded_file ( $value['tmp_name'] ) == false )
				{
					continue;
				}
				
				chmod($path, 0777);
				
				$uploadfile = $path . '/' . $nname ;
				
				if ( !move_uploaded_file($value['tmp_name'], $uploadfile) ) {
					$this->errors[] = 1006 ;
				}

			}
		}
		return true;
	}
	
	
	
	
	/**
	 * Check if a path is a subpath of another path
	 * 
	 * @param string $path Path to a directory
	 * @return Size of the directory, false if directory not found
	 * @private
	 */
	private function isSubDir($path , $compare ) 
	{
		return strcasecmp($path, $compare) > 0 ;
	}

	
	/**
	 * Returns the size of a whole directory
	 * 
	 * @param string $path Path to a directory
	 * @return Size of the directory, false if directory not found
	 * @private
	 */
	public function getDirSize ( $path )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}

		if ( $handle = @opendir($path) )
		{
			$mas = 0 ;
			
			while ($file = readdir($handle)) 
			{
				if ($file != '..' && $file != '.')
				{
					if ( is_dir(realpath ($path.DS.$file)) ) 
					{
						$mas += $this->getDirSize ($path.DS.$file);
					} else if (is_file(realpath($path.DS.$file))) {
						$mas += $this->getSize($path.DS.$file);
					}
				}
			}
	    	return $mas;
		}
		
		return 0 ;
	}


	/**
	 * Returns true if directory is empty, false otherwise
	 * 
	 * @param string $path Path to a directory
	 * @return Size of the file
	 */
	public function isEmpty ( $path )
	{
		return ( $this->getFilesCount( $path ) == 0 ) ;
	}
	
	/**
	 * Returns the size of a file
	 * 
	 * @param string $path Path to a file
	 * @return Size of the file
	 */
	public function getSize ( $path )
	{
		$path = $this->getPath($path) ;
		if ( $path === false )
		{
			$this->errors[] = 1002 ;
			return false ;
		}
		
		if ( $fs = @filesize($path) )
		{
			return round ($fs/1024/1024, 2);
		}
		
		return 0 ;
	}
	
	/**
	 * Returns the last modification date of a file
	 * 
	 * @param string $path Path to a file
	 * @return Date of last modification of the file
	 * @private
	 */
	private function getLastModif ( $path )
	{
		if ( $fs = @filectime($path) )
		{
			return round ($fs/1024/1024, 2);
		}
	}
	
	/**
	 * Checks if root directory size is below max size
	 * 
	 * @return True if space is still available, false otherwise
	 * @private
	 */
	private function checkSize ()
	{
		if ( $this->getSize ( $this->getPath() ) > $this->max_size )
		{
			return false ;
		} else {
			return true;
		}
	}
	
	/**
	 * Checks if a name is a valid directory name
	 * 
	 * @param string $n
	 * @return True if name is valid, false otherwise
	 */
	function checkName ( $n )
	{
		if ( !preg_match ( '`^[[:alnum:]]{1,32}$`' , $n ) )
		{
			return false ;
		} else {
			return true ;
		}
	}
	
	/**
	 * Checks if a name is a valid file name
	 * 
	 * @param string $n
	 * @return True if name is valid, false otherwise
	 */
	function checkFilename ( $n )
	{
		return preg_replace ( '`^[[:alnum:].-_]{1,128}$`' , '' , $n ) ;
	}
	
	/**
	 * Get the extension of a file
	 * 
	 * @param string $file
	 * @return Extension of file
	 */
	function getExtension ( $file )
	{
		$f = basename ( $file ) ;
		$f = explode ( '.' , $file ) ;
		return $f[count ( $f )-1] ;
	}
	
	/**
	 * Get the name of the file without the extension of a file
	 * 
	 * @param string $file
	 * @return Extension of file
	 */
	function getFilename ( $file )
	{
		$f = basename ( $file ) ;
		$f = explode ( '.' , $file ) ;
		array_pop($f) ;
		return implode ( '.' , $f ) ;
	}
	
	/**
	 * Get the type of a file based on its extension
	 * 
	 * @param string $ext The extension
	 * @return Type of file, as described by ASEFile consts.
	 */
	function getType ( $ext )
	{
		switch ( $ext )
		{
			case 'avi': case 'mov': case 'flv' :
			case 'mpg': case 'mpeg':
				return self::VIDEO ;
			break;
			case 'jpg': case 'jpeg': case 'psd':
			case 'gif': case 'ai': case 'bmp':
			case 'png': case 'raw':
				return self::IMAGE ;
			break;
			case 'mp3': case 'wav': case 'mp4':
			case 'wma': case 'aif': case 'aifc':
			case 'aiff': case 'aac':
				return self::SOUND ;
			break;
			case 'swf': case 'swc':
				return self::FLASH ;
			break;
			case 'txt': case 'ini': case 'xml':
			case 'rtf':
				return self::TEXT ;
			break;
			case 'pdf':
				return self::PDF ;
			break;
			default:
				return self::FILE ;
			break;
		}
	}
	
}

?>