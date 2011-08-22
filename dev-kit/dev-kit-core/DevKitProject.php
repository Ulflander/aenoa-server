<?php


/**
 * This object represents an Aenoa dev-kit project
 */
class DevKitProject {
	
	const CHANGELOG_DIR = '.aenoachangelog' ;
	
	/**
	 * The name of the project, it's the same as the name of the directory of this project
	 * @var string
	 */
	public $name ;
	
	/**
	 * Type of project. For now type is one of these :
	 * - aenoa
	 * - wordpress
	 * - normal
	 * @var string
	 */
	public $type ;
	
	/**
	 * The path to the project
	 * @var
	 */
	public $path ;
	
	/**
	 * The URL to the project
	 * @var
	 */
	public $URL ;
	
	/**
	 * Validity of project : 
	 * if you try to create a project that does not have a directory in dev-kit environment, project
	 * will be considered as invalid, and type, path and URL properties will not be available.
	 * 
	 * If you w
	 * @var
	 */
	public $valid = true ;
	
	public $class = 'light-block' ;
	
	/**
	 * A reference to the FSUtil main instance
	 * @var
	 */
	private $futil ;
	
	
	private $_cvsEnable = false ;
	
	private $_versionTag ;
	
	private $_locked ;
	
	private $_hasOwnUpdateFTP = false ;
	
	
	/**
	 * Constructor
	 * 
	 * @param string $name The name of the project
	 * @return 
	 */
	function __construct ( $name )
	{
		if ( defined ( 'DK_DEV_KIT' ) == false )
		{
			trigger_error('DevKitProject must be used in Aenoa dev-kit environment.', E_USER_ERROR) ;
			die();
		}
		
		$this->futil = new FSUtil(dirname(ROOT)) ;
		
		$this->name = $name ;
		
		$this->_getInfos () ;
		
	}
	
	public function isLocked ()
	{
		return $this->_locked ;
	}
	
	
	public function refresh ()
	{
		$this->_getInfos () ;
	}
	
	public function hasOwnUpdateFTP ()
	{
		return $this->_hasOwnUpdateFTP ;
	}
	
	private function _getInfos () 
	{
		$this->_check () ;
		
		$this->_locked = $this->futil->fileExists ( ROOT . $this->name . DS . '.locked' ) ;
		
		$this->_hasOwnUpdateFTP = $this->futil->fileExists ( ROOT . $this->name . DS . 'app' . DS . 'libs' . DS .'ApplicationUpdate.php' ) ;
		
		$this->_getType () ;
		
		$this->_getPath () ;
		
		$this->URL = url() . $this->name ;
		
		$this->_getCVSInfo () ;
	}
	
	
	private function _getCVSInfo ()
	{
		if ( is_dir ( ROOT.$this->name.DS.'CVS' ) == true )
		{
			$this->_cvsEnable = true ;
			if ( is_file ( ROOT.$this->name.DS.'version.php' ) )
			{
				$f = new PHPPrefsFile ( ROOT.$this->name.DS.'version.php' , false) ;
				$this->_versionTag = $f->get ('version') ;
				$f->close () ;
				
			}
		}
	}
	
	public function isCVSEnable ()
	{
		return $this->_cvsEnable ;
	}

	public function getCVSTag ()
	{
		return $this->_versionTag ;
	}
	
	public function getCVSVersion ()
	{
		return trim(str_replace($this->getPackageName ().'-','',$this->getCVSTag()));
	}
	
	public function getPackageName ()
	{
		return str_replace (array('_', '.', '-'), array('','',''), $this->name ) ;
	}
	
	public function getPackageNameAndVersion ()
	{
		return $this->getPackageName () . '-' . $this->getCVSVersion() ;
	}
	
	private function _check ()
	{
		
		return $this->valid ;
	}
	
	private function _getType ()
	{
		if ( $this->futil->fileExists ( ROOT . 'app-conf.php' ) )
		{
			$this->type = DevKitProjectType::AENOA ;
			$this->class = 'orange-block' ;
		} else if ( $this->futil->fileExists ( ROOT . $this->name .DS . 'plugin-conf.php' ) )
		{
			$this->type = DevKitProjectType::AENOA_PLUGIN ;
			$this->class = 'orange-block' ;
		} else if ( $this->futil->fileExists ( $this->name .DS . 'core' . DS . 'libs' . DS . 'AeCSSCompressor.php' )
				 || $this->futil->fileExists ( $this->name .DS . 'app-conf.php' )
				 || $this->name =='acf' || $this->name == 'ajsf' || $this->name == 'static' )
		{
			$this->type = DevKitProjectType::AENOA ;
			$this->class = 'green-block' ;
		} else if ( $this->futil->hasSubdirs ( $this->name , array ('wp-admin' , 'wp-content' , 'wp-includes' ) ) )
		{
			$this->type = DevKitProjectType::WORDPRESS ;
			$this->class = 'blue-block' ;
		} else {
			$this->type = DevKitProjectType::UNKNOWN ;
			$this->class = 'light-block' ;
		}
	}
	
	
	private function _getPath ()
	{
		$this->path = $this->futil->getPath ( $this->name ) ;
	}
	
	public function create ( $name )
	{
		if ( $this->_check == false && $this->futil->createDir ( '' , $name ) == true )
		{
			 return true ;
		}
		
		return false ;
	}
	
	public function isEmpty ()
	{
		if ( $this->futil->isEmpty ( $this->path ) )
		{
			
		}
	}
	
	public function hasFavicon ()
	{
		if ( $this->futil->fileExists ( $this->name . DS . 'favicon.png' ) || $this->futil->fileExists ( $this->name . DS . 'favicon.ico' ))
		{
			return true ;
		}
		
		return false ;
	}
	
	public function getFaviconURL ()
	{
		if ( $this->futil->fileExists ( $this->name . DS . 'favicon.png' ) )
		{
			return $this->URL . '/favicon.png' ;
		} else {
			return $this->URL . '/favicon.ico' ;
		}
		
	}
	
	
	public function getSubProjects ()
	{
		if ( $this->valid )
		{
			$files = $this->getDirsList ( ROOT.$this->name ) ;
			
		}
	}
	
	
	
}
?>