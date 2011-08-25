<?php

class UserCoreController extends Controller{
	
	private $user ;
	
	/**
	 * In hours
	 */
	const CONFIRMATIONS_VALIDITY = 24 ;
	
	function beforeAction( $action )
	{
		
		$this->user = App::getUser() ;
		
		if ( Config::get(App::USER_CORE_SYSTEM) !== true )
		{
			App::do404 ( 'This is not the right gateway for user management.' ) ;
		}
		
		$this->view->useLayout = true ;
		
		$this->view->appendToTitle ( _('Users') ) ;
	}
	
	
	/**
	 * Change group of a user
	 * @param $user_id
	 */
	function group ( $user_id )
	{
		$userObj = App::getUser() ;
		
		if ( $userObj->getTrueLevel() > 0 )
		{
			App::do401 ('Level not valid') ; 
		}
		
		$this->view->layoutName = 'layout-backend' ;
		
		$user = $this->db->find('ae_users', $user_id ) ;
		
		if ( empty($user) )
		{
			App::do404 ('User not found') ;
		}
		
		$done = false ;
		$groups = $this->db->findAll('ae_groups') ;
		
		if ( !empty ( $this->data ) )
		{
			if ( ake('user/group', $this->data) )
			{
				$g = $this->data['user/group'] ;
				
				foreach ( $groups as $group )
				{
					if ( $group['id'] == $g )
					{
						$g = $group ;
						break;
					}
				}
				
				if ( is_array($g) && $g['id'] != $suer['group'] && $this->db->edit('ae_users',$user_id,array('group'=>$g['id'])) )
				{
					$done = true ;
				
					$mailer = new AeMail () ;
					if ( $mailer->sendThis (
						array ( 
							'to' => $user['email'],
							'subject' => sprintf(_('[%s] %s group information'), Config::get(App::APP_NAME), $g['label'] ) ,
							'template' => array (
								'file'=>'email'.DS.'user-core'.DS.'group-edited.thtml',
								'vars'=> array (
									'firstname' => $user['firstname'],
									'lastname' => $user['lastname'],
									'group' => $g['label'] 
								)
							) ,
						)
					) )
					{
						$user['group'] = $g['id'] ;
						$this->addResponse(sprintf(_('Group has been successfully changed, and an email has been sent to %s to warn this person about the group edition.'), $user['email'])) ;
					} else {
						App::do500('Sending mail failure');
					}
				}
				
			}
			
			if ( !$done )
			{
				$this->addResponse(_('Group not valid. Please retry.'), self::RESPONSE_ERROR ) ;
			}
		}
	
		foreach ( $groups as $group )
		{
			if ( $group['id'] == $user['group'] )
			{
				$this->addResponse(sprintf(_('User <strong>%s</strong>, also known as <strong>%s %s</strong>, is currently in <strong>%s</strong> group '),$user['email'],$user['firstname'],$user['lastname'],$group['label']) , self::RESPONSE_INFO ) ;
				break;
			}
		}
		
		
		$this->view->set('done', $done);
		$this->view->set('user',$user);
		$this->view->set('groups',$groups);
		
	}
	
	function fake ()
	{
		$userObj = App::getUser() ;
		
		if ( $userObj->getTrueLevel() > 0 )
		{
			App::do401 ('Level not valid') ; 
		}
		
		if ( !empty($this->data) && ake('level',$this->data) && $this->data['level'] >= 0 && $this->data['level'] <= 100 )
		{
			$userObj->setFakeLevel($this->data['level']);
		} else {
			$userObj->unsetFakeLevel();
		}
		
		App::redirectGlobal(url());
	}

