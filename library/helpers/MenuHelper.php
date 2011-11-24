<?php

class MenuHelper {

	/**
	 * Displays a menu by retrieving Webpage pages.
	 * 
	 * @param string $class [optional] Class names for the UL element
	 * @param boolean $showCurrent [optional] Should the current page be shown, default: true
	 * @param string [optional] Class name for A elements
	 * @return void
	 */
	public static function displayWebpageMenu($class = '', $showCurrent = true, $innerclass = '') {
		$menu = Webpage::getWebpagePages();
		$current = Webpage::getCurrent();
		$default = Webpage::getDefault();

		$str = '<ul id="webpages-menu" class="' . $class . '">' . "\n";
		foreach ($menu as $menuItem) {
			if ($menuItem['filename'] != $current) {
				$str .= '<li><a href="' . url();
				if ($default != $menuItem['filename']) {
					$str .= $menuItem['filename'];
				}
				$str .= '" class="'.$innerclass.'">' . $menuItem['title'] . '</a></li>' . "\n";
			} else if ($showCurrent == true) {
				$str .= '<li class="current '.$innerclass.'">' . $menuItem['title'] . '</li>' . "\n";
			}
		}
		$str .= '</ul>' . "\n";

		echo $str;
	}

	/**
	 * Generates and displays a menu of the whole backend
	 * 
	 * @param object $class [optional] Class names for the UL element
	 * @param object $showCurrent [optional] Should the current page be shown, default: true
	 * @return void
	 */
	public static function displayBackendMenu($struct = 'main', $class = '', $showCurrent = true) {

		$query = App::getQueryString();
		$baseURL = url();
		$str = '<ul class="no-list-style">';
		$str .= '<li class=""><a href="' . url() . 'maintenance/status" class="icon16 home">' . ucfirst(sprintf(_('Manager home'), $struct)) . '</a></li>' . "\n";
		$js = '<script type="text/javascript">ajsf.load("ae-block-switch");ajsf.ready(function(){';


		echo $str . '<li>';
		$str = '';

		$str .= '<li><a href="#" id="maintenance-menu-btn" class="icon16 group">' . _('Maintenance') . '</a></li>' . "\n";
		$str .= '<li><ul id="maintenance-menu" class="no-list-style aemenu ' . $class . '">' . "\n";
		$str .= '<li><a href="' . url() . 'maintenance/logs" title="' . _('Logs') . '" class="icon16 files">' . _('Logs') . '</a></li>' . "\n";
		$str .= '<li><a href="' . url() . 'maintenance/update" title="' . _('Updates') . '" class="icon16 update">' . _('Updates') . '</a></li>' . "\n";
		$str .= '<li><a href="' . url() . 'maintenance/robots" title="' . _('robots.text file') . '" class="icon16">' . _('robots.txt') . '</a></li>' . "\n";
		$str .= '<li><a href="' . url() . 'maintenance/debug" title="' . _('Debug mode') . '" class="icon16 warning">' . _('Debug mode') . '</a></li>' . "\n";
		$str .= '</ul></li>' . "\n";

		$js .= 'new ajsf.AeBlockSwitch({button:_("#maintenance-menu-btn"),element:_("#maintenance-menu"),openedClass:"icon16 down",closedClass:"icon16 play"}).' .
			($query->getAt() == 'maintenance' ? 'open' : 'close')
			. '();';

		echo $str;
		$str = '';

		if (Config::get(App::USER_CORE_SYSTEM) == true) {
			$str .= '<li><a href="#" id="users-menu-btn" class="icon16 group">' . _('Users') . '</a></li>' . "\n";
			$str .= '<li><ul id="users-menu" class="no-list-style aemenu ' . $class . '">' . "\n";
			$str .= '<li><a href="' . url() . 'user-manage/new-users" title="' . _('Manage new users') . '" class="icon16 user">' . _('Manage new users') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'user-manage/all-users" title="' . _('Manage all users') . '" class="icon16 group">' . _('Manage users') . '</a></li>' . "\n";
			$str .= '</ul></li>' . "\n";

			$js .= 'new ajsf.AeBlockSwitch({button:_("#users-menu-btn"),element:_("#users-menu"),openedClass:"icon16 down",closedClass:"icon16 play"}).' .
				($query->getAt() == 'user-manage' ? 'open' : 'close')
				. '();';

			echo $str;
		}

		$structures = App::getAllDBs();
		$str = '';

		foreach ($structures as $id => &$db) {
			self::displayStructuresMenu($id, '', true, true);
		}

		$str .= '</li>';

		$menu = array();

		$futil = new FSUtil(ROOT);
		$files = $futil->getTree(AE_APP_WEBPAGES);

		$str .= '<li><a href="#" id="webpages-menu-btn" class="expanded">' . _('Webpages') . '</a></li>' . "\n";
		$str .= '<li><ul id="webpages-menu" class="no-list-style aemenu ' . $class . '">' . "\n";
		foreach ($files as $file) {
			if (strpos($file, '.html') === false) {
				continue;
			}
			$file = str_replace(AE_APP_WEBPAGES, '', $file);
			$str .= '<li><a href="' . url() . 'webpages/edit/' . $file . '" title="' . sprintf(_('Edit %s webpage'), $file) . '" class="icon16 file">' . $file . '</a></li>' . "\n";
		}
		$str .= '<li><a href="' . url() . 'webpages/create" title="' . _('Add a new webpage') . '" class="icon16 add">' . _('Add a new webpage') . '</a></li>' . "\n";
		$str .= '</ul>' . "\n";

		$js .= 'new ajsf.AeBlockSwitch({button:_("#webpages-menu-btn"),element:_("#webpages-menu"),openedClass:"icon16 down",closedClass:"icon16 play"}).' .
			($query->getAt() == 'webpages' ? 'open' : 'close')
			. '();';

		$str .='</li>';

		if (debuggin()) {
			$str .= '<li><a href="#" id="dev-menu-btn" class="">' . _('Development') . '</a></li>' . "\n";
			$str .= '<li><ul id="dev-menu" class="no-list-style aemenu ' . $class . '">' . "\n";
			$str .= '<li><a href="' . url() . 'dev/Backup" title="' . _('Backup application') . '" class="icon16 backup">' . _('Backup application') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'dev/PHPI18n" title="' . _('Extract I18n strings using xgetext utility') . '" class="icon16 i18n">' . _('Extract I18n') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'dev/GenerateStructureDocFile" title="' . _('Update structures documentation files by extracting structure description') . '" class="icon16 manual">' . _('Update structures doc') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'dev/GenerateDocumentation" title="' . _('Create documentation using NaturalDocs') . '" class="icon16 manual">' . _('Create documentation') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'dev/EhtmlToThtml" title="' . _('Generate templates from EHtml files to THtml files') . '" class="icon16 wizard">' . _('Generate templates') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'dev/HashMachine" title="' . _('Make some hash') . '" class="icon16 run">' . _('Hash machine') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'dev/AssetsCompressor" title="' . _('Compress assets') . '" class="icon16 package">' . _('Compress assets') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'dev/RevertAssetsCompressor" title="' . _('Revert assets compression') . '" class="icon16 package">' . _('Revert assets compression') . '</a></li>' . "\n";
			$str .= '<li><a href="' . url() . 'dev/ExploreTasks" title="' . _('List tasks') . '" class="icon16 files">' . _('Explore tasks') . '</a></li>' . "\n";
			$str .= '</ul></li>' . "\n";

			$js .= 'new ajsf.AeBlockSwitch({button:_("#dev-menu-btn"),element:_("#dev-menu"),openedClass:"icon16 down",closedClass:"icon16 play"}).' .
				($query->getAt() == QueryString::DEV_TOKEN ? 'open' : 'close')
				. '();';
		}
		echo $str . '</ul>';

		$js .= '});</script>';
		echo $js;
	}

