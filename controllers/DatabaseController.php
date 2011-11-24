<?php

/**
 * The Database controller is one of the main controllers in Aenoa Server.
 * It contains base action to read, add, edit and delete elements in database.
 *
 * It can be used directly using the url token /database/_db_id_/_table_name_/_action_
 * or by extending DatabaseController to your own controller, and then use callbacks
 * to modify the common DatabaseController behaviors.
 *
 */
class DatabaseController extends Controller {

	public $databaseID;
	public $table;
	public $tableLength = 20;
	public $recursivity = 1;
	public $fields = array();
	public $subFields = array();
	protected $structure;
	protected $errors = array();
	protected $toSave = array();
	protected $previousPage = null;
	protected $baseURL = null;
	protected $conditions = array();
	public $RESTResult = true;
	protected $added = false;
	protected $edited = false;

	/**
	 * Last ID on add operation
	 * @var mixed
	 */
	protected $lastId;

	protected function renderView() {
		if (is_null($this->view)) {
			return;
		}

		if ($this->view->isRendered() == false) {
			$this->view->set('__responses', $this->responses);

			if (debuggin() && App::$sanitizer->get('GET', 'scaffold') === 'yes') {
				$this->scaffold();
			} else {
				$this->view->render();
			}
		}
	}

	private function _validate() {
		$validity = array();

		$hasError = ake(self::RESPONSE_ERROR, $this->responses);


		foreach ($this->data as $k => $v) {
			if ($k == '__SESS_ID') {
				continue;
			}

			$id = explode('/', $k);
			if (count($id) > 3) {
				continue;
			}
			
			foreach ($this->structure[$this->table] as &$field) {
				if (is_array($field) && array_key_exists('name', $field) && $field['name'] == $id[2]) {
					if (array_key_exists('validation', $field)) {
						$r = '/' . $field['validation']['rule'] . '/';

						if (!preg_match($r, $v)) {
							$hasError = true;
							$this->addResponse($field['validation']['message'], self::RESPONSE_ERROR);
							$validity[$field['name']] = false;
						} else {
							$validity[$field['name']] = true;
						}
					}
				}
			}

			$this->toSave[$id[2]] = $v;
		}

		$this->view->set('validities', $validity);

		return $hasError == false;
	}

	protected function beforeAction($action) {
		$this->view->useLayout = true;

		$this->view->layoutName = 'layout-backend';
		
		/**
		 * @var AbstractDBEngine
		 */
		$this->db = App::getDatabase($this->databaseID);

		if (is_null($this->db)) {
			App::do500(sprintf(_('Database %s not available'), $this->databaseID));
		}

		$this->structure = $this->db->getStructure();


		if (is_null($this->structure) || empty($this->structure) || array_key_exists($this->table, $this->structure) == false) {
			if (debuggin()) {
				App::do404('Required structure does not exists');
			} else {
				App::do500('Required structure does not exists');
			}
		}

		$this->schema = $this->db->getTableSchema($this->table);

		if (!is_null(App::getSession()->get('DB_PREV_PAGE'))) {
			$this->previousPage = App::getSession()->get('DB_PREV_PAGE');
		}

		$this->createView('html/database-edition.thtml');

		$this->view->useLayout = true;

		$this->view->layoutName = 'layout-backend';

		if (is_subclass_of($this->model, 'Model') == false) {
			$this->reloadModel(camelize($this->table, '_'));
		}

		if (!is_null(App::getSession()->get('DB_DELETION_DONE'))) {
			$this->addResponse(sprintf(_('Element %s of %s has been deleted'), App::getSession()->getAndDestroy('DB_DELETION_DONE'), $this->table), self::RESPONSE_SUCCESS);
		}

		if (is_null($this->baseURL)) {
			$this->baseURL = url() . 'database/' . $this->databaseID . '/' . $this->table . '/';
		}

		$this->view->setAll(array(
			'structure' => $this->structure[$this->table],
			'table' => $this->table,
			'data' => $this->data,
			'databaseID' => $this->databaseID,
			'baseURL' => $this->baseURL,
			'validities' => array(),
			'mode' => '',
			'urls' => array(),
		));

		if ($this->recursivity > 10) {
			$this->recursivity = 10;
		}
	}

