<?php

if ( !isset ( $AUTOLOAD_PATHS ) )
{
	$AUTOLOAD_PATHS = array () ;
}

if ( !defined ( 'EOL' ) && !defined ( 'SOL' ) )  {
	if( strtoupper(substr(PHP_OS,0,3)=='WIN' )){
		define ( 'EOL' , "\r\n" ) ;
		define ( 'SOL' , "\n" ) ; 
	} else if( strtoupper(substr(PHP_OS,0,3)=='MAC' )){
		define ( 'EOL' , "\r" ) ;
		$eol="\r";
	}else{
		define ( 'EOL' , "\n" ) ;
	}
	if ( !defined ( 'SOL' ) ){
		define ( 'SOL', EOL ) ;
	}
}

/**
 * Transforms keys an array of data from simple keys to HTML Form field keys
 * 
 * Accept an array of data like this one
 * > array (
 * > 	'fieldname' => 'value'
 * > );
 * 
 * and convert it to 
 * > array (
 * > 	'database_id/table_name/fieldname' => 'value'
 * > );
 * 
 * @param string $databaseId Id of database
 * @param string $table Id of table
 * @param array $data Array of data
 * @return array An associative array with HTML Form field keys
 */
function keysToFormKeys ( $databaseId , $table , $data )
{
	$d = array () ;
	foreach ( $data as $k => $v )
	{
		$d[$databaseId.'/'.$table.'/'.$k] = $v ;
	}
	return $d ;
}


function url ()
{
	return Config::get(App::APP_URL) ;
}

function setTrailingSlash($str) {
	return unsetTrailingSlash($str) . '/' ;
}

function unsetTrailingSlash($str) {
	return rtrim($str, '/' );
}


function setTrailingDS($str) {
	return unsetTrailingSlash($str) . DIRECTORY_SEPARATOR ;
}

function unsetTrailingDS($str) {
	return rtrim($str, DIRECTORY_SEPARATOR );
}

function ake($key,&$array)
{
	if( !is_array($array) )
	{
		return false ;
	}
	if ( is_string($key) )
	{
		return array_key_exists($key,$array) ;
	} else if ( is_array($key ) )
	{
		while ( $k = array_pop($key) )
		{
			if ( !array_key_exists($k,$array) )
				return false;
		}
		return true ;
	}
	return false ;
}

function is_public_method ( $class_name, $method_name )
{
	$refl = new ReflectionMethod($class_name, $method_name) ;
	return $refl->isPublic();
}

function is_public_controller_method ( $class_name, $method_name )
{
	$refl = new ReflectionClass($class_name) ;
	if ( $refl->hasMethod($method_name) )
	{
		$method = $refl->getMethod($method_name) ;
		if ( $method->getDeclaringClass()->getName() != 'Controller' )
		{
			return $method->isPublic () ;
		}
	}
	
	return false ;
}

function array_keys_exists ( $keys = array () , $needle )
{
	foreach ( $keys as $key )
	{
		if ( array_key_exists( $key , $needle ) == false )
		{
			return false ;
		}
	}
	return true ;
}

if ( !function_exists ( 'set_memory_limit' ) )
{
	function set_memory_limit ( $limit = 256 ) 
	{
		$mem = ini_get ( 'memory_limit' ) ;
		if ( intval ( substr ( $mem , 0 , strlen ( $mem ) - 1 ) ) < $limit ) 
		{
			$limit = $limit . 'M' ;
		
			if ( @ini_set ( 'memory_limit' , $limit ) === $mem )
			{
				return true ;
			}
		} else {
			return true ;
		}
		
		return false ;
	}
}



function url2rdns_str ( $url )
{
	$_url  = str_replace(
		array ('http://','https://','ftp://'),
		array( '', '', '' ) ,
		$url
	);
	
	$_url = explode ('/',$_url);
	$domain = explode('.',array_shift($_url));
	
	$rdns = implode('_',array_reverse($domain)) ;
	
	foreach($_url as $d)
	{
		if ( $d != '' )
		{
			$rdns .= '_' . str_replace('.','_',$d);
		}
	}

	return $rdns ;
}