	function login ()
	{
		
		$this->view->appendToTitle ( _('Login') ) ;
		$this->view->set('login_closed', false);
		
		$userObj = App::getUser() ;
		$redirect = '' ;
		
		if ( $userObj->isLogged () )
		{
			$this->addResponse(sprintf(_('You are yet logged on %s.'), Config::get(App::APP_NAME) ) , self::RESPONSE_ERROR ) ;
			$this->view->set('login_closed', true);
		}
		
	
		if ( !empty ( $this->data ) )
		{
			if ( $this->validateInputs ( array (
				'usercore/login/email' => DBValidator::EMAIL,
				'usercore/login/password' => DBValidator::PASSWORD
			) ) === true )
			{
				$userObj->login ( $this->data['usercore/login/email'], sha1($this->data['usercore/login/password'] ));
				
				
				
				if ( $userObj->isLogged () )
				{
					if ( App::getSession()->has('redirect') )
					{
						$redirect = App::getSession()->getAndDestroy('redirect') ;
					}
					
					/*
						Hook: UserCoreLogin
						  	
						This hook is dispatched when a user has logged into the system.
							
						Parameters:
							$args[0] - [int] user id
							$args[1] - [string] user email
							
					*/
					new Hook ('UserCoreLogin', $userObj->getDatabaseId() , $userObj->getIdentifier() ) ;
					
					$this->addResponse(sprintf(_('Your are logged into %s'), Config::get(App::APP_NAME) ) , self::RESPONSE_SUCCESS ) ;
					
					/*
					if ( $userObj->isGod () )
					{
						$ftp = new AeFTPUpdate() ;
						$ftp->timeout = 2 ;
						$versions = new AeVersions() ; 
						$packagesToUpdate = $versions->hasUpdates ( $ftp->ls () ) ;
						if(!empty($packagesToUpdate))
						{
							App::$session->set('Server.hasUpdate',true);
						}
					}
					*/
					
					if ( $redirect != 'logout' && $redirect != 'logout-in' )
					{
						App::redirectGlobal(url().$redirect);
					} else {
						App::redirectGlobal(url());
					}
					
					$this->addResponse(sprintf(_('Welcome on %s'), Config::get(App::APP_NAME) ) , self::RESPONSE_SUCCESS ) ;
					
				} else{
					$this->addResponse(sprintf(_('Your identifiers are not valid. Please retry.'), Config::get(App::APP_NAME) ) , self::RESPONSE_ERROR ) ;
				}
				
			} else {
				$this->addResponse(sprintf(_('Your identifiers are not valid. Please retry.'), Config::get(App::APP_NAME) ) , self::RESPONSE_ERROR ) ;
			}
			
			if ( array_key_exists( 'usercore/login/email', $this->data ) )
			{
				$this->view->set ('UserCoreEmail', $this->data['usercore/login/email'] ) ;
			}
		}
	}
	
	function logout ()
	{
		
		$userObj = App::getUser() ;
		
		if ( $userObj->isLogged () )
		{
			
			/*
				 Hook: UserCoreLogout
				  	
				This hook is dispatched when a user is about to log out the system.
					
				Parameters:
					$args[0] - [int] user id
					$args[1] - [string] user email
				
			*/
			new Hook ('UserCoreLogout', $userObj->getDatabaseId() , $userObj->getIdentifier() ) ;
					
			$userObj->logout ();
			
			App::redirectGlobal(url().'user-core/logged-out');
		} else {
			App::do401 ('Was not connected');
		}
	}
	
	function logoutIn ()
	{
		
		$userObj = App::getUser() ;
		
		if ( $userObj->isLogged () )
		{
			new Hook ('UserCoreLogout' ) ;
					
			$userObj->logout ();
			
			$this->runAction('login');
		} else {
			App::redirectGlobal(url());
		}
	}
	
	function account ()
	{
		User::requireLogged () ;
		
		$data = App::getUser()->getData() ;
		
		if ( ake('id',$data) )
		{
			unset($data['id']) ;
		}
		if ( ake('user_id', $data) )
		{
			unset($data['user_id']) ;
		}
		
		unset ($data['created']);
		unset ($data['updated']);
		
		$this->view->set('data', $this->db->keysToLabel('ae_users_info' , $data ) ) ;
	}
	
