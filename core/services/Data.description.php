<?php 
 class DataServiceDescription { 
	public $generated = 'December 12, 2010, 7:06 am' ;
	public $methods = array (
	'description' => 'This service authorize read-only access to data. It provides methods for field autocompletion and more further...',
	'methods' => array (
		'autoComplete' => array (
			'name' => 'autoComplete',
			'arguments' => array (
				0 => array (
					'name' => 'query',
					'optional' => false,
					'description' => 'The search query',
					),
				1 => array (
					'name' => 'source',
					'optional' => false,
					'description' => 'The databaseID / table / field in which to search',
					),
				2 => array (
					'name' => 'conditions',
					'optional' => true,
					'default' => '""',
					'description' => 'Conditions to fit',
					),
				3 => array (
					'name' => 'results',
					'optional' => true,
					'default' => 10,
					'description' => 'Number of results to return',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Returns a list of pairs id / value that match the query',
			),
		'getAllAndChilds' => array (
			'name' => 'getAllAndChilds',
			'arguments' => array (
				0 => array (
					'name' => 'source',
					'optional' => false,
					'description' => 'The databaseID / table in which to search',
					),
				1 => array (
					'name' => 'conditions',
					'optional' => true,
					'default' => '""',
					'description' => 'Conditions to fit',
					),
				2 => array (
					'name' => 'keysAsLabel',
					'optional' => true,
					'default' => 'false',
					'description' => 'Returns labels in results despite of db field name',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Returns all data af a table (if table count < 300) and the childs based on PICK_ONE and PICK_IN behaviors',
			),
		'getOneAndChilds' => array (
			'name' => 'getOneAndChilds',
			'arguments' => array (
				0 => array (
					'name' => 'source',
					'optional' => false,
					'description' => 'The databaseID / table / elementPrimaryKey to retrieve',
					),
				1 => array (
					'name' => 'conditions',
					'optional' => true,
					'default' => '""',
					'description' => 'Conditions to fit',
					),
				2 => array (
					'name' => 'keysAsLabel',
					'optional' => true,
					'default' => 'false',
					'description' => 'Returns labels in results despite of db field name',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Re',
			),
		),
	);
}
?>