	/**
	 * Scaffolder
	 */
	protected function scaffold() {
		if (!debuggin() || is_null($this->view) || $this->view->isRendered()) {
			App::do401('Scaffolding not authorized');
		}

		$this->view->set('scaffolding', true);

		$content = $this->view->render(false);

		$path = ROOT . 'app' . DS . 'templates' . DS . 'html' . DS;
		$futil = new FSUtil($path);
		if (!$futil->dirExists($this->table)) {
			$futil->createDir('', $this->table);
		}

		$filepath = $path . DS . $this->table . DS . uncamelize($this->action) . '.thtml';

		$f = new File($filepath, true);
		if ($f->isEmpty() == false) {
			$f->copy($filepath . '.' . date('H-m-d-h-i-s') . '.thtml');
		}
		$f->write($content);
		$f->close();

		App::doRespond(200, 'Scaffolding done');
	}

	private function appendPersistentData($arr = array(), $formatted = true) {
		return array_merge(( $formatted ?
					App::getSession()->getFormattedPersitentPost($this->databaseID, $this->table) :
					App::getSession()->getPersitentPost($this->databaseID, $this->table)), $arr);
	}

	private function __getId($primaryKey, $id = null) {
		if (is_null($id) && !empty($this->data[$this->table . '/' . $primaryKey])) {
			$id = $this->data[$this->databaseID . '/' . $this->table . '/' . $primaryKey];
		}

		if (is_null($id)) {
			App::do500('Database table ID check failure');
		}

		return $id;
	}

	/**
	 * Add a database entry
	 *
	 *
	 *
	 *
	 *
	 *
	 * @param mixed $id A string or an int depending of the type of the primary field
	 * @param string $child_table In case of child edition, providen id should be id of the parent, and child_table the name of
	 */
	public function add($id=null) {
		$this->view->set('mode', 'add');

		$urls = array();

		$res = false;

		if (!is_null($this->previousPage)) {
			$urls[_('Back to previous page')] = array('url' => $this->previousPage, 'class' => 'icon16 back bold');
		}

		if (!empty($this->data)) {
			$res = $this->__add();

			if ($res) {
				$urls = array_merge($urls, array(
					sprintf(_('Add a new entry to %s'), $this->table) => array('url' => $this->baseURL . 'add/' . time(), 'class' => 'icon16 add'),
					sprintf(_('Search in %s'), $this->table) => array('url' => $this->baseURL . '/search', 'class' => 'icon16 search')
					));
			}
		} else {
			$this->__setPreAddData($id);
		}

		$this->baseURL .= 'add';

		$this->view->set('baseURL', $this->baseURL);

		$this->view->set('urls', $urls);

		return $res;
	}

	private function __setPreAddData($id) {
		if (!is_null($id)) {
			$primaryKey = $this->schema->getPrimary();
			$data = $this->appendPersistentData($this->db->find($this->table, $this->__getId($primaryKey, $id)), false);
			if (!empty($data) && ake($primaryKey, $data)) {
				unset($data[$primaryKey]);
			}
		} else {
			$data = $this->appendPersistentData(array(), false);
		}

		if (!empty($data)) {
			$dat = array();
			foreach ($data as $fieldName => $value) {
				foreach ($this->structure[$this->table] as &$field) {
					if ($fieldName == $field['name'] && !is_array($value)) {
						if (ake('behavior', $field)) {
							if ($field['behavior'] & DBSchema::BHR_PICK_ONE) {
								$dat[$this->databaseID . '/' . $this->table . '/' . $fieldName] = $this->db->find($field['source'], $value);
							} else if ($field['behavior'] & DBSchema::BHR_PICK_IN) {
								$dat[$this->databaseID . '/' . $this->table . '/' . $fieldName] = $this->db->findAll($field['source'], array($this->schema->getPrimary() => $value));
							}
						} else {
							$dat[$this->databaseID . '/' . $this->table . '/' . $fieldName] = $value;
						}
						break;
					}
				}
			}

			$this->view->set('data', $this->output = $dat);
		} else {
			$this->view->set('data', $this->output = array());
		}
	}

	public function __add() {

		if (!$this->_validate()) {
			return false;
		}

		$this->toSave = $this->model->beforeAdd($this->toSave);
		
		if ($this->toSave !== false && ($res = $this->db->add($this->table, $this->toSave) )) {
			$lid = $this->db->lastId();

			$this->model->onAdd($lid, $this->toSave);

			$this->lastId = $lid;

			App::getSession()->set('Application.db.add.' . $this->databaseID . '.' . $this->table, $this->lastId);

			$this->view->set('mode', 'read');

			$this->view->set('data', $this->output = $this->db->find($this->table, $lid));

			$this->view->set('done', true);

			$this->added = true;

			$this->addResponse(sprintf(_('New element has been added to table %s.'), $this->table));
		} else {

			$this->RESTResult = false;

			$this->view->set('done', false);

			$this->addResponse(sprintf(_('New element has not been added to table %s.'), $this->table), self::RESPONSE_ERROR);
		}

		return $res;
	}

