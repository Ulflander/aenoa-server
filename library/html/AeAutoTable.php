<?php

class AeAutoTable {

	protected $_result = array();
	public $baseURL = '';
	protected $_dbID;
	protected $_struct;
	protected $_table;
	protected $_hasFile;
	protected $_mode = 'inline'; // 'inline', 'icons', 'list'
	protected $_odd = false;
	protected $_schema;
	protected $_hasSearch = false;
	private $db;
	public $isUpdate = false;
	public $showActions = true;
	public $showGlobalActions = true;
	public $currentFilter = '';

	function __construct() {
		$this->_mode = 'inline';
	}

	function setMode($mode) {
		if ($mode != 'inline' && $mode != 'icons') {
			$this->_mode = 'icons';
		} else {
			$this->_mode = $mode;
		}
	}

	function getMode() {
		return $this->_mode;
	}

	function setDatabase($dbID, $table) {
		$this->_dbID = $dbID;
		$this->_table = $table;

		$this->db = App::getDatabase($this->_dbID);

		if ($this->db) {
			$this->_struct = $this->db->getStructure();

			$this->_schema = $this->db->getTableSchema($this->_table);

			if ($this->_struct && ake($this->_table, $this->_struct)) {
				$this->_struct = $this->_struct[$this->_table];
				return true;
			}
		}

		return false;
	}

	function build($data, $page=1, $length = 1, $count=1, $order = null, $dir = null, $currentWidget = null, $context =nul) {
		if (empty($data)) {
			if ($this->currentFilter == '') {

				$this->showGlobalActions = false;
			}

			$cols = $this->startTable($page, $length, $count, $order, $dir);

			$this->_result[] = '<tr><td colspan="' . $cols . '"><div class="notify info">' . sprintf(($this->currentFilter == '' ? _('There is no element in table <strong>%s</strong>.') : _('There is no element matching with selection in table <strong>%s</strong>.')), humanize($this->_table, '_')) . '</div></td></tr>';
		} else {

			foreach ($this->_struct as $field) {
				if ($field['type'] == DBSchema::TYPE_FILE) {

					$this->_hasFile = true;
					break;
				}
			}

			$cols = $this->startTable($page, $length, $count, $order, $dir, $currentWidget, $context);

			foreach ($data as &$row) {
				$this->addRow($row, $order, $dir);
			}
		}

		$this->endTable($page, $length, $count, $order, $dir, $cols);

		echo implode("\n", $this->_result);
	}

