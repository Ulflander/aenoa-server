<?php 
/**
 * <h2>Aenoa Social service documentation</h2>
 * 
 * 
 * <p>Provides an access to some Social medias APIs</p>
 * 
 * 
 * 
 * 
 * 
 * <h2>Service method: getShareService</h2>
 * 
 * 
 * <p>Provides a list of sharing services.</p>
 * 
 * <h3>Parameters</h3>
 * 
 * 
 * <b>pageTitle</b> (Optional: no) Title of the page to share 
 * <b>pageURI</b> (Optional: no) URI of the page to share 
 * <b>pageExcerpt</b> (Optional: yes, default value is "null" ) An excerpt of the content of the page 
 * 
 * 
 * 
 * <h3>Returns in case of success</h3>
 * 
 * <b>share_services</b> An array of URLs to share the page on social networks  <pre>AeSocialShare::mapShares( $pageTitle , $pageURI, $pageExcerpt )</pre>
 * 
 * 
 * 
 * 
 * <h3>Returns in case of failure</h3>
 * 
 * 
 * 
 * 
 * 
 * @see SocialService
 * 
 */

class SocialServiceDescription { 
	public $generated = 'September 12, 2011, 10:04 am' ;
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
				0 => array (
					'name' => 'share_services',
					'value' => 'AeSocialShare::mapShares( $pageTitle , $pageURI, $pageExcerpt )',
					'description' => 'An array of URLs to share the page on social networks',
					),
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Provides a list of sharing services.',
			),
		),
	);
}
?>