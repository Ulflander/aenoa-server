<?php


class SearchApproxTerm {
	
	
	/**
	 * Will search the most approximative (or the equal) string in an array of strings.
	 * 
	 * If a term is found, an array like this will be returned:
	 * $arr['query'] => the original query string
	 * $arr['result'] => a number between 100 and 1 (100 is not aproximative at all, 1 is equal
	 * $arr['term'] => the approximative term found in $array array
	 *  
	 * @param object $query A string to compare
	 * @param object $array An array of strings to compare
	 * @return null if no approximative term found,  
	 */
    static function search ( $query , $array ) 
	{
		if ( !is_string ( $query ) || !is_array ( $array ) || empty ( $array ) )
		{
			return;
		}
		
		$best = 100 ;
		$result = array () ;
		$result['query'] = $query ;
		
		
		foreach ( $array as $value )
		{
			$res = self::compare ( $query , $value ) ;
			if ( $best > $res )
			{
				$best = $res ;
				$result['result'] = $res ;
				$result['term'] = $value ;
			}
		}
		
		if ( $best == 100 )
		{
			return null ;
		}
		
		return $result ;
	}
	
	
	/**
	 * Compare two string and return a result between 1 and 100
	 * Result of 1 represents a strict equality
	 * Result of 100 represents a strict inequality
	 * 
	 * We can consider have a good approximation with a result between 1 and 1,6 
	 * 
	 * @param object $str1
	 * @param object $str2
	 * @return 
	 */
	static function compare ( $str1 , $str2 )
	{
		$str1 = strtolower( $str1 ) ;
		$str2 = strtolower( $str2 ) ;
		
		if ( $str1 == $str2 )
		{
			return 1;
		}
		
		$l1 = strlen ( $str1 ) ;
		$l2 = strlen ( $str2 ) ;
		
		if ( $l1 < 3 || $l2 < 3 )
		{
			return 100;
		}
		
		$l = ($l1 > $l2 ? $l1 : $l2 ) ;
		$lx = ($l1 > $l2 ? $l1 - $l2 : $l2 - $l1 ) ;

		
		// Same letters count
		$c = 0 ;
		
		// Letters before count
		$b = 0 ;
		
		// Letters after count
		$a = 0 ;
		
		for ( $i = 0 ; $i < $l ; $i ++ )
		{
			if ( $i < $l1 )
			{
				$letter = $str1[$i] ;
			} else {
				break;
			}
			
			// Same position
			if ( $i < $l2 && $letter == $str2[$i] ) 
			{
				$c += 1 ;
			// Position + 1
			} else if ( $l2 > $i + 1 && $letter == $str2[$i+1] )
			{
				$b += 0.75 ;
			// Position - 1
			} else if ( $i > 0 && $i <= $l2 && $letter == $str2[$i-1] )
			{
				$a += 0.75 ;
			// Position + 2
			} else if ( $l2 > $i + 2 && $letter == $str2[$i+2] )
			{
				$b += 0.50 ;
			// Position - 2
			} else if ( $i > 1 && $i <= $l2 && $letter == $str2[$i-2] )
			{
				$a += 0.40 ;
			// Position + 3
			} else if ( $l2 > $i + 3 && $letter == $str2[$i+3] )
			{
				$b += 0.30 ;
			// Position - 3
			} else if ( $i > 2 && $i <= $l2 && $letter == $str2[$i-3] )
			{
				$a += 0.15 ;
			}
		}
		
		$c = ($c+$a+$b) - $lx / 2 ;
		
		$i = @( $l1 / $c ) ;
		
		if ( $c < 1 )
		{
			return 100;
		} else {
			return $i ;
		}
	}
	
}
?>