	function startTable($page=1, $length= 1, $count=1, $order='asc', $dir='', $currentWidget=null, $context=null) {
		$l = 1;

		if ($this->baseURL == '') {
			$this->baseURL = url() . 'database/' . $this->_dbID . '/' . $this->_table . '/';
		}

		if ($this->isUpdate == false) {
			$this->_result[] = '<div class="ajsf-table-container">';
		}

		if (($field = AbstractDBEngine::getFilterable($this->_struct) ) !== false) {
			$this->_result[] = '<form action="' . $this->getURL() . 'read-all-update/' . $page . (!is_null($order) ? '/' . $order . '/' . $dir : '' ) . '" method="post" id="' . $this->_table . '/search" data-odd="odd">';

			$this->_result[] = '<input type="text" id="' . $this->_dbID . '/' . $this->_table . '/search" name="' . $this->_dbID . '/' . $this->_table . '/search" placeholder="' . _('Search into this table') . '" class="search" value="' . $this->currentFilter . '" autocomplete="off" />';

			$this->_result[] = '</form>';

			$this->_hasSearch = true;
		} else if (($field = AbstractDBEngine::getSearchable($this->_struct) ) !== false) {

			$this->_result[] = '<form action="' . $this->getURL() . 'edit/_selected" method="post" id="' . $this->_table . '/search" class="">';
			$this->_result[] = '<div class="control">';
			$this->_result[] = '<input type="hidden" id="' . $this->_dbID . '/' . $this->_table . '/field" name="' . $this->_dbID . '/' . $this->_table . '/field" />';
			$this->_result[] = '<div class="mid-left"><div id="' . $this->_dbID . '/' . $this->_table . '/field/display" name="' . $this->_dbID . '/' . $this->_table . '/field/display" data-behavior="as-input"  data-ac-multi="false" ></div></div>';
			$this->_result[] = '<div class="mid-right"><label for="' . $this->_dbID . '/' . $this->_table . '/field/input"></label>';
			$this->_result[] = '<input type="text" id="' . $this->_dbID . '/' . $this->_table . '/field/input" name="' . $this->_dbID . '/' . $this->_table . '/field/input" autocomplete="off" placeholder="' . _('Type a few letters...') . '"';
			$this->_result[] = ' data-ac-source="' . $this->_dbID . '/' . $this->_table . '/' . $field . '" data-ac-conditions="" data-ac-target="' . $this->_dbID . '/' . $this->_table . '/field/display" data-ac-primary-key="' . $this->_schema->getPrimary() . '" data-ac-empty-message="' . _('No suggestion') . '"  data-ac-multi="false" /></div>';
			$this->_result[] = '</div>';
			$this->_result[] = '<input type="submit" value="' . _('Edit this element') . '" />';
			$this->_result[] = '<script type="text/javascript">if (ajsf && ajsf.forms) ajsf.ready (function(){new ajsf.forms.Form ( _(\'#' . $this->_table . '/search\')) ;} ) ;</script>';
			$this->_result[] = '</form>';
		}

		$this->_result[] = '<ul class="right inline no-list-style table-options">';
		if ($context != 'readFilter' && isset($currentWidget)) {
			$this->_result[] = '<li><a href="' . $this->getURL() . 'read-mode-filter/inline/desc/created/' . $currentWidget . '" class="icon16 rows-list unlabeled inline" title="' . _('Show widget product content') . '"></a></li>';
		}
		else {
			$this->_result[] = '<li><a href="' . $this->getURL() . 'reset-filter/inline/' . (!is_null($order) ? 'created/' . $dir . '/' . $currentWidget : '' ) . '" class="icon16 rows-list unlabeled inline" title="' . _('Reset result') . '"></a></li>';
		}
		$this->_result[] = '<li><a href="' . $this->getURL() . 'read-mode-switch/inline/' . $page . (!is_null($order) ? '/' . $order . '/' . $dir : '' ) . '" class="icon16 rows-list unlabeled ' . ($this->getMode() == 'inline' ? 'current' : '') . '" title="' . _('Show results inline') . '">' . _('Show results inline') . '</a></li>';

		if ($this->_hasFile) {
			$this->_result[] = '<li><a href="' . $this->getURL() . 'read-mode-switch/icons/' . $page . (!is_null($order) ? '/' . $order . '/' . $dir : '' ) . '" class="icon16 icons-list unlabeled ' . ($this->getMode() == 'inline' ? '' : 'current') . '" title="' . _('Show results as icons') . '">' . _('Show results as icons') . '</a></li>';
		}
		$this->_result[] = '</ul>';


		if ($this->showGlobalActions) {
			$this->_result[] = '<form action="' . $this->getURL() . 'delete-all/redirect" method="post">';
		}
		$this->_result[] = '<table class="margedtop expanded" summary="' . sprintf(_('%s content'), $this->_table) . '."'
			. ' border="0" cellpadding="0" cellspacing="0" id="table_' . $this->_table . '">';

		$this->_result[] = '<thead>';
		$this->_result[] = '<tr>';
		$cols = 0;

		if ($this->showGlobalActions) {
			$this->_result[] = '<th class="checkbox"><input type="checkbox" id="' . $this->_table . '/__odd" name="' . $this->_table . '/__odd" data-odd="true" onclick="javascript:if($ && $.aeforms) $.aeforms.table.selectCheckboxes(\'table_' . $this->_table . '\' , this.checked );" /></th>';
			$cols++;
		}

		$b = $this->getURL() . 'readAll/';

		foreach ($this->_struct as &$field) {

			$append = $this->getAppendURLOrder($field, $order, $dir);

			$class = $this->getClass($field, $order, $dir);

			if (@$field['hide-from-table'] == true) {
				continue;
			}
			if (array_key_exists('label', $field)) {
				$this->_result[] = '<th' . $class . '><a href="' . $b . $page . '/' . $field['name'] . '/' . $append . '" title="' . sprintf(_('Order by %s'), _(ucfirst($field['name']))) . '">' . $field['label'] . '</a></th>';
			} else if (array_key_exists('name', $field)) {
				$this->_result[] = '<th' . $class . '><a href="' . $b . $page . '/' . $field['name'] . '/' . $append . '" title="' . sprintf(_('Order by %s'), _(ucfirst($field['name']))) . '">' . _(ucfirst($field['name'])) . '</a></th>';
			} else {
				continue;
			}

			$cols++;
		}

		if ($this->showActions) {
			$this->_result[] = '<th class="actions">' . _('Actions');

			$this->_result[] = '</th>';
			$cols++;
		}


		$this->_result[] = '</tr>';
		$this->_result[] = '</thead>';

		$prevId = $this->_table . '_previous_page';
		$nextId = $this->_table . '_next_page';

		return $cols;
	}

