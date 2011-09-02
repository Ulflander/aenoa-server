<?php


class LoginService extends Service {
	
	
	function beforeService ()
	{
		if ( Config::get(App::USER_CORE_SYSTEM) !== true )
		{
			App::do500(_('Attempt to use Aenoa core user login service'), _('Core user login service is not available')) ;
		} else if ( Config::get(App::API_REQUIRE_KEY) !== true )
		{
			App::do500(_('Attempt to use Aenoa core user login service'), _('Core API keys management service is not available')) ;
		}
		
		$this->db = App::getDatabase() ;
	}
	
	
	function login ( $email, $pwdHash , $publicKey )
	{
		
		$key = $this->db->findFirst( 'ae_api_keys' , array('public'=> $publicKey ) ) ;
		
		if ( empty ( $key ) )
		{
			$this->protocol->setFailure('Public API key not valid');
			return ;
		}
		
		$dbuser = $this->db->findFirst ('ae_users', array ('email'=>$email) ) ;
		
		if ( empty ($dbuser) )
		{
			$this->protocol->setFailure('Invalid email');
			return ;
		}
		
		if ( sha1($key['private'] . $dbuser['password']) !== $pwdHash )
		{
			$this->protocol->setFailure('Private API authentication failed');
			return ;
		}
		
		$user = App::getUser() ;
		
		if ( $user->isLogged() && $user->getIdentifier() != $email )
		{
			$user->logout() ;
		}
		
		$res = $user->login ( $email , $dbuser['password'] ) ;
		
		if ( $user->isLogged() )
		{
			$this->protocol->addData('user', array (
				'dbid' => $user->getDatabaseId(),
				'user' => $user->getIdentifier(),
				'firstname' => $user->getFirstname(),
				'lastname' => $user->getFirstname(),
				'properties' => $user->getProperties(),
				'infos' => $user->getData(),
				'level' => $user->getLevel()
			) ) ;
		} else {
			$this->protocol->setFailure('Authentication failed');
		}
	}
	
	
	function logout ()
	{
		$user = App::getUser() ;
		
		if ( $user->isLogged() )
		{
			$user->logout() ;
		} else {
			$this->protocol->setFailure('User was not connected');
		}
	}
	
}


?>