	function password ()
	{
		User::requireLogged () ;
		
		$user = App::getUser() ;
		
		if ( !empty ( $this->data ) )
		{
			$this->view->set('done', true); 
			
			$confirmId = $this->generateConfirmationCode('passwordEdit' , $user->getIdentifier () ) ;
			$link = url() .'user-core/password-confirm/' . $confirmId ;
			
			$mailer = new AeMail () ;
			if ( $mailer->sendThis (
				array ( 
					'to' => $user->getIdentifier () ,
					'subject' => sprintf(_('[%s] Confirm you want to change your password'), Config::get(App::APP_NAME)),
					'template' => array (
						'file'=>'email'.DS.'user-core'.DS.'new-password-confirm.thtml',
						'vars'=> array (
							'firstname' => $user->getFirstname() ,
							'lastname' => $user->getLastname() ,
							'link' => $link, 
							'expiry' => self::CONFIRMATIONS_VALIDITY 
						)
					) ,
				)
			) )
			{
				$this->view->set('email',$user->getIdentifier ());
			} else {
				App::do500('Sending mail failure');
			}
		} else {
			$this->view->set('done', false);
		}
	}
	
	function passwordConfirm ( $confirmationid = null )
	{
		User::requireLogged () ;
	
		$user = App::getUser() ;
				
		if ( is_null ( $confirmationid ) )
		{
			App::do401 ( 'Confirmation code not provided' ) ;
		}
		
		$this->cleanExpiredConfirmationCodes () ;
		
		$confirmation = $this->db->findFirst ( 'ae_confirmations' , array ( 'hash' => $confirmationid ) ) ;
		
		if ( empty($confirmation) || $confirmation['user'] != $user->getIdentifier () )
		{
			App::do401 ( 'Bad confirmation code' ) ;
		}
		
		$this->view->set ( 'hash', $confirmationid ) ;
		$this->view->set('done', false);
		
		if ( !empty($this->data) )
		{
			$passhash = sha1($this->data['usercore/identifier/password']) ;
		
			if ( !ake( 'usercore/identifier/password', $this->data ) || !$user->recheckPassword( $passhash ) )
			{
				$this->addResponse(sprintf(_('Please provide your old password.') ) , self::RESPONSE_ERROR ) ;
				return ;
			}
			
			if ( $this->validateInputs ( array (
					'usercore/identifier/new_password' => DBValidator::NOT_EMPTY,
					'usercore/identifier/new_password_confirm' => DBValidator::NOT_EMPTY,
				) ) === true && $this->data['usercore/identifier/new_password'] == $this->data['usercore/identifier/new_password_confirm'] )
			{
				$data = array ( 'password' => $this->data['usercore/identifier/new_password'] ) ;
				if ( $this->db->edit ('ae_users', $user->getDatabaseId() , $data ) )
				{
				
					$mailer = new AeMail () ;
					if ( $mailer->sendThis (
						array ( 
							'to' =>$user->getIdentifier ()  ,
							'subject' => sprintf(_('[%s] New password edited'), Config::get(App::APP_NAME)),
							'template' => array (
								'file'=>'email'.DS.'user-core'.DS.'new-password-edited.thtml',
								'vars'=> array (
									'firstname' => $user->getFirstname() ,
									'lastname' => $user->getLastname() ,
									'password' => $this->data['usercore/identifier/new_password']
								)
							) ,
						)
					) )
					{		
						
						/*
							Hook: UserCorePasswordEdited
							  	
							This hook is dispatched when a user has changed his password
								
							Parameters:
								$args[0] - [int] user id
								$args[1] - [string] user email
						
						*/
						new Hook ('UserCorePasswordEdited', $user->getDatabaseId() , $user->getIdentifier() ) ;
						
								
						$this->addResponse(sprintf(_('Your password has been changed. An email has been sent to %s, containing your new password.'), $user->getIdentifier() ) , self::RESPONSE_SUCCESS ) ;
						$this->view->set('done', true);
					} else {
						App::do500('Sending mail failure');
					}
				} else {
					App::do500('Saving new password failure');
				}
			} else {
				$this->addResponse(sprintf(_('The new password must be valid and confirmed.') ) , self::RESPONSE_ERROR ) ;
			}
			
		}
	}
	
