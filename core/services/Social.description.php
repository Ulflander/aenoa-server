<?php 
/**
 * <h2>Aenoa Social service documentation</h2>
 * 
 * <p>Provides an access to some Social medias APIs</p>
 * 
 * 
 * 
 * 
 * @see AenoaServerProtocol
 * @see Service
 * @see ServiceDescription
 * @see RemoteService
 * @see Gateway
 * 
 * 
 * <h2>Service method: getShareService</h2>
 * 
 * 
 * <p>Provides a list of sharing services.</p>
 * 
 * 
 * Parameters:
 * 
 * <h4>pageTitle</h4><p>Optional: *no*</p><p>Title of the page to share</p>
 * <h4>pageURI</h4><p>Optional: *no*</p><p>URI of the page to share</p>
 * <h4>pageExcerpt</h4><p>Optional: yes, default value is *null* </p><p>An excerpt of the content of the page</p>
 * 
 * 
 * 
 * 
 * 
 * 
 */

class SocialServiceDescription { 
	public $generated = 'September 2, 2011, 7:14 am' ;
	public $methods = array (
	'description' => 'Provides an access to some Social medias APIs',
	'methods' => array (
		'getShareService' => array (
			'name' => 'getShareService',
			'arguments' => array (
				0 => array (
					'name' => 'pageTitle',
					'optional' => false,
					'description' => 'Title of the page to share',
					),
				1 => array (
					'name' => 'pageURI',
					'optional' => false,
					'description' => 'URI of the page to share',
					),
				2 => array (
					'name' => 'pageExcerpt',
					'optional' => true,
					'default' => 'null',
					'description' => 'An excerpt of the content of the page',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Provides a list of sharing services.',
			),
		),
	);
}
?>