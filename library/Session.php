<?php

/**
 * Class: Session
 *
 * Session class extends <Collection> so the <Collection> API must be used to set and get session stored items.
 *
 * A session is automatically created by <App> at initialization of Aenoa Server.
 *
 * An <User> is created when <Session::connect> is called by <App>
 *
 * See also:
 * <App>, <User>
 */


/**
 * Hook: SessionClose
 *
 * Hook dispatched when a <Session> is closed
 *
 * Parameters:
 *		- $args[0] Session object reference
 *
 * See also:
 * <Hook>, <Session>
 */

class Session extends Collection {
	
	const SECURE_HIGH = 'high' ;
	
	const SECURE_MEDIUM = 'medium' ;
	
	const SECURE_LOW = 'low' ;
	
	const STATE_INIT = 'init' ;
	
	const STATE_FATAL = 'fatal' ;
	
	const STATE_CLOSED = 'closed' ;
	
	const STATE_UNLOGGED = 'unlogged' ;
	
	const STATE_LOGGED = 'logged' ;
	
	/**
	 * Used with checkSession method, 
	 * define a check result for a new session
	 */
	const CHECK_NEW = 'new' ;
	
	/**
	 * Used with checkSession method, 
	 * define a check result for a failed session (possibly highjacked)
	 */
	const CHECK_FAIL = 'fail' ;
	
	/**
	 * Used with checkSession method, 
	 * define a check result for a yet registered and valid session
	 */
	const CHECK_OLD = 'old' ;
	
	private $_prev2Sid ;
	
	private $_prevSid ;
	
	private $_sid ;
	
	private $_state ;
	
	private $_oldData = array () ;
	
	private $_sessKey = false ;
	
	private $_isNew = true ;
	
	private $_lastError = '' ;
	
	private $user ;
	
	/**
	 * Creates a new instance of Session
	 *
	 * @see User
	 * @see App::getSession
	 */
	function __construct ()
	{
		$this->__setSessionKey () ;
		
		$this->_state = self::STATE_INIT ;
	}


	/**
	 * Connect to the existing session, or create a new one
	 *
	 * @return type
	 */
	function connect ()
	{
		if ( $this->__canStart () == false )
		{
			$this->_lastError = 'Session cannot start' ;
				
			return false;
		}
		
		session_name ('AESESS') ;
		
		$this->log( 'Session connection' ) ;
	
		if ( !is_dir(ROOT.'.private'.DS.'sessions'))
		{
			global $FILE_UTIL;	
			$FILE_UTIL->createDir(ROOT,'.private');
			chmod(ROOT.'.private', 0777);
			$FILE_UTIL->createDir(ROOT.'.private', 'sessions');
			chmod(ROOT.'.private'.DS.'sessions', 0777);
		}
		
		if ( is_dir(Config::get ( App::SESS_PATH )) && is_writable( Config::get ( App::SESS_PATH ) ))
		{
			session_save_path( unsetTrailingSlash ( Config::get ( App::SESS_PATH ) ) ) ;
			ini_set('session.gc_probability', 1);
		} else {
			$this->_lastError = 'Session save path problem' ;
				
			return false ;
		}
	
		
		
		if ( !session_start () || session_id() == '' )
		{
			$this->_lastError = 'connection failed' ;
				
			return false;
		}
		
		$this->_prevSid = session_id ()  ;
		
		$this->log( 'Session connection / Current id : ' . session_id () );
		
		if ( function_exists ( 'apache_request_headers' ) )
		{
			$headers = apache_request_headers ( ) ;
			
			if ( array_key_exists ( 'Cookie' , $headers ) )
			{
				$hc = $headers['Cookie'] ;
				$this->_prev2Sid = preg_replace('/(.*)PHPSESSID=([^;]*)(.*)/' , '\2', $hc );
			}
		}
			
		$tdata = $_SESSION ;
		$this->_oldData = $tdata;
		
		
		$check = $this->checkSession ( $tdata ) ;
		
		// Yet connected
		switch ( $check )
		{
			case self::CHECK_OLD:
				$this->_isNew = false ;
				
				$this->_sid = $tdata['sid'] ;
				
				if ( Config::get ( App::SESS_REGENERATE_ID ) === true )
				{
					$this->__regenerateID () ;
				}
				
				
				$this->_state = self::STATE_UNLOGGED ;
				$this->setAll ( $tdata['data'] ) ;
				
				$this->_getPersistentHeaders () ;
			break;
			case self::CHECK_NEW:
				
				$this->setAll( array () ) ;
				$_SESSION = array () ;
				
				$this->__regenerateID () ;

				$this->_state = self::STATE_UNLOGGED ;
				$this->set ( 'Session.started' , time () ) ;
				break;
			case self::CHECK_FAIL:
				$this->_lastError = 'reconnection failed' ;
				$_SESSION = array () ;
				$this->close ( false ) ;
				return false ;
		}
		
		
		$this->user = new User () ;
		
		$this->set ( 'Session.key' , $this->_sessKey ) ;
		
		return true ;
	}
	