	function identifier ()
	{
		User::requireLogged () ;
		
		if ( !empty($this->data) )
		{
			
			if ( !empty ( $this->data ) &&$this->validateInputs ( array (
					'usercore/identifier/email' => DBValidator::EMAIL,
					'usercore/identifier/email_confirm' => DBValidator::EMAIL,
				) ) === true && $this->data['usercore/identifier/email'] == $this->data['usercore/identifier/email_confirm'] )
			{
				$user = App::getUser() ;
				
				if ( $user->getIdentifier () == $this->data['usercore/identifier/email'] )
				{
					$this->addResponse(sprintf(_('This is yet your email address. Do you really want to change your email address for %s ?'), Config::get(App::APP_NAME) ) , self::RESPONSE_WARNING ) ;
				} else {
			
					$confirmId = $this->generateConfirmationCode('identifierChange' , $this->data['usercore/identifier/email'] ) ;
					$link = url() .'user-core/confirm-identifier/' . $confirmId ;
					
					$mailer = new AeMail () ;
					if ( $mailer->sendThis (
						array ( 
							'to' => $this->data['usercore/identifier/email']  ,
							'subject' => sprintf(_('[%s] Confirm your new email address'), Config::get(App::APP_NAME)),
							'template' => array (
								'file'=>'email'.DS.'user-core'.DS.'new-identifier-confirm.thtml',
								'vars'=> array (
									'firstname' => $user->getFirstname() ,
									'lastname' => $user->getLastname() ,
									'link' => $link, 
									'expiry' => self::CONFIRMATIONS_VALIDITY 
								)
							) ,
						)
					) )
					{		
						$this->createViewFromAction('identifierEmailSent');
						$this->view->set('email',$this->data['usercore/identifier/email']);
					} else {
						App::do500('Sending mail failure');
					}
				}
			} else {
				$this->addResponse(sprintf(_('You have to provide a valid email address, and you have to confirm it.') ) , self::RESPONSE_ERROR ) ;
			}
			
		}
	}
	
	function profile ()
	{
		User::requireLogged () ;
	
		$structure = App::getDatabase()->getTableStructure('ae_users_info') ;
		
		$user = App::getUser() ;
		
		$user->reloadInfos() ;
			
		foreach ( $structure as $k => $v )
		{
			if ( $v['name'] == 'created' || $v['name'] == 'updated' || $v['name'] == 'user_id')
			{
				unset ($structure[$k]);
			}
		}
		
		$user_info = $user->getData() ;
		
		$this->view->set('structure', $structure);
		
		$this->view->set ('data', $user_info ) ;
		
		if ( !empty( $this->data ) )
		{
			
			$controller = Controller::launchController ( 'Database' , 'edit' , $user->getDatabaseId() , array (
				'databaseID' => 'main',
				'table' => 'ae_users',
				'avoidRender' => true
			), array ( 'ae_users_info' ) , false );
			
			if ( $controller->RESTResult == false )
			{
				$user->reloadInfos() ;
			
				$this->responses = $controller->getResponses() ;

				$this->view->set ('data', $controller->output);
			} else {
				$this->addResponse(_('Your profile has been updated'));
			}
			
			
		}
	}
	
	function loggedOut ()
	{
		$this->view->render () ;
		
		$this->view->redirect ( url() , 3000 ) ;
		
		return;
	}
	
