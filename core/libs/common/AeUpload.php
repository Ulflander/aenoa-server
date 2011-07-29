<?php


class AeUpload {


	public $destination ;
	
	public $appendRoot = true ;
	
	private $fsutil = null ; 
	
	private $_valid = true ;

	private $_type ;

	private $_mime = '' ;

	private $_minWidth = 0 ;

	private $_minHeight = 0 ;
	
	private $_maxWidth = 10000 ;

	private $_maxHeight = 10000 ;

	// Default 2m
	private $_max = 2097152 ;

	private $_requiredTypes ;
	
	private $_error = '' ;
	
	private $_finalPath = '' ;
	
	private $_rename_to = null ;
	
	/*
	 	Variable: conversion
	 	
	 	Only for web images, should be an array like this one :
	 	(start code)
	 	array (
			'type' => 'jpg',
			'size' => array ( 50, 50),
			'suffix' => '_50',
			'path' => 'path'.DS.'from'.DS.'root' 
	 	);
	 	(end)
	 */
	public $conversions = array () ;
	
	
	function renameTo ( $name = null )
	{
		$this->_rename_to = $name ;
	}
	
	function getPath ()
	{
		return $this->_finalPath ;
	}

	function __construct ()
	{
		$this->destination = 'downloads' ;
		
		global $FILE_UTIL;
		$this->fsutil = $FILE_UTIL ;
	}

	function setMaxUploadSize ( $size = 2097152 )
	{
		$this->_max = $size ;
	}

	function setMaxImageSize ( $wh )
	{
		$this->_maxWidth = $wh[0] ;
		$this->_maxHeight = $wh[1] ;
	}

	function setMinImageSize ( $wh )
	{
		$this->_minWidth = $wh[0] ;
		$this->_minHeight = $wh[1] ;
	}
	
	
	
	
	function getType ()
	{
		return $this->_type ;
	}

	function setRequiredTypes ( $mimetypes )
	{
		if ( is_array($mimetypes) )
		{
			$this->_requiredTypes = $mimetypes ;
		} else if(is_string($mimetypes) )
		{
			$this->_requiredTypes = $mimetypes ;
		} else {
			trigger_error('AeUploadd::setRequiredTypes : mimetype ' . $mimetypes.' not valid',E_USER_ERROR);
		}
	}
	
	function requireWebImage ()
	{
		$this->_requiredTypes = File::$webImageMimes ;
	}
	
	function isValid ()
	{
		return $this->_valid ;
	}
	
	function process ( $name , $dest = null)
	{
		/* Check file */
		if ( is_uploaded_file( $_FILES[$name]['tmp_name'] ) && !empty ( $_FILES[$name]['tmp_name'] ) )
		{
			if ( is_null ( $dest ) )
			{
				$dest = $this->destination ;
			}
			
			$this->_finalPath = setTrailingSlash($dest) ;
			
			if ( $this->appendRoot )
			{
				$dest = ROOT . $dest ; 
			}
				
			/* Chek Destination folder */
			if ( @is_dir ( $dest ) )
			{
					
				/* Check Destination folder end slash, to add file name without problem */
				$dest = setTrailingDS($dest);

				$_FILES[$name]['name'] = strtolower($_FILES[$name]['name']);
				
				/* Split name/ext */
				$filename = preg_split ('/\./', $_FILES[$name]['name'] , -1 , PREG_SPLIT_NO_EMPTY ) ;
				$count = count ( $filename ) ;
				$ext = strtolower( $filename[$count-1] );
				unset ( $filename[$count-1] ) ;
				$basename = implode ( '.' , $filename ) ;
				
				if ( $filename == '' )
				{
					$this->_error = _('Filename is not valid');
					$this->_valid = false ;
					return false;
				}
				

				$this->_mime = File::getMimeFromExt($ext) ;
				
				if ( !empty($this->_requiredTypes) && !in_array($this->_mime,$this->_requiredTypes))
				{
					$this->_error = _('File extension is not valid. Please provide a valid file.');
					$this->_valid = false ;
					return false;
				}
				
				if ( $this->isWebImage() )
				{
					if ( !$this->validateWebImage ($name) )
					{
						return false;
					}
				}
				


				/* Check if a file with the same name already exists */
				/* and change our filename if that's true */
					
				$handle = opendir ( $dest ) ;
					
				/* Read filenames and put them in an array */
				/* In PHP5, scandir does the same */
				$i = 0 ;
				while ( $handle_files = readdir ( $handle ) )
				{
					$existing_files[$i] = $handle_files ;
					$i ++ ;
				}
				
				if ( is_null($this->_rename_to ) )
				{
					$basename = tl_get($basename) ;
					
					
					/* If a file already have the name */
					 if ( in_array ( $basename . '.' . $ext , $existing_files ) )
					{
					
						/* Increment filename */
						/* Something wrong ??   ;)  */
						for ( $i = 1 ; $i < 999 ; $i ++ )
						{
							if ( !in_array ( $basename . '-'.$i . '.' . $ext , $existing_files ) )
							{
								$basename = $basename . '-'.$i ;
								$_FILES[$name]['name'] = $basename . '.' . $ext ;
								break ;
							
							}
						}
					} else {
						$_FILES[$name]['name'] = $basename . '.' . $ext ;
					}
					
				} else {
					
					$basename = $this->_rename_to ;
					
					$_FILES[$name]['name'] = $basename . '.' . $ext ;
				}
				
				
					
					
				$this->_finalPath .= $_FILES[$name]['name'] ;
				
				/* Save our file */
				if ( move_uploaded_file( $_FILES[$name]['tmp_name'] , $dest . $_FILES[$name]['name'] ) )
				{
					if ( is_array($this->conversions) && !empty($this->conversions) )
					{
						$this->convertWebImage($dest . $_FILES[$name]['name'] , $basename , $ext );
					}
					
					return true ;
				} else {
					$this->_error = _('File not saved on server.') ;
					$this->_valid = false ;
				}

			} else {
					
				$this->_valid = false ;
				$this->_error = _('Destination folder on server does not exists.') ;
			}
				
		} else {

			$this->_valid = false ;
			$this->_error = _('No such uploaded file') ;
		}
		
		return false ;
	}
	
	
	function validateWebImage ($name)
	{
		$size = getimagesize($_FILES[$name]['tmp_name']);

		if ( $size[0] > $this->_maxWidth || $size[1] > $this->_maxHeight )
		{
			$this->_valid = false ;
			$this->_error = sprintf(_('Image is too big: required image should have a maximum width of %s and a maximum height of %s'),$this->_maxWidth, $this->_maxHeight) ;
			return false ;
		}
		if ( $size[0] < $this->_minWidth || $size[1] < $this->_minHeight )
		{
			$this->_valid = false ;
			$this->_error = sprintf(_('Image is too small: required image should have a minimum width of %s and a minimum height of %s'),$this->_minWidth, $this->_minHeight) ;
			return false ;
		}
		return true ;
	}
	