	public function lastError ()
	{
		return $this->_lastError ;
	}
	
	public function reset ()
	{
		$this->close(false) ;
		
		$this->_state = self::STATE_INIT ;
		
	}
	
	private function checkSession ( $sessData )
	{
		if ( !empty ( $sessData ) )
		{
			if ( array_key_exists ( 'sid' , $sessData ) && $sessData['sid'] == session_id () )
			{
				return self::CHECK_OLD ;
			}
			
			return self::CHECK_FAIL ;
		}
		
		return self::CHECK_NEW ;
	}
	
	function isNew () 
	{
		return $this->_isNew ;
	}
	
	function started ()
	{
		return ( $this->_state != self::STATE_FATAL && $this->_state != self::STATE_INIT ) ;
	}

	
	function close ( $write = true )
	{
		if ( $this->started () == true )
		{

			
			new Hook ( 'SessionClose' , $this ) ;
			
			if ( $write === true )
			{
				$_SESSION['prevSid'] = $this->_prevSid ;
				
				$_SESSION['sid'] = $this->_sid ;
				
				$_SESSION['data'] = $this->getAll() ;
			} else {
				$_SESSION = array () ;
			}
			
			$this->_state = self::STATE_CLOSED ;
			
			@session_write_close () ;
		}
		
		
		
		return true ;
	}
	
	function getSID ()
	{
		return $this->_sid ;
	}
	
	function checkSID ( $sid , $previousIsValid = false )
	{
		return ( $sid != '' && ( $sid == $this->_sid || ( $previousIsValid == true && $sid == $this->_prev2Sid ) ) );
	}
	
	private function __canStart ()
	{
		return ( headers_sent () == false && $this->_state == self::STATE_INIT ) ;
	}
	
	private function __regenerateID ()
	{	
		session_destroy();
		
		session_start () ;
		
		/* Regenerate session */
		if ( session_regenerate_id ( true ) )
		{
			$this->_sid = session_id() ;
		}
		
		$this->log ( 'SID regeneration: prev: ' . $this->_prevSid . ' / New: ' . $this->_sid ) ;
	}
	
	function regenerate ()
	{
		$this->__regenerateID() ;
	}
	
	private function __setSessionKey ()
	{
		$str = '' ;
		
		if ( array_key_exists( 'HTTP_USER_AGENT' , $_SERVER ) )
		{
			$str .= md5($_SERVER['HTTP_USER_AGENT']);
		}
		
		if ( Config::get ( App::SESS_STRING ) == App::TEMP_SESS_STRING && App::getQuery () != 'maintenance/check-context' )
		{
			trigger_error('You must define a configuration value named Application::SESS_STRING containing a randomized string before using Aenoa Server', E_USER_ERROR) ;
		}
		
		$str .= '_' . Config::get ( App::SESS_STRING ) ;
		
		$this->_sessKey = $str ;
	}
	
