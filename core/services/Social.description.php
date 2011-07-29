<?php 
 class SocialServiceDescription { 
	public $generated = 'November 1, 2010, 10:54 am' ;
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