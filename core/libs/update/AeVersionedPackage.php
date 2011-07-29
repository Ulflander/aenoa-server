<?php

class AeVersionedPackage {
	
	private $_packageName = '' ;
	
	private $_packagePath = '' ;
	
	private $_name = '';
	
	private $_localVersion = '0-0-0' ;
	
	private $_remoteUpdatedVersion = '0-0-0' ;
	
	private $_updateFile ;
	
	function __construct ( $name , $path ) {
		$this->_packageName = $name ;
		$this->_packagePath = $path ;
		$this->_name = basename($path);
		
		
		$f = new PHPPrefsFile(setTrailingDS($this->_packagePath) . 'version.php', true);
		if ( $f->has('version') )
		{
			$this->_localVersion = $f->get('version');
		} else {
			if ( is_dir($this->_packagePath) )
			{
				$f->set('version', $this->_localVersion);
				$f->flush () ;
			} else {
				$this->_localVersion = '999-0-0' ;
			}
		}
		
		
	}
	
	function hasUpdate( $filelist = null )
	{
		if ( !is_null($this->_updateFile ) )
		{
			return true ;
		}
		if ( is_null($filelist) )
		{
			return false ;
		}
		
		foreach ($filelist as $filename)
		{
			if ( strpos($filename,$this->_packageName.'-') === 0 && strpos($filename,'.zip') !== false )
			{
				$remoteVersion = trim(str_replace(array('.zip',$this->_packageName.'-'),array('',''),$filename)) ;
				
				if ( version_comp($remoteVersion,$this->_localVersion) === 1 && ($this->_remoteUpdatedVersion == '0-0-0' || version_comp($remoteVersion,$this->_remoteUpdatedVersion)))
				{
					$this->_remoteUpdatedVersion = $remoteVersion ;
					$this->_updateFile = $filename ;
				}
			}
		}
		
		if ( !is_null($this->_updateFile) )
		{
			return true ;
		}
		
		return false ;
	}
	
	function getName ()
	{
		return $this->_name ;
	}
	
	function getPackageName()
	{
		return $this->_packageName;
	}
	
	function getPackagePath()
	{
		return $this->_packagePath;
	}
	
	function getUpdateFile()
	{
		return $this->_updateFile;
	}
	
	function getLocalVersion()
	{
		return $this->_localVersion;
	}
	
	function getRemoteUpdatedVersion()
	{
		return $this->_remoteUpdatedVersion;
	}
	
}

?>