	function endTable($page=1, $length= 1, $count=1, $order, $dir, $cols) {

		$this->_result[] = '<tfoot>';
		$this->_result[] = '<tr>';
		$this->_result[] = '<td colspan="' . $cols . '" class="aligncenter"><ul class="no-list-style inline">';

		$this->getPreviousPageLink($page, $length, $count, $order, $dir);
		$this->getPageInfo($page, $length, $count, $order, $dir);
		$this->getNextPageLink($page, $length, $count, $order, $dir);

		$this->_result[] = '</ul></td>';
		$this->_result[] = '</tr>';
		$this->_result[] = '</tfoot>';

		$this->_result[] = '</table>';

		if ($this->_hasSearch) {
			$this->_result[] = '<script type="text/javascript">if(ajsf){';
			$this->_result[] = 'ajsf.load("aejax");';
			$this->_result[] = 'ajsf.load("ae-dynamic-table");';
			$this->_result[] = 'ajsf.load("aepopup");';
			$this->_result[] = 'ajsf.load("aeforms");';
			$this->_result[] = 'ajsf.ready (function(){var f = new ajsf.forms.Form ( _u(\'#' . $this->_table . '/search\') ) ; var t = new ajsf.DynamicTable(f, _u(\'#table_' . $this->_table . '\')); });';
			$this->_result[] = '}</script>';
		}
		if ($this->showGlobalActions) {
			$this->_result[] = '<input type="submit" value="' . _('Delete selection') . '" onclick="javascript: return confirm(\'' . _('Are you sure you want to delete these elements ?') . '\');" />';
			$this->_result[] = '</form>';
		}
		if ($this->isUpdate == false) {
			$this->_result[] = '</div>';
		}
	}

	function addRow($data, $order, $dir) {
		if (is_array($data)) {
			$primaryField = $this->_struct[$this->_schema->getPrimary()];
			$primaryVal = $this->getVal($this->_schema->getPrimary($data), $primaryField);
		} else {
			foreach ($this->_struct as $field) {
				$primaryField = $field;
				break;
			}
			$primaryVal = $data;
		}

		$this->_result[] = '<tr data-identifier="' . $this->getVal($primaryVal, $primaryField) . '" ' . ($this->_odd ? 'class="odd"' : '') . '>';

		if ($this->showGlobalActions) {
			$this->_result[] = '<td><input type="checkbox" id="' . $this->_dbID . '/' . $this->_table . '/' . $primaryVal . '" name="' . $this->_dbID . '/' . $this->_table . '/' . $primaryVal . '" /></td>';
		}

		$mainField = '';



		foreach ($this->_struct as &$field) {
			if (@$field['hide-from-table'] == true) {
				continue;
			}

			$class = $this->getClass($field, $order, $dir);

			if (is_array($data)) {
				$val = $data[$field['name']];
			} else {
				$val = $data;
			}


			if ($field['name'] == 'id') {
				$val = '#' . $val;
			}

			if (@$field['main'] == true || $field['name'] == 'label') {
				$mainField = $val;
			}

			switch ($field['type']) {
				case DBSchema::TYPE_PARENT:
				case DBSchema::TYPE_CHILD:
					$res = '<td class="bool ' . $class . '" data-field="' . $field['name'] . '">';
					if (intval($val) > 0) {
						$res .= '<a class="icon16 file" href="' . url() . 'database/' . $this->_dbID . '/' . $field['source'] . '/read/' . $val . '" onclick="javascript:if( ajsf && ajsf.aejax) { ajsf.aejax.detail (\'' . $this->_dbID . '/' . $field['source'] . '/' . $val . '\', \'' . sprintf(_('%s of %s'), $field['label'], $mainField) . '\', this) ; return false;}">' . _('Details') . '</a>';
					}
					$res .= '<a class="icon16 edit" href="' . url() . 'database/' . $this->_dbID . '/' . $this->_table . '/edit/' . $primaryVal . '/' . $field['source'] . '">' . _('Edit') . '</a>';
					$res .= '</td>';
					break;

				case DBSchema::TYPE_BOOL:
					$bool = $this->getVal($val, $field);
					if ($bool !== '1' && $bool !== '0') {
						$bool = '0';
					}
					$res = '<td class="bool ' . $class . '" data-field="' . $field['name'] . '"><span class="icon16 bool_' . $bool . ' unlabeled">' . $this->getVal($val, $field) . '</span></td>';
					break;

				case DBSchema::TYPE_DATETIME:
					$res = '<td class="datetime ' . $class . '" data-field="' . $field['name'] . '">' . $this->getVal($val, $field) . '</td>';
					break;

				case DBSchema::TYPE_ENUM:
					$res = '<td class="enum ' . $class . '" data-field="' . $field['name'] . '">' . $this->getVal($val, $field) . '</td>';
					break;

				case DBSchema::TYPE_FILE:
					$res = '<td class="file ' . $class . '" data-field="' . $field['name'] . '">' . $this->getVal($val, $field) . '</td>';
					break;

				case DBSchema::TYPE_INT:
				case DBSchema::TYPE_FLOAT:
					$res = '<td class="' . (!is_array($val) ? 'numeric ' : '' ) . $class . '" data-field="' . $field['name'] . '">' . $this->getVal($val, $field) . '</td>';
					break;

				case DBSchema::TYPE_STRING:
					$res = '<td class="string ' . $class . '" data-field="' . $field['name'] . '">' . $this->getVal($val, $field) . '</td>';
					break;

				case DBSchema::TYPE_TEXT:
					$res = '<td class="text ' . $class . '" data-field="' . $field['name'] . '">' . $this->getVal($val, $field) . '</td>';
					break;
			}

			$this->_result[] = $res;
		}

		if (is_null($mainField)) {
			$mainField = $primaryVal;
		}


		if ($this->showActions) {
			$this->_result[] = '<td class="inline-icons">';
			$this->_result[] = '<a class="icon16 file" href="' . $this->getURL() . 'read/' . $primaryVal . '" onclick="javascript:if($ && $.aejax) { $.aejax.detail (\'' . $this->_dbID . '/' . $this->_table . '/' . $primaryVal . '\', \'' . sprintf(_('See details about %s'), _(ucfirst($mainField))) . '\', this) ; return false;}">' . _('Details') . '</a>';
			$this->_result[] = '<a class="icon16 edit" href="' . $this->getURL() . 'edit/' . $primaryVal . '">' . _('Edit') . '</a>';
			$this->_result[] = '<a class="icon16 add" href="' . $this->getURL() . 'add/' . $primaryVal . '">' . _('Copy') . '</a>';
			$this->_result[] = '<a class="icon16 trash_empty" href="' . $this->getURL() . 'delete/' . $primaryVal . '/redirect">' . _('Delete') . '</a>';
			$this->_result[] = '</td>';
		}

		$this->_result[] = '</tr>';

		$this->_odd = !$this->_odd;
	}

