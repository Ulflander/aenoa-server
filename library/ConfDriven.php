<?php


/**
 * Description of ConfDriven
 *
 * @see Route
 * @see I18n
 */
class ConfDriven extends AeObject {
	
	protected $file = '' ;
	
	protected $conf = array () ;
	
	
	function __construct ()
	{
		if ( !empty ( $this->file ) )
		{
			$f = new File($this->file, true);
			$conf = explode("\n", $f->read());
			$f->close();
			
			$this->parseConf( $conf ) ;
		}
	}
	
	/**
	 *
	 * @param type $values 
	 */
	protected function parseConf ( $values = array () )
	{
		foreach ($values as $val ) {
			if (strpos($val, '>') === false) {
				array_push ( $this->conf , $val ) ;
				continue;
			}

			$v = explode('>', $val);
			$this->conf[trim($v[0])] = trim($v[1]);
		}
	}
	
}

?>
