<?php

/**
 * PHPPrefsFile only supports strings and numeric values
 */
class PHPPrefsFile extends PrefsFile {
	
	
	
	/**
	 * Overwrite this method in concrete classes
	 * 
	 * @return 
	 */
	public function read ()
	{
		$result = array () ;
		preg_match_all('/\$data\[\'([A-Za-z0-9_]+)\'\]=\'(.*?)(?<!\')\';/', parent::read (), $result ) ;
		
		$this->data = array () ;
		
		$l = count ( $result[1] ) ;
		for ( $i = 0 ; $i < $l ; $i ++ )
		{
			switch ( true )
			{
				case is_numeric ( $result[2][$i] ):
					$this->data[$result[1][$i]] = intval ( $result[2][$i] ) ;
					break;
				case $result[2][$i] == 'true':
					$this->data[$result[1][$i]] = true ;
					break;
				case $result[2][$i] == 'false':
					$this->data[$result[1][$i]] = false ;
					break;
				default:
					$this->data[$result[1][$i]] = $result[2][$i] ;
			}
		}
	}
	
	
	public function flush ()
	{
		$data = '<?php ' ;
		foreach( $this->data as $k =>$v )
		{
			if ( is_string($v) || is_numeric($v) )
			{
				$data .= '$data[\''.$k.'\']=\'' . $v . '\'; ' ; 
			} else if ( is_bool ( $v ) )
			{
				$data .= '$data[\''.$k.'\']=\'' . ( $v === true ? 'true' : 'false' ) . '\'; ' ;
			}
		}
		$data .= ' ?>' ;
		return parent::write ( $data ) ;
	}
	
}














?>