if ( function_exists( 'pr' ) == false )
{
	/**
	 * Alias of print_r
	 */
	function pr ( $expression, $return = false )
	{
		$str = '<pre>' . print_r ( $expression, true ) . '</pre><br />' . "\n\n";
		if ( $return )
		{
			return $str ;
		} else {
			echo $str ;
		}
	}
}


if ( function_exists( 'urlize' ) == false )
{
	/**
	 * urlize a string :
	 * - all European chars with accent are replaced with their equivalent whitout accent
	 * - strings authorized are letters, numbers and -
	 * - spaces are replaced by $separator char
	 *
	 * get a clean string without any special char
	 * @param $str String to urlize
	 * @return urlized string
	 */
	function urlize ( $str = null , $separator = '-' ) {
		if ( !is_null ( $str ) ) {
			return str_replace(' ', $separator ,trim(preg_replace('/[^a-z0-9\\'.$separator.'\s]+/', '' , strtolower( strtr(
				utf8_decode($str),
				utf8_decode('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ'),
							'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr'
			)))));
		}
		return '' ;
	}
}
if ( function_exists( 'tl_get' ) == false )
{
	$tl_rules = array () ;
	$tl__from = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
	$tl__to = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
	function tl_get ( $str = null , $separator = '-' ) {

		global $tl__to;
		global $tl__from;
		global $tl_rules;
		$str = mb_strtolower($str,'UTF8');
		if ( !is_null ( $str ) ) {
			foreach( $tl_rules as $rule => $replace )
			{
				$str = mb_ereg_replace('/'.$rule.'/',$replace, $str);
			}
			
			$from = preg_split('/(?<!^)(?!$)/u',$tl__from);
			$to = preg_split('/(?<!^)(?!$)/u',$tl__to);
			
			for ( $i = 0, $l = count($from) ; $i < $l ; $i ++ )
			{
				$str = str_replace($from[$i],$to[$i], $str);
			}
			
			return str_replace(' ', $separator ,trim(preg_replace('/[^a-z0-9\\'.$separator.'\s]+/', '' , $str )));
		}
		return '' ;
	}
	function tl_add ( $rules, $replaces )
	{
		global $tl__to;
		global $tl__from;
		$tl__from = $rules . $tl__from;
		$tl__to = $replaces . $tl__to ;
	}
	function tl_set ( $rule, $replace ) {
		$tl_rules[$rule] = $replace ;
	}
	
	
	function replace_unicode_escape_sequence($match) {
	    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
	}
	
	function unescape_unicode ( $str )
	{
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
	}
				
}


if ( !function_exists('version_comp') )
{
	/**
	 * Compare two sets of versions, where major/minor/etc. releases are separated by dots or hyphens.
	 * @param string $a The first version
	 * @param string $b The second version
	 * @return int 0 if both are equal, 1 if A > B, and -1 if A < B.  
	 */
	function version_comp($a, $b) 
	{ 
	    $a = explode('.', rtrim(str_replace(array('-','_','+'),array('.','.','.'),$a), ".0")); //Split version into pieces and remove trailing .0 
	    $b = explode(".", rtrim(str_replace(array('-','_','+'),array('.','.','.'),$b), ".0")); //Split version into pieces and remove trailing .0
	    foreach ($a as $k => $aVal) 
	    { //Iterate over each piece of A 
	        if (isset($b[$k])) 
	        { 
	    		while(strlen($aVal) < strlen($b[$k]) )
	    		{
	    			$aVal = intval($aVal) * 10 ;
	    		}
	    		while(strlen($b[$k]) < strlen($aVal) )
	    		{
	    			$b[$k] = intval($b[$k]) * 10 ;
	    		}
	    		//If B matches A to this depth, compare the values 
	            if (intval($aVal) > intval($b[$k])) return 1; //Return A > B 
	            else if (intval($aVal) < intval($b[$k])) return -1; //Return B > A 
	            //An equal result is inconclusive at this point 
	        } 
	        else 
	        { //If B does not match A to this depth, then A comes after B in sort order 
	            return 1; //so return A > B 
	        } 
	    } 
	    //At this point, we know that to the depth that A and B extend to, they are equivalent. 
	    //Either the loop ended because A is shorter than B, or both are equal. 
	    return (count($a) < count($b)) ? -1 : 0; 
	} 
}