	function register ()
	{
		$this->view->appendToTitle ( _('Registration') ) ;
	
		if ( Config::get ( App::USER_REGISTER_AUTH ) !== true )
		{
			App::do403 ( 'Registration closed' ) ;
		}
		
		if ( !is_null( App::$session->get('registrationEmail') ) )
		{
			$this->runAction ( 'registerInfo' ) ;
			return;
		}
	
	
	
		if ( !empty ( $this->data ) &&$this->validateInputs ( array (
				'usercore/register/email' => DBValidator::EMAIL
			) ) === true )
		{
			
			App::checkForBot( $this, App::getQuery () ) ;
			
			if ( $this->db->findFirst('ae_users', array ('email' => $this->data['usercore/register/email']) ) )
			{
				$this->addResponse(sprintf(_('This address is yet registered in the system.') ) , self::RESPONSE_ERROR ) ;
			} else {
				$this->addResponse(sprintf(_('Your email address has been accepted. Let\'s continue registration.') ) , self::RESPONSE_SUCCESS ) ;
				
				App::$session->set('registrationEmail',$this->data['usercore/register/email']);
				
				App::$session->set('registration',1);
				
				$this->data = array () ;
				
				$this->runAction ( 'registerInfo' ) ;
				
			}
			
			
		}
		
	}
	
	
	function registerInfo ()
	{
		if ( Config::get ( App::USER_REGISTER_AUTH ) !== true )
		{
			App::do403 ( 'Registration closed' ) ;
		}
		
		if ( App::$session->get('registration') !== 1 || is_null( App::$session->get('registrationEmail') ) )
		{
			App::do401 ( 'Registration process broken' ) ;	
		}
	
		
		if ( !empty ( $this->data ) )
		{
			if ( $this->validateInputs ( array (
				'usercore/register/firstname' => DBValidator::NOT_EMPTY,
				'usercore/register/lastname' => DBValidator::NOT_EMPTY,
			) ) === true ) {
				
				$group = $this->db->findFirst('ae_groups',array('level'=>99)) ;
				
				if ( $this->db->add ( 'ae_users', array (
					'email' => App::$session->get('registrationEmail'),
					'firstname' => $this->data['usercore/register/firstname'] ,
					'lastname' => $this->data['usercore/register/lastname']  ,
					'group' => $group['id'] 
				) ) == false )
				{
					App::do401('Saving registration data failure');
				}
				
				$confirmId = $this->generateConfirmationCode('register' , App::$session->get('registrationEmail') ) ;
				$link = url() .'user-core/confirm-registration/' . $confirmId ;
				$denylink = url() .'user-core/deny-registration/' . $confirmId ;
				
				$mailer = new AeMail () ;
				
				
			
				if ( $mailer->sendThis (
					array ( 
						'to' => App::$session->get('registrationEmail')  ,
						'subject' => sprintf(_('[%s] Confirm your registration'), Config::get(App::APP_NAME)),
						'template' => array (
							'file'=>'email'.DS.'user-core'.DS.'register-confirm.thtml',
							'vars'=> array (
								'firstname' => $this->data['usercore/register/firstname'] ,
								'lastname' => $this->data['usercore/register/lastname'] ,
								'link' => $link, 
								'denylink' => $denylink,
								'expiry' => self::CONFIRMATIONS_VALIDITY 
							)
						) ,
					)
				) )
				{		
					App::$session->set('registration',2);
						
					$this->runAction('registerDone') ;
				} else {
					App::do500('Sending mail failure');
				}
				
			}
		}
	}
	
	
	function registerDone ()
	{
		if ( Config::get ( App::USER_REGISTER_AUTH ) !== true )
		{
			App::do403 ( 'Registration closed' ) ;
		}
		
		if ( App::$session->get('registration') !== 2 || is_null( App::$session->get('registrationEmail') ) )
		{
			App::do401 ( 'Registration process broken' ) ;	
		}
	
		$this->addResponse(sprintf(_('Your are now almost registered on %s'), Config::get(App::APP_NAME) ) , self::RESPONSE_SUCCESS ) ;
					
		$this->view->set('email',App::$session->get('registrationEmail'));
		
		App::$session->uset('registration') ;
		App::$session->uset('registrationEmail') ;
	}

	
	function denyRegistration ( $confirmationid = null )
	{
		if ( Config::get ( App::USER_REGISTER_AUTH ) !== true )
		{
			App::do403 ( 'Registration closed' ) ;
		}
	
		if ( is_null ( $confirmationid ) )
		{
			App::do401 ( 'Confirmation code not provided' ) ;
		}
		
		$this->cleanExpiredConfirmationCodes () ;
		
		$confirmation = $this->db->findFirst ( 'ae_confirmations' , array ( 'hash' => $confirmationid ) ) ;
		
		if ( empty($confirmation) )
		{
			App::do401 ( 'Bad confirmation code' ) ;
		}
		
		$user = $this->db->findFirst ( 'ae_users' , array ( 'email' => $confirmation['user'] ) ) ;
		
		if ( empty($user) )
		{
			App::do401 ( 'Registration not denied' ) ;
		}
		
		$this->view->set ( 'hash', $confirmationid ) ;
		$this->view->set ( 'email', $confirmation['user'] ) ;
		
		$error = false ;
		
		if ( $this->db->deleteAll ( 'ae_confirmations' , array ( 'user' => $confirmation['user'] ) )
			&& $this->db->deleteAll ( 'ae_users' , array ( 'email' => $confirmation['user'] ) ) )
		{
			$mailer = new AeMail () ;
			if ( $mailer->sendThis (
				array ( 
					'to' => $confirmation['user']  ,
					'subject' => sprintf(_('[%s] Registration denied'), Config::get(App::APP_NAME)),
					'template' => array (
						'file'=>'email'.DS.'user-core'.DS.'register-denied.thtml',
						'vars'=> array (
							'firstname' => $user['firstname'] ,
							'lastname' => $user['lastname']
						)
					) ,
				)
			) ) 
			{
				$this->cleanConfirmationCode ( $confirmationid ) ;
				$this->addResponse(_('You denied registration.'), self::RESPONSE_SUCCESS ) ;
				$this->view->redirect(url() .'user-core/register', 10000) ;
				return;
			} else {
				App::do500('Mailer failure',$this);
			}
		} else {
			App::do500('Database failure',$this);
		}
		
		
	}
	
