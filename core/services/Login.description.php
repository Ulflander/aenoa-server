<?php 
/**
 * <h2>Aenoa Login service documentation</h2>
 * 
 * <p>The Aenoa core Login service authorize remote applications to login and logout users based on Aenoa application users list.</p>
 * 
 * 
 * <h3>Service method: login</h3>
 * 
 * 
 * <p>The login method returns user info, group and create a cookie if given identifiers are valid in application.</p>
 * 
 * 
 * @param email - (Optional: no) The main user identifier, his email.
 * @param pwdPrivKeyHash - (Optional: no) A hash that is a combination of an API clear private key and a sha1 hash of a user password.
 * @param apiKey - (Optional: no) The public API key correspondign to the private API key used to create the pwdPrivKeyHash parameter
 * 
 * <h3>Service method: logout</h3>
 * 
 * 
 * <p>The logout method destroys cookie created by Login method.</p>
 * 
 * 
 * 
 * 
 */

class LoginServiceDescription { 
	public $generated = 'August 24, 2011, 11:12 am' ;
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
					'name' => 'pwdPrivKeyHash',
					'optional' => false,
					'description' => 'A hash that is a combination of an API clear private key and a sha1 hash of a user password.',
					),
				2 => array (
					'name' => 'apiKey',
					'optional' => false,
					'description' => 'The public API key correspondign to the private API key used to create the pwdPrivKeyHash parameter',
					),
				),
			'firstLevelReturns' => array (
				0 => array (
					'name' => 'user',
					'value' => 'array ("user" => $user->getIdentifier(),"firstname" => $user->getFirstname(),"lastname" => $user->getFirstname(),"properties" => $user->getProperties(),)',
					'description' => 'In case of success, an array containing user informations',
					),
				),
			'secondLevelReturns' => array (
				0 => array (
					'name' => 'user',
					'value' => 'array ("user" => $user->getIdentifier(),"firstname" => $user->getFirstname(),"lastname" => $user->getFirstname(),"properties" => $user->getProperties(),)',
					'description' => 'In case of error, an empty array.',
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
				),
			'description' => 'The logout method destroys cookie created by Login method.',
			),
		),
	);
}
?>