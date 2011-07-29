<?php


class Ftp {
	
	private $_host ;
	
	private $_user ;
	
	private $_pass ;
	
	private $_port ;
	
	private $_ftpStream ;
	
	private $_authorized ;
	
	private $mode = FTP_BINARY ;
	
	public $timeout = 30 ;

	function __construct ( $host, $user, $pass, $port = 21 , $autoConnect = true ) {
		
		$this->_host = $host ;
		
		$this->_user = $user ;
		
		$this->_pass = $pass ;
		
		$this->_port = $port ;
		
		if ( $autoConnect )
		{
			$this->connect () ;
		}
	}
	
	
	function close ()
	{
		if ( $this->isUsable () ) {
			ftp_close ( $this->_ftpStream ) ;
		}
	}
	

	function __destruct ( ) {
		$this->close () ;
	}

	function connect () {
		$this->_ftpStream = ftp_connect ( $this->_host, $this->_port, $this->timeout ) ; // or die ( "erreur de connexion" ) ;

		if ( !$this->_ftpStream === true ) {
			return 1 ;
		}
		else {
			$this->_authorized = ftp_login ( $this->_ftpStream, $this->_user, $this->_pass ) ;
			ftp_pasv($this->_ftpStream , true ) ;
			if ( $this->_authorized == true ) {
				return 2 ;
			}
			else {
				return 3 ;
			}
		}
	}
	
	function isUsable ()
	{
		return $this->_ftpStream !== false && $this->_authorized === true ;
	}

	function pwd () {
		if ( $this->isUsable () ) {
			return ftp_pwd ( $this->_ftpStream ) ;
		}
		else {
			return false ;
		}
	}

	function cd ( $dir ) {
		if ( $this->isUsable () ) {
			return ftp_chdir ( $this->_ftpStream, $dir ) ;
		}
		else {
			return false ;
		}
	}

	function ls ( $folder = '.' ) {
		if ( $this->isUsable () ) {
			$output = array ( ) ;
			
			$tab_ls = ftp_nlist ( $this->_ftpStream, $folder ) ;
			
			if ( $tab_ls !== false ) {
				foreach ( $tab_ls as $id => $file ) {
					if ( $file != '.' && $file != '..' ) {
						$output[] = $file ;
					}
				}
				return $output ;
			}
		}
		
		return false ;
	}

	function put($remoteFile, $localFile, $mode = ''){
		if ( true === empty ( $mode ) ) {
			$mode = $this->mode ;
		}
		
		if ( $this->isUsable () ) {
			return ftp_put ( $this->_ftpStream, $remoteFile, $localFile, $mode ) ;
		} 	else {
			return false ;
		}
	}

	function get ( $localFile, $remoteFile, $mode = '' ) {
		if ( true === empty ( $mode ) ) {
			$mode = $this->mode ;
		}
		
		if ( $this->isUsable () ) {
			return ftp_get ( $this->_ftpStream, $localFile, $remoteFile, $mode ) ;
		} else {
			return false ;
		}
	}

	function chmod ( $file, $mode ) {
		if ( $this->isUsable () ) {
			return ftp_chmod ( $this->_ftpStream, $mode, $file ) ;
		} else {
			return false ;
		}
	}

	function mkdir(){
		$dirs = func_get_args();
		$result = true;
		if ( $this->isUsable () ) {
			foreach($dirs as $dir){
				$result = ($result && ftp_mkdir($this->_ftpStream, $dir));
			}
			return $result;
		} else {
			return false ;
		}
	}

	function rm ( $file ) {
		if ( $this->isUsable () ) {
			return ftp_delete ( $this->_ftpStream, $file ) ;
		} else {
			return false ;
		}
	}

	function rmdir ( $dir ) {
		if ( $this->isUsable () ) {
			return ftp_rmdir ( $this->_ftpStream, $dir ) ;
		} else {
			return false ;
		}
	}

	function setMode ( $mode ) {
		$this->mode = $mode ;
	}
}


?>