	function confirmRegistration ( $confirmationid = null  )
	{
		if ( Config::get ( App::USER_REGISTER_AUTH ) !== true )
		{
			App::do403 ( 'Registration closed' ) ;
		}
		
		if ( is_null ( $confirmationid ) )
		{
			App::do401 ( 'Confirmation code not provided' ) ;
		}
		
		$this->cleanExpiredConfirmationCodes () ;
		
		$confirmation = $this->db->findFirst ( 'ae_confirmations' , array ( 'hash' => $confirmationid ) ) ;
		
		if ( empty($confirmation) )
		{
			App::do401 ( 'Bad confirmation code' ) ;
		}
		
		$this->view->set ( 'hash', $confirmationid ) ;
		
		$error = false ;
		if ( !empty ( $this->data ) )
		{
			if ( $this->validateInputs ( array (
				'usercore/confirm/email' => DBValidator::EMAIL,
			) ) === true )
			{
				
				$user = $this->db->findFirst ( 'ae_users' , array ( 'email' => $this->data['usercore/confirm/email'] ) ) ;
				
				if ( !empty($user) )
				{
					if ( $this->data['usercore/confirm/email'] == $confirmation['user'] )
					{
						$clearPassword = User::getClearNewPassword () ;
						
						if ( $this->db->edit('ae_users', $user['id'], array ( 'password' => $clearPassword ) ) ) 
						{
							$mailer = new AeMail () ;
							
							$link = url() . 'user-core/login' ;
							
							if ( $mailer->sendThis (
								array ( 
									'to' => $confirmation['user']  ,
									'subject' => sprintf(_('[%s] Registration confirmed'), Config::get(App::APP_NAME)),
									'template' => array (
										'file'=>'email'.DS.'user-core'.DS.'register-confirmed.thtml',
										'vars'=> array (
											'firstname' => $user['firstname'] ,
											'lastname' => $user['lastname'] ,
											'email' => $user['email'] ,
											'password' => $clearPassword ,
											'link' => $link
										)
									) ,
								)
							) ) 
							{
								/*
									Hook: UserCoreRegistered
									
									This hook is dispatched when a new user has confirmed his registration into system.
									
									Parameters:
										$args[0] - [int] user id
										$args[1] - [string] user email
									
								 */
								new Hook ('UserCoreRegistered' , $user['id'] , $user['email'] ) ;
								
								$this->cleanConfirmationCode ( $confirmationid ) ;
								$this->addResponse(_('Your registration has been confirmed.<br />Your password has been sent to you by email.'), self::RESPONSE_SUCCESS ) ;
								$this->data = array () ;
								$this->runAction('login') ;
								return;
							} else {
								App::do500('Mailer failure');
							}
						}
					}
				}
			}
			
			$this->addResponse(sprintf(_('This address is not valid. Please retry.') ) , self::RESPONSE_ERROR ) ;
		}
	}
	
	
	function passwordReset ()
	{
		$this->view->appendToTitle ( _('Password reset') ) ;
		
		if ( !empty ( $this->data ) )
		{
			App::checkForBot( $this, App::getQuery () ) ;
				
			if ( $this->validateInputs ( array (
				'usercore/passwordReset/email' => DBValidator::EMAIL
			) ) == true ) {
				
				$conf = $this->db->findFirst('ae_confirmations', array ('user' => $this->data['usercore/passwordReset/email']) )  ;
				
				if ( !empty ( $conf ) )
				{
					if ( $conf['action'] == 'passreset' )
					{
						$this->cleanConfirmationCode($conf['hash']) ;
						$this->addResponse(sprintf(_('On older password reset code related to your email address has been removed.') ) , self::RESPONSE_WARNING ) ;
					} else if ( $conf['action'] == 'register' )
					{
						$this->addResponse(sprintf(_('Your account is not activated now. Please confirm your account email address before reseting your password.') ) , self::RESPONSE_ERROR ) ;
						return ;
					}
				}
				
				
				$user = $this->db->findFirst('ae_users', array ('email' => $this->data['usercore/passwordReset/email']) )  ;
			
				if ( empty($user) )
				{
					$this->addResponse(sprintf(_('This address is not registered in our database.') ) , self::RESPONSE_ERROR ) ;
				} else {
					
					
					$confirmId = $this->generateConfirmationCode('passreset' , $user['email']) ;
					$link = url() .'user-core/password-reset-confirm/' . $confirmId ;
					$denylink = url() .'user-core/password-reset-deny/' . $confirmId ;
					
					$mailer = new AeMail () ;
					
					
				
					if ( $mailer->sendThis (
						array ( 
							'to' => $user['email'] ,
							'subject' => sprintf(_('[%s] Confirm password reset process'), Config::get(App::APP_NAME)),
							'template' => array (
								'file'=>'email'.DS.'user-core'.DS.'password-reset-confirm.thtml',
								'vars'=> array (
									'firstname' => $user['firstname'] ,
									'lastname' => $user['lastname'] ,
									'link' => $link, 
									'denylink' => $denylink,
									'expiry' => self::CONFIRMATIONS_VALIDITY 
								)
							) ,
						)
					) )
					{		
						$this->addResponse(sprintf(_('Your email address has been accepted.') ) , self::RESPONSE_SUCCESS ) ;
					
						$this->createViewFromAction('passwordResetSent') ;
						
						$this->view->set('email', $user['email'] ) ;
					} else {
						App::do500('Sending mail failure');
					}
					
				}
			}
			
		}
	}

