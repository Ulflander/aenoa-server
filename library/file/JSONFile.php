<?php


class JSONFile extends File {
	
	
    function __construct( $filepath , $create = false , $chmod = 0777 )
	{
		parent::__construct ( $filepath , $create , $chmod ) ;
	}
	
	
	function read ()
	{
		$content = &parent::read ();
		return json_decode( &$content, true ) ;
	}
	
	function readUndecoded () 
	{
		return parent::read () ;
	}
	
	function write ( &$array )
	{
		$res = parent::write ( 
			json_encode ( $array )
		 ) ;
		return $res ;
	}
	
}
?>