	public function __edit($id) {

		if (!$this->_validate()) {
			return false;
		}

		if (App::getSession()->has('Database.add.' . $this->table . '.' . $id)) {
			App::getSession()->uset('Database.add.' . $this->table . '.' . $id);

			$this->toSave = $this->model->beforeAdd($this->toSave);
		}

		$this->toSave = $this->model->beforeEdit($id, $this->toSave);

		if ($this->toSave !== false && ($res = $this->db->edit($this->table, $id, $this->toSave))) {

			$data = $this->db->findChildren($this->table, $this->db->find($this->table, $id));

			$this->model->onEdit($id, $data);

			$this->view->set('mode', 'none');

			$this->view->set('data', $this->output = $data);

			$this->view->set('done', true);

			$this->edited = true;

			$this->addResponse(sprintf(_('Element %s of table %s has been edited.'), $id, $this->table));
		} else {
			$this->RESTResult = false;

			$this->view->set('done', false);

			$this->addResponse(sprintf(_('Element %s of table %s has not been edited.'), $id, $this->table), self::RESPONSE_ERROR);
		}

		return $res;
	}

	/**
	 * Mass import
	 *
	 *
	 *
	 *
	 *
	 *
	 * @param mixed $id A string or an int depending of the type of the primary field to copy from
	 */
	public function massImport($id=null) {
		$primaryKey = $this->schema->getPrimary();

		$this->view->set('mode', 'add');

		$urls = array();

		$res = false;

		if (!is_null($this->previousPage)) {
			$urls[_('Back to previous page')] = array('url' => $this->previousPage, 'class' => 'icon16 back bold');
		}
		$this->view->set('urls', $urls);

		$editableField = null;
		$slugField = null;
		$str = $this->databaseID . '/' . $this->table . '/';
		$pickins = array();

		foreach ($this->structure[$this->table] as $fieldname => $field) {
			if (@$field['main'] == true) {
				$editableField = $field['name'];
				if (ake('urlize-to', $field)) {
					$slugField = $field['urlize-to'];
				}
			}

			if (@$field['behavior'] & DBSchema::BHR_PICK_IN) {
				$pickins[] = $field['name'];
			}
		}

		if (is_null($editableField)) {
			$this->view->set('done', true);
			$this->addResponse(sprintf(_('Table %s does not support mass import.'), $this->table), self::RESPONSE_ERROR);
			return;
		}


		if (!empty($this->data)) {
			$data = $this->data;

			$this->data = array();

			if ($data[$str . '__import'] == '') {
				$this->view->set('done', false);
				$this->addResponse(sprintf(_('Please fill the content field before starting mass import.'), $this->table), self::RESPONSE_ERROR);
			} else {
				$elements = explode("\n", $data[$str . '__import']);
				array_clean($elements);
				
				$search = ($data[$str . '__import/overwrite'] != '' );

				unset($data[$str . '__import']);
				unset($data[$str . '__import/overwrite']);
				$res = true;

				if (!is_null($slugField)) {
					$searchField = $slugField;
				} else {
					$searchField = $editableField;
				}

				$finalElements = array();

				foreach ($elements as $element) {
					$row = array_merge($data, array($str . $editableField => $element));

					if (!is_null($slugField)) {
						$searchVal = tl_get($element);
						$row[$str . $slugField] = tl_get($searchVal);
					}

					if ($search) {
						$_res = $this->db->findFirst($this->table, array($searchField => $searchVal));
						
						if (!empty($_res)) {
							$this->data = array();

							if (!empty($pickins)) {
								foreach ($pickins as $field) {
									$ids = explode(',', $_res[$field]);
									if (ake($str . $field, $data) && !in_array($data[$str . $field], $ids)) {
										$this->data [$str . $field] = ( $_res[$field] == '' ? $data[$str . $field] : $_res[$field] . ',' . $data[$str . $field] );
									}
								}
								if (!empty($this->data)) {
									if (!$this->__edit($res[$primaryKey])) {
										$res = false;
									}
								}
							} else {
								$finalElements[] = $row;
							}
						} else {
							$finalElements[] = $row;
						}
					} else {
						$finalElements[] = $row;
					}
				}

				$this->db->startTransaction();
				foreach ($finalElements as $row) {
					$this->data = $row;
					if (!$this->__add()) {
						$res = false;
					}
				}
				$this->db->endTransaction();

				$this->view->set('done', true);

				if ($res) {
					$this->addResponse(sprintf(_('Mass import in %s done without error.'), $this->table), self::RESPONSE_SUCCESS);
				} else {
					$this->addResponse(sprintf(_('Mass import in %s done, but some errors have been found.'), $this->table), self::RESPONSE_ERROR);
				}
			}
		}

		$this->__setPreAddData($id);


		$struct = array();
		foreach ($this->structure[$this->table] as &$field) {
			if ($field['name'] != $editableField && $field['name'] != $slugField) {
				$struct[$field['name']] = $field;
			}
		}

		$this->view->set('structure', array_merge($struct, array(
				array(
					'type' => DBSchema::TYPE_TEXT,
					'name' => '__import',
					'label' => _('Paste here your content'),
					'description' => _('Each row must be separated by a new line, each line is considered as the main field. If an urlized version is required, it will automatically be generated.'),
				),
				array(
					'type' => DBSchema::TYPE_BOOL,
					'name' => '__import/overwrite',
					'label' => _('Search for existing elements, and try to edit them'),
					'description' => _('If you don\' check this box, system may create some duplicate rows in your table. If you check this box, system will try to merge some of your table rows with your new content, only if some multi pick-in fields are available in table.'),
				)
			)));

		$this->baseURL .= 'mass-import';

		$this->view->set('baseURL', $this->baseURL);

		return $res;
	}