	function passwordResetConfirm ( $confirmationid )
	{
		if ( is_null ( $confirmationid ) )
		{
			App::do401 ( 'Confirmation code not provided' ) ;
		}
		
		$this->cleanExpiredConfirmationCodes () ;
		
		$confirmation = $this->db->findFirst ( 'ae_confirmations' , array ( 'hash' => $confirmationid ) ) ;
		
		if ( empty($confirmation) )
		{
			App::do401 ( 'Bad confirmation code' ) ;
		}
		
		$this->view->set ( 'hash', $confirmationid ) ;
		
		$error = false ;
		if ( !empty ( $this->data ) )
		{
			if ( $this->validateInputs ( array (
				'usercore/confirm/email' => DBValidator::EMAIL,
			) ) === true )
			{
				
				$user = $this->db->findFirst ( 'ae_users' , array ( 'email' => $this->data['usercore/confirm/email'] ) ) ;
				
				if ( !empty($user) )
				{
					if ( $this->data['usercore/confirm/email'] == $confirmation['user'] )
					{
						$clearPassword = User::getClearNewPassword () ;
						
						if ( $this->db->edit('ae_users', $user['id'], array ( 'password' => $clearPassword ) ) ) 
						{
							$mailer = new AeMail () ;
							
							$link = url() . 'user-core/login' ;
							
							if ( $mailer->sendThis (
								array ( 
									'to' => $confirmation['user']  ,
									'subject' => sprintf(_('[%s] Password reseted'), Config::get(App::APP_NAME)),
									'template' => array (
										'file'=>'email'.DS.'user-core'.DS.'password-reset-confirmed.thtml',
										'vars'=> array (
											'firstname' => $user['firstname'] ,
											'lastname' => $user['lastname'] ,
											'email' => $user['email'] ,
											'password' => $clearPassword ,
											'link' => $link
										)
									) ,
								)
							) ) 
							{
								
								/*
									Hook: UserCorePasswordReset
									
									This hook is dispatched when a user has forgotten and recovered a new password
									
									Parameters:
										$args[0] - [int] user id
										$args[1] - [string] user email
									
								*/
								new Hook ('UserCorePasswordReset', $user['id'], $user['email'] ) ;
								
								$this->cleanConfirmationCode ( $confirmationid ) ;
								$this->addResponse(_('Password reset has been confirmed.<br />Your password has been sent to you by email.'), self::RESPONSE_SUCCESS ) ;
								$this->data = array () ;
								$this->runAction('login') ;
								return;
							} else {
								App::do500('Mailer failure');
							}
						}
					}
				}
			}
			
			$this->addResponse(sprintf(_('This address is not valid. Please retry.') ) , self::RESPONSE_ERROR ) ;
		}
	}

