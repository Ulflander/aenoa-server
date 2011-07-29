<?php

class AeFTPUpdate extends Ftp {
	
	
	private $_hasUpdate = false ;
	
	
	const REPO_URL = 'http://up.aenoa-systems.com/' ;
	
	function __construct () {
		parent::__construct ('ftp.aenoa-systems.com','aenoasys-upd','anonymous') ;
		
	}

	
}

?>