	/**
	 * Display content of one entry
	 *
	 *
	 *
	 *
	 *
	 *
	 * @param mixed $id A string or an int depending of the type of the primary field
	 * @param string $child_table In case of child edition, providen id should be id of the parent, and child_table the name of
	 */
	public function read($id=null) {
		$primaryKey = $this->schema->getPrimary();

		$res = false;

		$id = $this->__getId($primaryKey, $id);

		if ($this->recursivity < 2) {
			$this->recursivity = 2;
		}

		$data = $this->db->findRelatives($this->table, $this->db->find($this->table, $id, $this->fields), $this->subFields, $this->recursivity);

		if (empty($data)) {
			App::do404('Element not found');
		}


		$this->view->set('mode', 'read');
		$this->view->set('data', $this->output = $data);
		$this->view->set('identifier', $id);

		$urls = array();

		if (!is_null($this->previousPage)) {
			$urls[_('Back to previous page')] = array('url' => $this->previousPage, 'class' => 'icon16 back bold');
		}

		$urls = array_merge($urls, array(
			sprintf(_('Add a new entry to %s'), $this->table) => array('url' => $this->baseURL . 'add', 'class' => 'icon16 add'),
			sprintf(_('Edit this entry'), $this->table) => array('url' => $this->baseURL . 'edit/' . $id, 'class' => 'icon16 edit'),
			sprintf(_('Search in %s'), $this->table) => array('url' => $this->baseURL . 'search', 'class' => 'icon16 search')
			));

		$this->baseURL .= 'read/' . $id;

		$this->view->set('baseURL', $this->baseURL);

		$this->view->set('urls', $urls);
	}

