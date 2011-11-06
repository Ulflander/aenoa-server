<?php



class AenoaRights {
	
	private static $_rights = array () ;
	
	static function setRights ( $rights )
	{
		if ( empty( self::$_rights ) && App::getInitialized () == false )
		{
			self::$_rights = $rights ;
		}
	}
	
	static function hasRightsOnQuery ( $query = null )
	{
		if ( empty( self::$_rights ) )
		{
			return true ;
		}
		
		if ( is_null( $query ) )
		{
			$query = App::getQuery() ;
		}
		
		$user = &App::$session->getUser() ;
		
		if ( array_key_exists( $query, self::$_rights ) )
		{
			if ( !self::_checkGroup( $user, self::$_rights[$query] ) )
			{
				return self::_checkUser ( $user, self::$_rights[$query] ) ;
			}
		}
		
		foreach ( self::$_rights as $q => &$rights )
		{
			preg_match_all('|'. str_replace('\\*','[a-z0-9\_\/]{1,}',preg_quote($q)) .'|i' , $query, $m ) ;
			if ( $m && !empty($m[0]) )
			{
				if ( $q == '*' && ( self::_checkGroup( $user, $rights ) || self::_checkUser ( $user, $rights ) ) )
				{
					return true ;
				} else 
				{
					return ( self::_checkGroup( $user, $rights ) && self::_checkUser ( $user, $rights ) ) ;
				}
			}
		}
		
		return false ;
	}
	
	private static function _checkGroup ( User &$user, &$rights )
	{
		if ( !array_key_exists('groups', $rights ) )
		{
			return false ;
		} else if ( $rights['groups'] == 'all' )
		{
			return true ;
		}
		
		return !is_null($user->getGroup()) && in_array ( $user->getLevel() , explode(',', $rights['groups'] ) ) ;
	}
	
	private static function _checkUser ( User &$user, $rights )
	{
		if ( !array_key_exists('users', $rights ) || $rights['users'] == 'none' )
		{
			return false ;
		} else if ( $rights['users'] == 'all' )
		{
			return true ;
		}
		
		return !is_null($user->getIdentifier()) && in_array ( $user->getIdentifier() , explode(',', $rights['users'] ) ) ;
	}
}

?>