	/**
	 * Generates and displays a menu using database structures.
	 * 
	 * @param object $class [optional] Class names for the UL element
	 * @param object $showCurrent [optional] Should the current page be shown, default: true
	 * @return void
	 */
	public static function displayStructuresMenu($struct = 'main', $class = '', $showCurrent = true, $encapsulate = false) {
		$db = App::getDatabase($struct);
		$baseURL = url() . 'database/' . $struct . '/';

		$str = '';

		if ($encapsulate) {
			$str .= '<li><a href="#" id="menu-db-' . $struct . '-btn" class="icon16 db">' . ucfirst(sprintf(_('%s database (%s)'), $struct, str_replace('Engine', '', get_class($db)))) . '</a></li><li>' . "\n";
		}

		$str .= '<ul id="menu-db-' . $struct . '" class="no-list-style aemenu ' . $class . '">' . "\n";

		if (!$encapsulate) {
			$str .= '<li class="caption">' . ucfirst(sprintf(_('%s database'), $struct)) . '</li>' . "\n";
		}

		if ($db->isUsable()) {
			$structure = $db->getStructure();

			foreach ($structure as $table => $fields) {
				$class = 'icon16';
				switch ($table) {
					case 'ae_users': $class = 'icon16 user';
						break;
					case 'ae_groups': $class = 'icon16 group';
						break;
					case 'ae_api_keys': $class = 'icon16 lock';
						break;
					default: $class = 'icon16 db';
				}

				$tableName = _(ucfirst(humanize($table, '_')));

				if (strpos($tableName, 'Ae ') !== false) {
					$tableName = str_replace('Ae ', '', $tableName);
				}

				$str .= '<li><a href="' . $baseURL . $table . '/index';
				$str .= '" class="' . $class . '">' . $tableName . '</a></li>' . "\n";
			}
		}
		$str .= '</ul>' . "\n";

		if ($encapsulate) {
			$str .= '</li>';

			$str .= '<script type="text/javascript">ajsf.load("ae-block-switch");ajsf.ready(function(){';
			$str .= 'new ajsf.AeBlockSwitch({button:_("#menu-db-' . $struct . '-btn"),element:_("#menu-db-' . $struct . '"),openedClass:"icon16 down",closedClass:"icon16 play"}).' .
				(App::getQueryString()->getAt(1) == $struct ? 'open' : 'close')
				. '();});</script>';
		}

		echo $str;
	}

}

?>