	/**
	 * Edit a database entry
	 *
	 *
	 *
	 *
	 *
	 *
	 * @param mixed $id A string or an int depending of the type of the primary field
	 * @param string $child_table In case of child edition, providen id should be id of the parent, and child_table the name of
	 */
	public function edit($id=null, $child_table = null) {

		if ($id == 'ae:last') {
			$key = 'Application.db.add.' . $this->databaseID . '.' . $this->table;

			if (App::getSession()->has($key)) {
				$id = App::getSession()->get($key);
			} else {
				App::do404('Element not found, no element previously added');
			}
		} else if ($id == '_selected' && array_key_exists($this->databaseID . '/' . $this->table . '/field', $this->data)) {
			$id = $this->data[$this->databaseID . '/' . $this->table . '/field'];
			$this->data = array();
		}

		$primaryKey = $this->schema->getPrimary();


		$urls = array();

		if (!is_null($this->previousPage)) {
			$urls[_('Back to previous page')] = array('url' => $this->previousPage, 'class' => 'icon16 back bold');
		}

		$urls = array_merge($urls, array(
			sprintf(_('Add a new entry to %s'), $this->table) => array('url' => $this->baseURL . 'add', 'class' => 'icon16 add'),
			sprintf(_('Copy this entry into a new entry'), $this->table) => array('url' => $this->baseURL . 'add/' . $id, 'class' => 'icon16 add'),
			sprintf(_('Mass import in %s'), $this->table) => array('url' => $this->baseURL . 'mass-import/' . $id, 'class' => 'icon16 download'),
			sprintf(_('Search in %s'), $this->table) => array('url' => $this->baseURL . 'search', 'class' => 'icon16 search')
			));


		$res = false;

		$dat = $this->db->find($this->table, $this->__getId($primaryKey, $id));

		if (empty($dat)) {
			App::do404('Element not found');
		}


		$this->baseURL .= 'edit/' . $id;

		/*
		 * In case of Child edition
		 * - we select the required child
		 * -
		 */
		if (!is_null($child_table)) {
			$sub_dat = array();

			$childPrimaryKey = null;

			$parentId = $id;

			foreach ($this->structure[$this->table] as $field) {
				if (( $field['type'] == DBSchema::TYPE_PARENT ) && $field['source'] == $child_table) {
					$childPrimaryKey = $this->db->getTableSchema($child_table)->getPrimary();

					if (ake($field['name'], $dat)) {
						$sub_dat = $this->db->find($child_table, $dat[$field['name']]);
					}


					if (empty($sub_dat)) {
						$sub_dat = array($field['source-link-field'] => $parentId);

						$res = $this->db->add($child_table, $sub_dat);

						App::getSession()->set('Database.add.' . $child_table . '.' . $id, 'odd');

						if (!$res) {
							App::do500('Unable to add such child');
						}



						$_id = $this->__getId($childPrimaryKey, $this->db->lastId($child_table));

						$res = $this->db->edit($this->table, $id, array($field['name'] => $_id));

						if (!$res) {
							App::do500('Unable to add such child');
						}


						$id = $_id;

						$sub_dat[$childPrimaryKey] = $_id;
					} else {
						$id = $sub_dat[$childPrimaryKey];
					}


					break;
				}
			}



			if (is_null($childPrimaryKey)) {
				App::do404('Element child not found');
			}

			$this->table = $child_table;

			$this->reloadModel(camelize($child_table, '_'));

			$this->view->setAll(array(
				'table' => $this->table,
				'structure' => $this->structure[$this->table],
			));

			$primaryKey = $childPrimaryKey;

			$dat = $sub_dat;
		} else {
			$id = $this->__getId($primaryKey, $id);
		}


		$dat = $this->db->findAscendants($this->table, $this->db->findChildren($this->table, $this->appendPersistentData($dat, false)));
		$data = array();

		$str = $this->databaseID . '/' . $this->table;
		foreach ($dat as $k => &$v) {
			$data[$str . '/' . $k] = $v;
		}

		$this->view->set('data', $this->output = $data);

		$this->view->set('mode', 'edit');
		$this->view->set('identifier', $id);

		if (!empty($this->data)) {

			$this->__edit($id);
		}


		if (!is_null($child_table)) {
			$this->baseURL .= '/' . $child_table;
		}

		$this->view->set('baseURL', $this->baseURL);

		$this->view->set('urls', $urls);
	}

	/**
	 * List ALL entries
	 *
	 *
	 * This method is used only by REST api
	 *
	 *
	 *
	 */
	public function __enumerate() {
		$this->output = $this->db->findRelatives($this->table, $this->db->findAll($this->table, $this->conditions, 0, $this->fields), $this->subFields, $this->recursivity);
	}

	/**
	 * Count entries of a table or number of pages for a agiven length
	 *
	 *
	 * This method is used only by REST api
	 *
	 *
	 * @return array
	 */
	public function __count($length = null) {
		$this->output = array('total' => $this->db->count($this->table, $this->conditions));

		if (!is_null($length)) {
			$this->output = array('pages' => ceil($this->output['total'] / $length));
		}
	}

