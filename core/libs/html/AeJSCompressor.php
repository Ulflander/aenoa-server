<?php

class AeJSCompressor 
{
	
	/**
	 * Auth chars in a css string
	 * @var string
	 */
	public static $PATTERNS = array (
		array (
			'preg_replace',
			'/(\/\*[^@](.*?)\*\/)/s' ,
			'',
			'Comments removed'
		),
		array (
			'preg_replace',
			'/\t/' ,
			'',
			'Indentation removed'
		),
		array (
			'preg_replace',
			'/#([a-f0-9])\1([a-f0-9])\2([a-f0-9])\3/i' ,
			'#\1\2\3',
			'Color shortcuts created'
		),
	) ;
	
	
	private static $__report = array () ;
	
	public static function getReport ()
	{
		return self::$__report ;
	}
	
	public static function compress ( $fromfile, $tofile)
	{
		$f = new File ( $fromfile , false ) ;
		
		$f2 = new File ( $fromfile , true ) ;
		
		if ( $f->exists() && $f2->exists() )
		{
			$js = self::compressString ( $f->read() ) ;
			
			$f->close () ;
				
			if ( $f2->write () && $f2->close () )
			{
				pr($js);
				return true ;
			}
		}
		
		return false ;
		
	}


	public static function safeCompressString ( $jsString )
	{

		foreach ( self::$PATTERNS as $pattern )
		{
			// get method
			if ($pattern[0] == 'preg_replace' || $pattern[0] == 'preg_replace_callback' )
			{

				preg_match_all ( $pattern[1] , $jsString , $res ) ;

				self::$__report[] = $pattern[3] . ': <strong>' . count($res[0]) . '</strong>' ;

			} else {

				self::$__report[] = $pattern[3] . ': done' ;

			}

			$jsString = @$pattern[0] ( $pattern[1] , $pattern[2], $jsString ) ;

		}
		$result = array() ;
		$jsString = explode("\n",str_replace("\r","\n",$jsString)) ;

		foreach ( $jsString as $line )
		{

			$result[] = self::cleanInlineComments($line) ;
		}
		array_clean($result);
		return implode("\n",  $result) ;
	}
	
	
	public static function compressString ( $jsString )
	{
		foreach ( self::$PATTERNS as $pattern )
		{
			// get method
			if ($pattern[0] == 'preg_replace' || $pattern[0] == 'preg_replace_callback' ) 
			{
				
				preg_match_all ( $pattern[1] , $jsString , $res ) ;
				
				self::$__report[] = $pattern[3] . ': <strong>' . count($res[0]) . '</strong>' ;
				
			} else {
				
				self::$__report[] = $pattern[3] . ': done' ;
				
			}
			
			$jsString = @$pattern[0] ( $pattern[1] , $pattern[2], $jsString ) ;
			
		}
		
		$result = '' ;
		$last = '' ;
		$jsString = explode("\n",str_replace("\r","\n",$jsString)) ;
		
		$avoidLineChars = ';(){}[]=,?:+-/"\'&' ;
		
		foreach ( $jsString as &$line )
		{
			
			if ( strlen($line) == 0 ) continue;
			
			$line = trim ($line) ;
			
			$line = self::cleanInlineComments($line) ;
			
			if ( strlen($line) == 0 ) continue;
			
			if ( strpos( $avoidLineChars , substr( $line, strlen( $line ) - 1 , 1 ) ) !== false )
			{
				$last .= $line ; 
			} else {
				$result .= "\n". $last . $line ;
				$last = '' ; 
			}
		}
		
		$result .= $last ;
		
		return $result;
	}
	
	public static function stripComments ( $jsString )
	{
		
		foreach ( self::$PATTERNS as $pattern )
		{
			// get method
			if ($pattern[0] == 'preg_replace' || $pattern[0] == 'preg_replace_callback' ) 
			{
				
				preg_match_all ( $pattern[1] , $jsString , $res ) ;
				
				self::$__report[] = $pattern[3] . ': <strong>' . count($res[0]) . '</strong>' ;
				
			} else {
				
				self::$__report[] = $pattern[3] . ': done' ;
				
			}
			
			$jsString = @$pattern[0] ( $pattern[1] , $pattern[2], $jsString ) ;
			
		}
		
		$result = '' ;
		$jsString = explode("\n",str_replace("\r","\n",$jsString)) ;
		
		foreach ( $jsString as &$line )
		{
			$line = trim ($line) ;
			
			$line = self::cleanInlineComments($line) ;
			
			if ( strlen($line) == 0 ) continue;
			
			$result .= $line . "\n" ;
		}
		
		return $result;
	}
	
	// to strip inline comments we have to check they are not in strings. So, true parsing.
	private static function cleanInlineComments ( $line )
	{
		$_line = str_split($line);
		$inString = false ;
		$commentPos = false ;
		$i = 0 ;
		$l = count($_line) ;
		
		for ( $i ; $i < $l ; $i ++ )
		{
			$char = $_line[$i] ;
			if ( $inString !== false )
			{
				if ( $char == $inString && $_line[$i-1] == '\\' )
				{
					$inString = false ;
				}
			} else if ( $char == '"' || $char == '\'' ) 
			{
				$inString = $char ;
			} else {
				if ( $char == '/' && $i+1 < $l && $_line[$i+1] == '/' )
				{
					$commentPos =  $i ;
					break;
				}
			}
		}
		
		if ($commentPos !== false )
		{
			if ( $commentPos > 0 )
			{
				return substr($line, 0, $commentPos ) ;
			} else {
				return '' ;
			}
		}
		
		return $line ; 
		
	} 
	
	/**
	 * Returns the CSS content before the compression
	 */
	public static function getBefore ()
	{
		return self::$__tcss ;
	}
	
	/**
	 * Returns the CSS content length before the compression
	 */
	public static function getLenBefore ()
	{
		return self::$_before ;
	}

	/**
	 * Returns the CSS content length after the compression
	 */
	public static function getLenAfter ()
	{
		return self::$_after ;
	}
	
	/**
	 * @private
	 */
	private static $_before = 0 ;
	
	/**
	 * @private
	 */
	private static $_after = 0 ;
	
	/**
	 * @private
	 */
	private static $__tcss = '' ;
}

?>