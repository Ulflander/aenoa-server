<?php

/**
 * The JSONPrefsFile file uses a JSON file located where you want.
 * You can set, unset and read data from JSONPrefsFile.
 * 
 * 
 * 
 */
class JSONPrefsFile extends PrefsFile
{
    protected $jutil ;
	
	/**
	 * Overwrite this method in concrete classes
	 * 
	 * @return 
	 */
	public function read ()
	{
		$data = std2arr ( json_decode( parent::read () ) ) ;
		
		if ( is_null ( $data ) || $data == 'null' )
		{
			$this->data = array () ;
		} else {
			$this->data = $data ;
		}
	}
	
	
	public function flush ()
	{
		return parent::write ( json_encode ( $this->data ) ) ;
	}
	
}
?>