	/**
	 * Read entries
	 *
	 *
	 *
	 *
	 *
	 *
	 * @param mixed $id A string or an int depending of the type of the primary field
	 * @param string $child_table In case of child edition, providen id should be id of the parent, and child_table the name of
	 */
	public function readAll($page = null, $order = null, $dir = null, $useSession = true, $id=null) {
		if (intval($page) < 1 || is_null($page)) {
			if (App::getSession()->has('DB_PAGE_' . $this->databaseID . '_' . $this->table)) {
				$page = App::getSession()->get('DB_PAGE_' . $this->databaseID . '_' . $this->table);
			} else {
				$page = 1;
			}
		}

		if ($useSession === true) {
			App::getSession()->set('DB_PAGE_' . $this->databaseID . '_' . $this->table, $page);

			if (App::getSession()->has('DB_CONDITIONS_' . $this->databaseID . '_' . $this->table)) {
				$this->conditions = App::getSession()->get('DB_CONDITIONS_' . $this->databaseID . '_' . $this->table);
			}
			if (App::getSession()->has('DB_CONDITIONS_KEY_' . $this->databaseID . '_' . $this->table)) {
				$this->view->set('autoTableConditions', App::getSession()->get('DB_CONDITIONS_KEY_' . $this->databaseID . '_' . $this->table));
			} else {

				$this->view->set('autoTableConditions', '');
			}

			if (App::getSession()->has('DB_TABLE_MODE_' . $this->databaseID . '_' . $this->table)) {
				$this->view->set('tableMode', App::getSession()->get('DB_TABLE_MODE_' . $this->databaseID . '_' . $this->table));
			}

			if (is_null($order)) {
				if (App::getSession()->has('DB_TABLE_ORDER_' . $this->databaseID . '_' . $this->table)) {
					$order = App::getSession()->get('DB_TABLE_ORDER_' . $this->databaseID . '_' . $this->table);
				}
			} else {
				App::getSession()->set('DB_TABLE_ORDER_' . $this->databaseID . '_' . $this->table, $order);
			}

			if (is_null($dir)) {
				if (App::getSession()->has('DB_TABLE_DIR_' . $this->databaseID . '_' . $this->table)) {
					$dir = App::getSession()->get('DB_TABLE_DIR_' . $this->databaseID . '_' . $this->table);
				}
			} else {
				App::getSession()->set('DB_TABLE_DIR_' . $this->databaseID . '_' . $this->table, $dir);
				
			}
		}
		
		else {
		    //App::getSession()->set('DB_PAGE_' . $this->databaseID . '_' . $this->table, $page);
		  $this->view->set('autoTableConditions', ''); 
		  if (is_null($order)) {
				if (App::getSession()->has('DB_TABLE_ORDER_' . $this->databaseID . '_' . $this->table)) {
					$order = App::getSession()->get('DB_TABLE_ORDER_' . $this->databaseID . '_' . $this->table);
				}
			} else {
				App::getSession()->set('DB_TABLE_ORDER_' . $this->databaseID . '_' . $this->table, $order);
			}

			if (is_null($dir)) {
				if (App::getSession()->has('DB_TABLE_DIR_' . $this->databaseID . '_' . $this->table)) {
					$dir = App::getSession()->get('DB_TABLE_DIR_' . $this->databaseID . '_' . $this->table);
				}
			} else {
				App::getSession()->set('DB_TABLE_DIR_' . $this->databaseID . '_' . $this->table, $dir);
				
			}
		 
		}

		$length = $this->tableLength;
		$this->view->set('currentWidget', $id);
		$this->view->set('mode', 'readAll');

		$this->view->set('structure', $this->structure[$this->table]);

		$count = $this->db->count($this->table, $this->conditions);

		$pageLength = ceil($count / $length);

		if ($page > $pageLength) {
			$page = $pageLength;
		}

		$this->view->set('count', $count);

		$this->view->set('page', $page);

		$this->view->set('pageLength', $pageLength);

		$limit = array($length, ($page - 1) * $length);

		$baseURL = $this->baseURL . 'index/' . $page;

		if (!is_null($order)) {
			$limit[2] = $order;
			$limit[3] = $dir = (!is_null($dir) ? $dir : 'asc' );
			$baseURL .= '/' . $order . '/' . $dir;
		}

		$this->view->set('order', $order);
		$this->view->set('dir', $dir);

		if ($useSession === true) {
			App::getSession()->set('DB_PREV_PAGE', $baseURL);
		}


		if ($this->recursivity > 0) {
			$this->output = $this->db->findRelatives($this->table, $this->db->findAll($this->table, $this->conditions, $limit, $this->fields), $this->subFields, $this->recursivity);
		} else {

			$this->output = $this->db->findAll($this->table, $this->conditions, $limit, $this->fields);
		}


		$this->view->set('data', $this->output);


		$urls = array(
			sprintf(_('Add a new entry to %s'), $this->table) => array('url' => $this->baseURL . 'add', 'class' => 'icon16 add'),
			sprintf(_('Mass import in %s'), $this->table) => array('url' => $this->baseURL . 'mass-import', 'class' => 'icon16 download'),
		);

		if ($count > 0) {
			$urls[sprintf(_('Search in %s'), $this->table)] = array('url' => $this->baseURL . 'search', 'class' => 'icon16 search');
		}

		$this->view->set('urls', $urls);
	}

