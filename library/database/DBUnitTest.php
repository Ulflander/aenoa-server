<?php

/**
 * Description of DBUnitTest
 *
 * @author xavier
 */
class DBUnitTest {

	function run($engine, $source) {
		$id = '__test__' . $engine;

		$tables = $this->getTestStructure();

		$db = new $engine($id, $tables);

		if ($db->sourceExists($source, true)) {
			if (!$db->setSource($source)) {
				trigger_error('Connection to DB ' . $id . ' failed.');
			}
		} else {
			trigger_error('Source for DB ' . $id . ' does not exist.');
		}

		if (empty($tables)) {
			trigger_error('No table found for database ' . $id . '.');
		}

		if ($db->setStructure($tables) == false) {
			trigger_error('Database ' . $id . ' requires to be deployed.');
		}
	}

	function getTestStructure() {
		return array(
			'table_1' => array(
				array(
					'name' => 'bhr_increment',
					'type' => DBSchema::TYPE_INT,
					'hide-from-table' => true
				),
				array(
					'name' => 'string_type',
					'label' => _('String type test'),
					'type' => DBSchema::TYPE_STRING
				),
				array(
					'name' => 'enum_type',
					'label' => _('Enum type test'),
					'type' => DBSchema::TYPE_ENUM,
					'values' => array('all', 'rest', 'services', 'other'),
					'labels' => array(_('All'), _('REST API'), _('Services'), _('Other')),
					'default' => 'all'
				),
				array(
					'name' => 'pick_one_bhr',
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_PICK_ONE,
					'source' => 'table_3',
					'source-main-field' => 'string_type'
				),
				array(
					'name' => 'pick_in_bhr',
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_PICK_IN,
					'source' => 'table_3',
					'source-main-field' => 'string_type'
				)
			),
			'table_2' => array(
				array(
					'name' => 'child_type',
					'label' => _('Child type test'),
					'type' => DBSchema::TYPE_CHILD,
					'source' => 'table_1',
					'source-main-field' => 'string_type',
					'source-link-field' => 'parent_type'
				),
				array(
					'name' => 'string_type',
					'label' => _('String type test'),
					'type' => DBSchema::TYPE_STRING
				),
			),
			'table_3' => array(
				array(
					'name' => 'int_type',
					'type' => DBSchema::TYPE_INT,
					'label' => _('INT type test')
				)
			)
		);
	}

}

?>