/**
 * Convenient function for php cor ucfirst function
 *
 * @param $str String to capitalize
 * @return capitalized string
 */
if ( function_exists( 'u' ) == false )
{
	function u ( $str = null ) {
		return ucfirst($str);
	}
}


/**
 * Convenient function for php cor ucfirst function
 *
 * @param $str String to capitalize
 * @return capitalized string
 */
if ( function_exists( 'lcfirst' ) == false )
{
	function lcfirst ( $str = null ) {
		return strtolower(substr($str, 0, 1)).substr($str,1);
	}
}


/**
 * Replace chars with accent with their equivalent whitout accents
 *
 * get a clean string without any accent
 * @param $str String to clean
 * @return urlized string
 */
if ( function_exists( 'deaccent' ) == false )
{
	function deaccent ( $str = null ) {
		return utf8_decode(strtr($str,
				'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ',
				'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr'
			));
	}
}

/**
 * array_clean
 *
 * clean an array passed by reference of all row with an empty value
 *
 */

if ( !function_exists ( 'array_clean' ) )
{
	function array_clean ( &$array )
	{
		if ( !$array || !count ( $array ) )
		{
			$array = array () ; 
			return;
		}
		$res = array () ;
		foreach ( $array as &$item )
		{
			if ( !empty ( $item ) )
			{
				$res[] = $item ;
			}
		}
		$array = $res ;
	}
}


if ( !function_exists('beautify_json') ) 
{
	/**
	* Indents a flat JSON string to make it more human-readable.
	* 
	* Checkout http://recursive-design.com/blog/2008/03/11/format-json-with-php/
	*
	* @param string $json The original JSON string to process.
	*
	* @return string Indented version of the original JSON string.
	*/
	function beautify_json($json) {
	
		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = '  ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;
	
		for ($i=0; $i<=$strLen; $i++) {
	
			// Grab the next character in the string.
			$char = substr($json, $i, 1);
	
			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;
	
				// If this character is the end of an element,
				// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}
	
			// Add the character to the result string.
			$result .= $char;
	
			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}
	
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
	
			$prevChar = $char;
		}
	
		return $result;
	}
}

/**
 * Add a path in directories for autoload class PHP feature
 * 
 * Part of __autoload PHP feature. See PHP doc for documentation of __autoload.
 * 
 * @param String $path The path where PHP must search classes
 * @return 
 */
if ( !function_exists ( 'addAutoloadPath' ) )
{
	function addAutoloadPath ( $path ) 
	{
		global $AUTOLOAD_PATHS;
		array_push($AUTOLOAD_PATHS, $path) ;
	}
}

/**
 * __autoload PHP magic function implementation.
 * 
 * @param String $class_name The class name to load
 * @return 
 */
if ( !function_exists ( '__autoload' ) )
{
	function __autoload ( $className )
	{
		static $ext = '.php' ;
		
		global $AUTOLOAD_PATHS;
	
		foreach ( $AUTOLOAD_PATHS as $dir )
		{
			if ( is_file ( $addr = $dir . $className . $ext ) )
			{
				require_once $addr ;
				return true ;
			}
		}
		
		return false ;
	}
}

/**
 * Require a file named $className.
 * Will search in all autoloads directory.
 * Use addAutoloadPath function to add paths.
 * 
 * Part of __autoload PHP feature. See PHP doc for documentation of __autoload.
 * 
 * @param String $className Name of the class
 * @return boolean True if class file loaded, false otherwise
 */
if ( !function_exists ( 'loadclass' ) )
{
	function loadclass ( $className )
	{
		__autoload($className);
	}
}


function cmp($a, $b) {
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}

function icmp ($a, $b)
{
	return -cmp($a,$b);
}