	/**
	 * Update read with filter or no filter
	 *
	 *
	 *
	 *

	 *
	 *
	 *
	 * @param mixed $id A string or an int depending of the type of the primary field
	 * @param string $child_table In case of child edition, providen id should be id of the parent, and child_table the name of
	 *
	 *
	 *
	 */
	public function readAllUpdate($page = 1, $order = null, $dir = null) {
		$k = $this->databaseID . '/' . $this->table . '/search';
		if (!empty($this->data) && ake($k, $this->data) && $this->data[$k] != '') {
			$key = AbstractDBEngine::getFilterable($this->structure[$this->table]);
			$this->conditions[$key . ' LIKE'] = '%' . trim($this->data[$k]) . '%';
			App::getSession()->set('DB_CONDITIONS_KEY_' . $this->databaseID . '_' . $this->table, $this->data[$k]);
		} else {
			App::getSession()->uset('DB_CONDITIONS_KEY_' . $this->databaseID . '_' . $this->table);
		}

		App::getSession()->set('DB_CONDITIONS_' . $this->databaseID . '_' . $this->table, $this->conditions);
		if ($order == 'desc')
			$order = 'label';
		$this->readAll($page, $order, $dir, true, '');

		if (App::isAjax()) {

			$this->view->set('mode', 'readAllUpdate');
		}
	}

	
	public function resetFilter($page = 1, $order = null, $dir = null, $currentWidget = null) {
		//App::getSession()->uset('DB_CONDITIONS_KEY_' . $this->databaseID . '_' . $this->table);
		//App::getSession()->uset('DB_CONDITIONS_' . $this->databaseID . '_' . $this->table, $this->conditions);
		if (!empty($currentWidget))
			$this->readAll($page, $order, $dir, false, $currentWidget);
		else
			$this->readAll($page, $order, $dir,false);



		$this->view->set('mode', 'readAllUpdate');
	}

	/**
	 * Update read with table mode changing
	 *
	 */
	public function readModeSwitch($mode, $page = 1, $order = null, $dir = null) {
		if ($mode == 'inline' || $mode == 'icons') {
			App::getSession()->set('DB_TABLE_MODE_' . $this->databaseID . '_' . $this->table, $mode);
		}

		$this->readAll($page, $order, $dir);
	}

	public function index($page = 1, $order = null, $dir = null) {
		$this->readAll($page, $order, $dir);
	}

	/**
	 * Delete a database entry
	 *
	 *
	 *
	 *
	 *
	 *
	 * @param mixed $id A string or an int depending of the type of the primary field
	 * @param string $child_table In case of child edition, providen id should be id of the parent, and child_table the name of
	 */
	public function delete($id = null, $redirect = false) {

		$result = $this->__delete($id);

		if ($result) {
			App::getSession()->set('DB_DELETION_DONE', $id);
		}

		if ($result && $redirect !== false && !is_null($this->previousPage)) {
			App::redirect($this->previousPage);
			return;
		}

		return $result;
	}

	/**
	 * Delete many database entries
	 *
	 *
	 *
	 *
	 *
	 *
	 * @param mixed $id A string or an int depending of the type of the primary field
	 * @param string $child_table In case of child edition, providen id should be id of the parent, and child_table the name of
	 */
	public function deleteAll($redirect = false) {
		$ids = array();

		$primaryKey = $this->schema->getPrimary();

		foreach ($this->data as $k => $v) {
			$identifier = explode('/', $k);
			if ($v == 'on' && count($identifier) > 2) {
				$ids[] = $identifier[2];
			}
		}

		if (empty($ids)) {
			$this->view->set('done', false);
			$this->addResponse(sprintf(_('You didn\'t select any element to delete.'), $this->table), self::RESPONSE_ERROR);

			if (!is_null($this->previousPage)) {
				$this->view->set('urls', array(_('Back to previous page') => array('url' => $this->previousPage, 'class' => 'icon16 back bold')));
			}
			return;
		}

		$result = $this->model->beforeDeleteAll($ids);

		if ($result) {
			$_ids = array();
			foreach ($ids as $id) {
				if (!$this->__delete($id)) {
					$result = false;
				} else {
					$_ids [] = $id;
				}
			}
		}

		if (!empty($_ids)) {
			$this->model->onDeleteAll($_ids);
			App::getSession()->set('DB_DELETION_DONE', implode(', ', $_ids));
		}

		if ($result && $redirect !== false && !is_null($this->previousPage)) {
			App::redirect($this->previousPage);
			return;
		}

		if ($result) {

			$this->view->set('done', true);
			$this->addResponse(sprintf(_('Elements of %d has been deleted.'), $this->table));
		} else {
			$this->view->set('done', false);
			$this->addResponse(sprintf(_('Elements of %d has not been deleted. Administrators has been advised. We are sorry for the inconvenience.'), $this->table), self::RESPONSE_ERROR);
		}

		return $result;
	}

