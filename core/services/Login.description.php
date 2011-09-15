<?php 
/**
 * <h2>Aenoa Login service documentation</h2>
 * 
 * 
 * <p>The Aenoa core Login service authorize remote applications to login and logout users based on Aenoa application users list.</p>
 * 
 * 
 * 
 * 
 * 
 * <h2>Service method: login</h2>
 * 
 * 
 * <p>The login method returns user info, group and create a cookie if given identifiers are valid in application.</p>
 * 
 * <h3>Parameters</h3>
 * 
 * 
 * <p><b>email</b> (Optional: no) The main user identifier, his email.</p>
 * <p><b>pwdHash</b> (Optional: no) A hash that is a combination of an API clear private key and a sha1 hash of a user password.</p>
 * <p><b>publicKey</b> (Optional: no) The public API key corresponding to the private API key used to create the pwdPrivKeyHash parameter</p>
 * 
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * <p><b>user</b> In case of success, an array containing user informations  <pre>array ("dbid" => $user->getDatabaseId(),"user" => $user->getIdentifier(),"firstname" => $user->getFirstname(),"lastname" => $user->getFirstname(),"properties" => $user->getProperties(),"infos" => $user->getData(),"level" => $user->getLevel())</pre></p>
 * 
 * 
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * <p><b>Public API key not valid</b> Failure message sent if public key is not valid</p>
 * 
 * 
 * <p><b>Invalid email</b> Failure message sent if email is not registered in users database</p>
 * 
 * 
 * <p><b>Private API authentication failed</b> Sent if password/hash combination is not valid</p>
 * 
 * 
 * <p><b>Authentication failed</b> Sent if login failed despite of valid login data</p>
 * 
 * 
 * <h2>Service method: logout</h2>
 * 
 * 
 * <p>The logout method destroys cookie created by Login method.</p>
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * <p>Nothing is returned in case of success</p>
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * <p><b>User was not connected</b> Sent if user was not connected</p>
 * 
 * 
 * 
 * 
 * 
 * 
 * @see LoginService
 * 
 */

class LoginServiceDescription { 
	public $generated = 'September 12, 2011, 10:10 am' ;
	public $methods = array (
	'description' => 'The Aenoa core Login service authorize remote applications to login and logout users based on Aenoa application users list.',
	'methods' => array (
		'login' => array (
			'name' => 'login',
			'arguments' => array (
				0 => array (
					'name' => 'email',
					'optional' => false,
					'description' => 'The main user identifier, his email.',
					),
				1 => array (
					'name' => 'pwdHash',
					'optional' => false,
					'description' => 'A hash that is a combination of an API clear private key and a sha1 hash of a user password.',
					),
				2 => array (
					'name' => 'publicKey',
					'optional' => false,
					'description' => 'The public API key corresponding to the private API key used to create the pwdPrivKeyHash parameter',
					),
				),
			'firstLevelReturns' => array (
				0 => array (
					'name' => 'user',
					'value' => 'array ("dbid" => $user->getDatabaseId(),"user" => $user->getIdentifier(),"firstname" => $user->getFirstname(),"lastname" => $user->getFirstname(),"properties" => $user->getProperties(),"infos" => $user->getData(),"level" => $user->getLevel())',
					'description' => 'In case of success, an array containing user informations',
					),
				),
			'secondLevelReturns' => array (
				0 => array (
					'name' => 'Public API key not valid',
					'description' => 'Failure message sent if public key is not valid',
					),
				1 => array (
					'name' => 'Invalid email',
					'description' => 'Failure message sent if email is not registered in users database',
					),
				2 => array (
					'name' => 'Private API authentication failed',
					'description' => 'Sent if password/hash combination is not valid',
					),
				3 => array (
					'name' => 'Authentication failed',
					'description' => 'Sent if login failed despite of valid login data',
					),
				),
			'description' => 'The login method returns user info, group and create a cookie if given identifiers are valid in application.',
			),
		'logout' => array (
			'name' => 'logout',
			'arguments' => array (
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				0 => array (
					'name' => 'User was not connected',
					'description' => 'Sent if user was not connected',
					),
				),
			'description' => 'The logout method destroys cookie created by Login method.',
			),
		),
	);
}
?>