/**
 * std2arr will transform recursively stdClass dynamic objects to array.
 * 
 * @param object $obj An stdClass instance
 * @return array An array containing data of stdClass.
 */
if ( function_exists( 'std2arr' ) == false )
{
	function std2arr ( $obj = null )
	{
		$arr = array () ;
		if ( is_object($obj) || is_array ( $obj ) )
		{
			foreach ( $obj as $k => $v )
			{
				if ( is_object( $v ) || is_array ( $v ) )
				{
					$arr[$k] = std2arr ( $v ) ;
				} else {
					$arr[$k] = $v ;
				}
			}
		}
		
		return $arr ;
	}
}
/**
 * Encode in utf8 all strings in an array recursively
 * 
 * @param object $obj A reference to the array or stdClass instance to encode
 * @return array The  reference to the array or stdClass with string values encoded
 */
if ( function_exists( 'utf8_encode_recursive' ) == false )
{
	function utf8_encode_recursive ( &$obj = null )
	{
		$arr = array () ;
		if ( is_object($obj) || is_array ( $obj ) )
		{
			foreach ( $obj as $k => $v )
			{
				if ( is_object( $v ) || is_array ( $v ) )
				{
					$arr[$k] = utf8_encode_recursive ( $v ) ;
				} else {
					$arr[$k] = ( is_string( $v ) ? utf8_encode ( $v) : $v ) ;
				}
			}
		}
		
		return $arr ;
	}
}


/**
 * it2space will replace \t chars by '&nbsp;&nbsp;&nbsp;&nbsp;'.
 * 
 * @param string $str The string to work on
 * @return The transformed string
 */
if ( function_exists( 'it2space' ) == false )
{
	function it2space ( $str = null , $spaces = '&nbsp;&nbsp;&nbsp;&nbsp;' )
	{
		if ( !is_null ( $str ) )
		{
			return str_replace(array ( '    ' , "\t"), array ( $spaces , $spaces ), $str) ;
		}
		
		return $str ;
	}
}


/**
 * nl2dec will replace \n chars by &#013;&#010;.
 * 
 * This is usefull for textarea content.
 *
 * @param string $str The string to work on
 * @return The transformed string
 */
if ( function_exists( 'nl2dec' ) == false )
{
	function nl2dec ( $str = null )
	{
		if ( !is_null ( $str ) )
		{
			return str_replace("\n", '&#013;&#010;', $str) ;
		}
		
		return $str ;
	}
}



/**
 * dec2n will replace &#013;&#010; chars by \n 
 *
 This is usefull for textarea content.
 *
 * @param string $str The string to work on
 * @return The transformed string
 */
if ( function_exists( 'dec2n' ) == false )
{
	function dec2n ( $str = null )
	{
		if ( !is_null ( $str ) )
		{
			return str_replace('&#013;&#010;', "\n", $str) ;
		}
		
		return $str ;
	}
}

/**
 * Returns the URL of this web application context
 * 
 * The first argument is an array of directories name where this functions.php file could be, 
 * and that we have to skip in order to retrieve the correct root URL.
 * 
 * For example, if your app root is http://www.example.com/my_web_app, 
 * and functions.php is in /home/web/example.com/www/my_web_app/include/in/directory/functions.php
 *
 * then you should skip the "include", "in" and "directory" folder names :
 * 
 * $my_web_app_root_url = retrieveContextURL ( array ( 'include', 'in', 'directory' ) ) ;
 * 
 * Use this variable as your root URL for your whole application:
 * when you will move your app from a domain to another one, you won't have to reconfigure the URLs.
 * 
 * @param object $dirsToSkip [optional] Skip these dirs names
 * 
 * @return The URL of your web app
 */