	function __delete($id) {
		$primaryKey = $this->schema->getPrimary();

		$id = $this->__getId($primaryKey, $id);

		$data = $this->db->find($this->table, $id);

		if (ake('label', $data)) {
			$title = $data['label'];
		} else {
			$title = $id;
		}

		if (empty($data)) {
			App::do404('Element not found');
		}

		if (!$this->model->beforeDelete($id)) {

			$this->RESTResult = false;

			return false;
		}

		// Check out the PARENT fields in order to delete childs
		$res = null;
		foreach ($this->structure[$this->table] as $field) {
			if (( $field['type'] == DBSchema::TYPE_PARENT)) {
				$c = $this->db->count($field['source'], array($field['source-link-field'] => $id));
				$res = $this->db->deleteAll($field['source'], array($field['source-link-field'] => $id));

				if (!$res) {
					$this->addResponse(sprintf(_('Children of %s in %s has NOT been deleted. '), $title, _(humanize($field['source'], '_'))), self::RESPONSE_ERROR);
				} else {
					$this->addResponse(sprintf(ngettext('%s child of %s in %s has been properly deleted.', '%s children of %s in %s has been properly deleted.', $c), $c, $title, _(humanize($field['source'], '_'))));
				}
			}
		}

		// Check out PICK_IN and PICK_ONE in others tables and delete reference to current
		$referencesTables = $this->db->getTableSchema($this->table)->getPickFields();
		foreach ($referencesTables as $table => $fields) {
			$conds = array();
			foreach ($fields as $field) {
				if ($field['behavior'] & DBSchema::BHR_PICK_ONE && ake('delete-as-parent', $field) && $field['delete-as-parent'] === true) {

					$c = $this->db->count($field['source'], array($field['name'] => $id));
					$res = $this->db->deleteAll($field['source'], array($field['name'] => $id));

					if (!$res) {
						$this->addResponse(sprintf(_('Children of %s in %s has NOT been deleted. '), $title, _(humanize($field['source'], '_'))), self::RESPONSE_ERROR);
					} else {
						$this->addResponse(sprintf(ngettext('%s child of %s in %s has been properly deleted.', '%s children of %s in %s has been properly deleted.', $c), $c, $title, _(humanize($field['source'], '_'))));
					}
				} else {
					$conds[$field] = $id;
				}
			}

			if (empty($conds))
				continue;

			$toEdit = $this->db->findAll($table, $conds);
			$res = true;
			foreach ($toEdit as &$element) {
				$els = array();
				foreach ($fields as $field) {
					$arr = array_flip(explode(',', $element[$field]));
					unset($arr[$id]);
					$els[$field] = implode(',', array_flip($arr));
				}
				if (!$this->db->edit($table, $this->schema->getPrimary($element), $els)) {
					$res = false;
				}
			}
			if (!$res) {
				$this->addResponse(sprintf(_('References of %s in %s has NOT been edited. '), $title, _(humanize($table, '_'))), self::RESPONSE_ERROR);
			} else {
				$this->addResponse(sprintf(_('References of %s in %s has been properly deleted. '), $title, _(humanize($table, '_'))));
			}
		}


		// Finally delete the entry
		$result = $this->db->delete($this->table, $id);

		if ($result) {
			$this->model->onDelete($id);

			$this->view->set('done', true);
			$this->addResponse(sprintf(_('Element %s of %s has been deleted.'), $title, $this->table));
		} else {
			$this->RESTResult = false;

			$this->view->set('done', false);
			$this->addResponse(sprintf(_('Element %s of %s has not been deleted. Administrators has been advised. We are sorry for the inconvenience.'), $title, _(humanize($this->table, '_'))), self::RESPONSE_ERROR);
		}
	}

}

?>