	function passwordResetDeny ( $confirmationid )
	{
		if ( is_null ( $confirmationid ) )
		{
			App::do401 ( 'Confirmation code not provided' ) ;
		}
		
		$this->cleanExpiredConfirmationCodes () ;
		
		$confirmation = $this->db->findFirst ( 'ae_confirmations' , array ( 'hash' => $confirmationid ) ) ;
		
		if ( empty($confirmation) )
		{
			App::do401 ( 'Bad confirmation code' ) ;
		}
		
		$user = $this->db->findFirst ( 'ae_users' , array ( 'email' => $confirmation['user'] ) ) ;
		
		if ( empty($user) )
		{
			App::do401 ( 'Password not reseted' ) ;
		}
		
		$this->view->set ( 'hash', $confirmationid ) ;
		$this->view->set ( 'email', $confirmation['user'] ) ;
		
		$error = false ;
		
		if ( $this->db->deleteAll ( 'ae_confirmations' , array ( 'user' => $confirmation['user'] ) ) )
		{
			$mailer = new AeMail () ;
			if ( $mailer->sendThis (
				array ( 
					'to' => $confirmation['user']  ,
					'subject' => sprintf(_('[%s] Password reset denied'), Config::get(App::APP_NAME)),
					'template' => array (
						'file'=>'email'.DS.'user-core'.DS.'password-reset-denied.thtml',
						'vars'=> array (
							'firstname' => $user['firstname'] ,
							'lastname' => $user['lastname']
						)
					) ,
				)
			) ) 
			{
				$this->cleanConfirmationCode ( $confirmationid ) ;
				$this->addResponse(_('You denied password reset.'), self::RESPONSE_SUCCESS ) ;
				return;
			} else {
				App::do500('Mailer failure',$this);
			}
		} else {
			App::do500('Database failure',$this);
		}
		
	}
	
	
	private function generateConfirmationCode ( $action , $email )
	{
		$hash = sha1 ( $action . $email . time () ) ;
		
		if ( $this->db->add ( 'ae_confirmations' , array (
			'user' => $email,
			'hash' => $hash ,
			'action' => $action ,
			'expiry' => mysql_date ( time () + self::CONFIRMATIONS_VALIDITY * 60 * 60 ) 
		) )  == false )
		{
			App::do500('Confirmation code generation error') ;
		}
		
		return $hash ;
	}
	
	private function cleanConfirmationCode ( $hash )
	{
		$this->db->deleteAll ( 'ae_confirmations' , array (
			'hash' => $hash
		) ) ;
		
	}
	
	private function cleanExpiredConfirmationCodes ()
	{
		$this->db->deleteAll ( 'ae_confirmations' , array (
			'expiry <' => mysql_date ( time () - self::CONFIRMATIONS_VALIDITY * 60 * 60 ) 
		) ) ;
		
	}
	

	
}

?>