	function convertWebImage ( $path , $basename , $ext , $crop = false)
	{
		if(!list($w, $h) = getimagesize($path)) return "Unsupported picture type!";
		
		switch ( $ext )
		{
			
			case 'jpg':
				$img = imagecreatefromjpeg($path);
				break;
			case 'png':
				$img = imagecreatefrompng($path);
				break;
			case 'gif':
				$img = imagecreatefromgif($path);
				break;
			default:
				return false ;
				
		}

		foreach ( $this->conversions as $conversion )
		{
			// TODO: check conversion array
			if ( ake('size', $conversion ) )
			{
				$width = $conversion['size'][0];
				$height = $conversion['size'][1];
				// resize
				if($crop)
				{
					if($w < $width or $h < $height) continue;
					$ratio = max($width/$w, $height/$h);
					$h = $height / $ratio;
					$x = ($w - $width / $ratio) / 2;
					$w = $width / $ratio;
				} else {
					if($w < $width and $h < $height) continue;
					$ratio = min($width/$w, $height/$h);
					$width = $w * $ratio;
					$height = $h * $ratio;
					$x = 0;
				}
			} else {
				
				$width = $w ;
				$height = $h ;
				$x = 0 ;
				
			}
			
			
			$new = imagecreatetruecolor($width, $height);
			
			if ( ake ('background', $conversion) )
			{
				$bgcolor = imagecolorallocate( $img, $conversion['background'][0], $conversion['background'][1], $conversion['background'][2]);
				
				imagefill ( $new, 0, 0, $bgcolor ) ;
			
			} else if(
				($ext == "gif" || $ext == "png") && 
				($conversion['type'] == "gif" || $conversion['type'] == "png")
			){
				
				imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
				
				imagealphablending($new, false);
				
				imagesavealpha($new, true);
				
			}
	  		
			imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
			
			$p = setTrailingDS(ROOT.$conversion['path']) ;
			
			$s = (ake('suffix', $conversion) ? $conversion['suffix']:'');
			
			$p = $p.$basename.$s.'.'.$conversion['type'];
			
			switch ( $conversion['type'] )
			{
				case 'jpg':
					$img2 = imagejpeg($new, $p);
					break;
				case 'png':
					$img2 = imagepng($new, $p);
					break;
				case 'gif':
					$img2 = imagegif($new, $p);
					break;
			}
		
		}
	}
	
	function getError ()
	{
		return $this->_error ;
	}

	
	function isWebImage ()
	{
		return in_array($this->_mime, File::$webImageMimes );
	}




}
?>