	function __destruct ()
	{
		if ( $this->started () == true )
		{
			$this->close () ;
		}
	}
	
	
	/**
	 * @return User
	 */
	function getUser ()
	{
		return $this->user ;
	}


	/**
	 * Get and unset a value, only if session is started
	 *
	 * @see Collection::uget
	 * @param string $key Key of option
	 * @return mixed Value if session started and key exists, null otherwise
	 */
	function uget ( $key )
	{
		if ( $this->started () == true )
		{
			return parent::uget( $key ) ;
		}

		return null ;
	}

	/**
	 * Set a new value, only if session is started
	 *
	 * @see Collection::set
	 * @param string $key Key of item
	 * @param mixed $value Value of item
	 * @return Session Current instance for chained command on this element
	 */
	function set ( $key , $value )
	{
		if ( $this->started () == true )
		{
			parent::set( $key, $value );
		}

		return $this ;
	}

	/**
	 * Unset an item of collection, only if session is started
	 *
	 * @see Collection::uset
	 * @param string $key Key of item to unset
	 * @return Session Current instance for chained command on this element
	 */
	function uset ( $key )
	{
		if ( $this->started () == true )
		{
			parent::uset( $key ) ;
		}

		return $this ;
	}

	/**
	 * Empty the collection, only if session is started
	 *
	 * @see Collection::usetAll
	 * @return Session Current instance for chained command on this element
	 */
	function usetAll ()
	{
		if ( $this->started() == true )
		{
			parent::usetAll() ;
		}

		return $this ;
	}

	
	
	private function _getPersistentHeaders ()
	{
	
		// Detect if we try to get page in ajax mode (by sending an http header from JS for example
		if ( ake('HTTP_AENOA_NEXT_EDIT_DATA', $_SERVER ) )
		{
			$list =explode(',', $_SERVER['HTTP_AENOA_NEXT_EDIT_DATA'] ) ;
			foreach ( $list as $str )
			{
				list($key,$val ) = explode(':',$str) ;
				if ( !is_null($key) && !is_null($val) )
				{
					list($struct,$table,$field) = explode('/', $key ) ;
					if ( !is_null($struct) && !is_null($table) && !is_null($field) )
					{
						$this->addPersistentPost($struct,$table,$field,$val) ;
					}
				}
			}
			
		}
	}
	
	function addPersistentPost ( $struct, $table, $field , $value )
	{
		
		if ( $this->has ( 'POST.Persistent' ) )
		{
			$a = $this->get('POST.Persistent') ;
		} else {
			$a = array () ;
		}
		
		if ( ake($struct.'/'.$table,$a) == false )
		{
			$a[$struct.'/'.$table] = array ( $field=>$value) ;
		} else {
			$a[$struct.'/'.$table][$field] = $value ;
		}
		
		
		
		$this->set('POST.Persistent',$a) ;
	}
	
	function getPersitentPost ( $struct , $table )
	{
		$ret = array () ;
		if ( $this->has ( 'POST.Persistent' ) )
		{
			$a = $this->get('POST.Persistent') ;
		
			if ( ake($struct.'/'.$table,$a) )
			{
				$ret = $a[$struct.'/'.$table] ;
				$a[$struct.'/'.$table] = array () ;
				$this->uset ( 'POST.Persistent' ) ;
				$this->set('POST.Persistent',$a) ;
			}
		}
		return $ret ;
	}
	
	function getFormattedPersitentPost ( $struct , $table )
	{
		$a = $this->getPersitentPost($struct,$table) ;
		$b = array () ;
		foreach ( $a as $field => $val )
		{
			$b[$struct.'/'.$table.'/'.$field] = $val ;
		}
		return $b ;
	}
}
?>