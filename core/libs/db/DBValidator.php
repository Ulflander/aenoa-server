<?php

class DBValidator {
	
	const EMAIL = '^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,4}$' ;
	
	const NOT_EMPTY = '.{1,}' ;
	
	const VALID_URL = '^(http|https|ftp)\:\/\/[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$' ;
	
	const URL= '^(http|https|ftp)\:\/\/';
	
	const VALID_LAT_LONG = '^[\-0-9]{1,3}[\.[0-9]{1,20}]{0,1},[\-0-9]{1,4}[\.[0-9]{1,20}]{0,1}$' ;
	
	const VALID_SIZE = '^[0-9]{1,5},[0-9]{1,5}$' ;
	
	const VALID_INT = '^[0-9]{1,5}$' ;
	
	const PASSWORD = '.{6,}' ;
}
?>