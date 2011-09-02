<?php 
/**
 * <h2>Aenoa File service documentation</h2>
 * 
 * <p>This service let you navigate and manage files and folders in the public folder of the Aenoa Server.</p>
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
 * <h2>Service method: getList</h2>
 * 
 * 
 * <p>This methods let\'s explore the public file system of an Aenoa Server</p>
 * 
 * 
 * Parameters:
 * 
 * <h4>path</h4><p>Optional: yes, default value is *"."* </p><p>The path of the folder to list</p>
 * 
 * 
 * 
 * 
 * 
 * 
 */

class FileServiceDescription { 
	public $generated = 'September 2, 2011, 7:13 am' ;
	public $methods = array (
	'description' => 'This service let you navigate and manage files and folders in the public folder of the Aenoa Server.',
	'methods' => array (
		'getList' => array (
			'name' => 'getList',
			'arguments' => array (
				0 => array (
					'name' => 'path',
					'optional' => true,
					'default' => '"."',
					'description' => 'The path of the folder to list',
					),
				),
			'firstLevelReturns' => array (
				0 => array (
					'name' => 'list',
					'value' => '$futil->getFilesList ( Config::get ( App::APP_PUBLIC_REPOSITORY ) . $path , false)',
					'description' => 'The list of files and folders',
					),
				),
			'secondLevelReturns' => array (
				),
			'description' => 'This methods let\'s explore the public file system of an Aenoa Server',
			),
		),
	);
}
?>