if ( function_exists ( 'retrieveContextURL' ) == false )
{
	function retrieveContextURL ( $dirsToSkip = array () )
	{
		if ( !isset ( $_SERVER['HTTPS'] ) || strtolower ( $_SERVER['HTTPS'] ) != 'on')
		{
			$prefix = 'http://' ;
		} else {
			$prefix = 'https://' ;
		}
		
		$url = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] ;
		$url = preg_split("/\//", $url, -1) ;
		do {
			unset( $url[count( $url )-1] ) ;
		} while ( in_array ( $url[count( $url )-1] , $dirsToSkip ) ) ;
		
		$url = implode( '/' , $url) . '/' ;
		
		return $prefix . $url ;
	}
}

if ( function_exists ( 'retrieveBasePath' ) == false )
{
	function retrieveBasePath ( $dirsToSkip = array () )
	{
		
		$url = $_SERVER['SCRIPT_NAME'] ;
		$url = preg_split("/\//", $url, -1) ;
		do {
			unset( $url[count( $url )-1] ) ;
		} while ( in_array ( $url[count( $url )-1] , $dirsToSkip ) ) ;
		
		return implode( '/' , $url) . '/'  ;
	}
}



if ( function_exists ( 'get_microtime' ) == false )
{
	function get_microtime ()
	{
		list ( $usec , $sec ) = explode ( " " , microtime() ) ;
		return ( ( float ) $usec + ( float ) $sec ) ;
	}
}


function humanize ($lowerCaseWord , $separator = '-' ) {
	return ucwords(str_replace($separator, ' ', $lowerCaseWord));
}

function camelize($lowerCaseWord , $separator = '-' ) {
	return str_replace(' ', '', ucwords(str_replace($separator, ' ', $lowerCaseWord)));
}

function uncamelize($camelCasedWord , $separator = '-' ) {
	return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '-\\1', $camelCasedWord));
}

function printArray ( $array , $indent = '' )
{
	$arr = array () ;
	$arr[] = 'array (' ;
	foreach ( $array as $k => $v )
	{
		$str = '';  
		switch ( true ) 
		{
			case is_numeric ( $k ):
				$str .= $k ;
				break;
			case is_string ( $k ):
				$str .= '\'' . $k . '\'' ;
		}
		
		$str .= ' => ' ;
		switch ( true ) 
		{
			case is_array ( $v ):
				$str .= printArray ( $v , $indent . "\t" ) ;
				break;
			case is_bool($v):
				$str .= ($v?'true':'false') ;
				break;
			case is_numeric ( $v ):
				$str .= $v ;
				break;
			case is_string ( $v ):
				$str .= '\'' . str_replace(array("\n","\r","\r\n"), array('','','') , $v) . '\'' ;
		}
		
		$arr[] = $indent. $str . ',' ;
	}
	$arr[] = $indent . ')' ;
	
	return implode ( "\n" , $arr ) ;
}


function arrayToList ( $array )
{
	$res[] = '<ul>';
	foreach ( $array as $k => $v )
	{
		if ( is_array ( $v ) )
		{
			$res[] = '<li><span class="col-3">' . _($k) . '</span></li>' ;
			$res[] = arrayToList($v) ;
		} else {
			$res[] = '<li><span class="col-3">' . _($k) . '</span><strong>'. $v . '</strong></li>' ;
		}
	}
	$res[] = '</ul>';
	return implode("\n",$res) ;
}

function array_merge_keep_structure($arr,$ins) {

    if(is_array($arr))
    {
        if(is_array($ins)) foreach($ins as $k=>$v)
        {
            if(isset($arr[$k])&&is_array($v)&&is_array($arr[$k]))
            {
                $arr[$k] = array_merge_keep_structure($arr[$k],$v);
            }
            else {
                // This is the new loop :)
                while (isset($arr[$k]))
                    $k++;
                $arr[$k] = $v;
            }
        }
    }
    elseif(!is_array($arr)&&(strlen($arr)==0||$arr==0))
    {
        $arr=$ins;
    }
    return($arr);
}

/**
 * Tests if the current application is in debug mode
 * 
 * @return boolean True if debug mode activated, false otherwise
 */
function debuggin ()
{
	return defined ('DEBUG') && DEBUG ;
}

function mysql_date ( $time )
{
	return date ( 'Y-m-d H:i:s' , $time ) ;
}

?>