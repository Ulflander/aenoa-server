<?php 
/**
 * <h2>Aenoa Data service documentation</h2>
 * 
 * <p>This service authorize read-only access to data. It provides methods for field autocompletion and more further...</p>
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
 * <h2>Service method: autoComplete</h2>
 * 
 * 
 * <p>Returns a list of pairs id / value that match the query</p>
 * 
 * 
 * Parameters:
 * 
 * <h4>query</h4><p>Optional: *no*</p><p>The search query</p>
 * <h4>source</h4><p>Optional: *no*</p><p>The databaseID / table / field in which to search</p>
 * <h4>conditions</h4><p>Optional: yes, default value is *""* </p><p>Conditions to fit</p>
 * <h4>results</h4><p>Optional: yes, default value is *10* </p><p>Number of results to return</p>
 * 
 * <h2>Service method: getAllAndChilds</h2>
 * 
 * 
 * <p>Returns all data af a table (if table count &lt; 300) and the childs based on PICK_ONE and PICK_IN behaviors</p>
 * 
 * 
 * Parameters:
 * 
 * <h4>source</h4><p>Optional: *no*</p><p>The databaseID / table in which to search</p>
 * <h4>conditions</h4><p>Optional: yes, default value is *""* </p><p>Conditions to fit</p>
 * <h4>keysAsLabel</h4><p>Optional: yes, default value is *false* </p><p>Returns labels in results despite of db field name</p>
 * <h4>max</h4><p>Optional: yes, default value is *300* </p><p>Max number of results to return</p>
 * 
 * <h2>Service method: getOneAndChilds</h2>
 * 
 * 
 * <p>Re</p>
 * 
 * 
 * Parameters:
 * 
 * <h4>source</h4><p>Optional: *no*</p><p>The databaseID / table / elementPrimaryKey to retrieve</p>
 * <h4>conditions</h4><p>Optional: yes, default value is *""* </p><p>Conditions to fit</p>
 * <h4>keysAsLabel</h4><p>Optional: yes, default value is *false* </p><p>Returns labels in results despite of db field name</p>
 * 
 * 
 * 
 * 
 * 
 * 
 */

class DataServiceDescription { 
	public $generated = 'September 2, 2011, 7:12 am' ;
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
				3 => array (
					'name' => 'max',
					'optional' => true,
					'default' => 300,
					'description' => 'Max number of results to return',
					),
				),
			'firstLevelReturns' => array (
				),
			'secondLevelReturns' => array (
				),
			'description' => 'Returns all data af a table (if table count &lt; 300) and the childs based on PICK_ONE and PICK_IN behaviors',
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