	function getNextPageLink($page, $length, $count, $order, $dir) {

		if ($page < $length) {
			$this->_result[] = '<li><a class="bold" href="' . $this->getURL() . 'readAll/' . ($page + 1) . (!is_null($order) ? '/' . $order . '/' . $dir : '' ) . '">' . _('Next page') . '</a></li>';
		}
	}

	function getPreviousPageLink($page, $length, $count, $order, $dir) {
		if ($page > 1) {
			$this->_result[] = '<li><a class="bold" href="' . $this->getURL() . 'readAll/' . ($page - 1) . (!is_null($order) ? '/' . $order . '/' . $dir : '' ) . '">' . _('Previous page') . '</a></li>';
		}
	}

	function getPageInfo($page, $length, $count, $order, $dir) {
		$this->_result[] = '<li>' . sprintf(ngettext('There is one element', 'There are %d elements', $count), $count) . '</li>';

		if ($length > 1) {
			$this->_result[] = '<li>' . sprintf(_('Page %d/%d'), $page, $length) . '</li>';
		}
	}

	function getClass($field, $order, $dir) {
		return ($field['name'] == $order ? ' class="current ' . $dir . '"' : '' );
	}

	function getAppendURLOrder($field, $order, $dir) {
		return ( (is_null($dir) || $dir == 'desc' || $field['name'] != $order) ? 'asc' : 'desc' );
	}

	function getVal($val, $field) {
		$val = $this->_getVal($val, $field);
		if (@$field['as-html'] == true) {
			return '<textarea style="height: 15px; width: 250px;">' . $val . '</textarea>';
		}

		if ($val == '')
			$val = '&nbsp;';
		return $val;
	}

	function _getVal($val, $field) {
		if ($field['type'] == 'file' && ake('requirements', $field) && @$field['requirements']['mimetypes'] == 'webimage') {
			return '<img src="' . url() . $val . '" class="table-image" />';
		}
		if (ake('behavior', $field) && ake('source-main-field', $field)) {
			if ($field['behavior'] & DBSchema::BHR_PICK_ONE) {
				if (!is_array($val))
					return '';
				return $val[$field['source-main-field']];
			} else if ($field['behavior'] & DBSchema::BHR_PICK_IN) {
				if (!is_array($val))
					return '';
				$res = array();
				foreach ($val as $v) {
					$str .= $v[$field['source-main-field']];
				}
				return implode(', ', $res);
			}
		}
		if ($field['type'] == DBSchema::TYPE_ENUM) {
			foreach ($field['values'] as $idx => $key) {
				if ($key == $val) {
					return ( array_key_exists('labels', $field) ? $field['labels'][$idx] : $key );
				}
			}
		}
		
		if ( ake ( 'auto_table_callback' , $field ) )
		{
			$val = $field['auto_table_callback']->apply ( array ( $val ) ) ;
		}
		
		return $val;
	}

	function getURL() {
		return $this->baseURL;
	}

}

?>
