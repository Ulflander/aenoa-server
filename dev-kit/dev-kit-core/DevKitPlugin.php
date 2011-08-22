<?php


class DevKitPlugin extends Task {
	
	private $pluginPath ;
	
	private $_usable = false ;
	
	private $_activated = false ;
	
	protected $version ;
	
	public $conf ;
	
    function initPlugin () {
    	
		$this->pluginPath = DK_PLUGINS . $this->taskName ;
		
		$this->conf = new PHPPrefsFile ( $this->pluginPath . DS . 'plugin-conf.php' ) ;
		
		if ( $this->conf->exists () == false )
		{
			$this->_usable = false ;
			return false ;
		}
		
		$this->_activated = $this->conf->get ( 'activated' ) ;
		return $this->isUsable () ;
    }
	
	final public function isPlugin ()
	{
		return true ;
	}
	
	public function hasUpdate ()
	{
		
	}

	public function isActivated ()
	{
		return $this->_activated ;
	}
	
	public function isUsable ()
	{
		return $this->_usable && $this->_activated ;
	}
	
	public function getVersion ()
	{
		return $this->version ;
	}
	
	public function getConfValue ( $confValueKey )
	{
		return $this->conf->get ( $confValueKey ) ;
	}
	
	public function confExists ()
	{
		return $this->conf->exists () ;
	}
	
	
}
?>