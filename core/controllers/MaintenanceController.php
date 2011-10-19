<?php

class MaintenanceController extends FileController {

	protected function beforeAction($action) {
		$this->view->useLayout = true;

		$this->view->layoutName = 'layout-backend';
	}

	function dbUnitTest() {
		$this->checkMaintenanceKey(null);


		$tester = new DBUnitTest ();

		$tester->run('MySQLEngine', array('host' => '192.168.0.42:8889',
			'login' => 'root',
			'password' => 'root',
			'database' => 'test'));

		$tester->run('CassandraEngine', array('host' => 'localhost', 'port' => '9160'));

		$tester->run('CouchDBEngine', array('host' => 'localhost', 'port' => '5984'));

		App::end();
	}

	function robots($key = null) {
		$this->checkMaintenanceKey($key);

		$f = new File(ROOT . 'robots.txt', true);

		$this->title = _('Edit robots.txt file');

		$this->edit('robots.txt', 'maintenance/robots');

		if (!App::isAjax()) {
			$this->view->appendTemplate('html' . DS . 'maintenance' . DS . 'robots-presets.thtml');
		}
	}

	function status($key = null) {
		$this->checkMaintenanceKey($key);
	}

	private function checkMaintenanceKey($key) {
		if (is_null($key) && !App::getUser()->isGod()) {
			App::do401(_('You have no right to maintain this application'));
		} else if ($key !== Config::get(App::SESS_STRING) && !App::getUser()->isGod()) {
			App::do401(_('No valid key nor user authorization'));
		}
	}

	function emptyLog($key = null) {
		$this->checkMaintenanceKey($key);

		$f = new File(ROOT . '.private' . DS . '.aenoalog', true);
		$f->write('');
		$f->close();

		$this->addResponse(_('Log file has been trashed'), 'success');

		$this->runAction('logs');
	}

	function logs($key = null) {
		$this->checkMaintenanceKey($key);

		$this->title = _('Application logs');

		$this->createView('html/maintenance/logs.thtml');

		$f = new File(ROOT . '.private' . DS . '.aenoalog', true);
		
		App::requireMemory(64);
		
		if ($f->isEmpty()) {
			$this->addResponse(_('Log file is empty'), 'info');
		} else {
			$this->addResponse('<pre>'. $f->read() .'</pre><br />', 'info');
		}
	}

	function update($key = null) {
		$this->checkMaintenanceKey($key);

		$this->title = _('Aenoa Server update process');

		$this->createView('html/maintenance/update.thtml');

		if (!is_null($key)) {
			$this->view->set('key', $key);
		}

		$this->view->render();

		$this->_sendMessage(_('Welcome to update process'), 'info');

		$futil = new FSUtil(ROOT);

		if (ake('do/dump', $this->data)) {
			if (!$futil->dirExists(AE_TMP . 'backup') && !$futil->createDir(AE_TMP, 'backup')) {
				$this->_sendMessage(_('Backup directory not created. Check file authorizations.'), 'error');
				$this->_sendMessage(_('Update process failed.'), 'error');
				return;
			}

			$errors = $this->mysqlDumps();
			if ($errors !== true) {
				pr($errors);
				$this->_sendMessage(_('Databases NOT dumped.'), 'error');
				$this->_sendMessage(_('Update process failed.'), 'error');
				return;
			} else {
				$this->_sendMessage(_('Databases dumped'), 'success');
			}

			unset($this->data['do/dump']);
		} else if (!empty($this->data)) {

			$this->_sendMessage(_('No database dump done'), 'info');
		}

		$ftp = new AeFTPUpdate ();

		if (!$ftp->isUsable()) {
			$this->_sendMessage(_('FTP connection for update is not usable. The Aenoa Server may be temporarily unavailable: please try later.'), 'error');
			return;
		} else {
			$this->_sendMessage(_('Connected to update server.'), 'success');
		}


		$trueRoot = setTrailingDS(dirname(ROOT));
		$futil = new FSUtil($trueRoot);

		// Check packages to update for Aenoa Package
		$versions = new AeVersions();
		$filelist = $ftp->ls();

		$packagesToUpdate = $versions->hasUpdates($filelist);

		// No package to update
		if (empty($this->data)) {
			if (empty($packagesToUpdate)) {
				App::$session->uset('Server.hasUpdate');
				$this->_sendMessage(_('Your system is up to date. No update needed for now. However you can backup your databases if you want to.'), 'info');
			} else {
				$this->_sendMessage(_('Some packages have to be updated. Please select packages to update or quit this page.'), 'critic');
			}
			$this->view->setAll(array(
				'packages' => $packagesToUpdate
			));
			$this->view->append('messages', 'update-form');
			return;
			// Selection done, we check if selection given by user is valid
		} else {

			$toUpdate = array();
			foreach ($this->data as $key => $val) {
				if (strpos($key, 'do/update/') === 0) {
					list ( $odd, $odd2, $packageName ) = explode('/', $key);
					$version = $versions->getVersionObject($packageName);
					if (!is_null($version) && $version->hasUpdate()) {
						$toUpdate[] = $version;
					}
				}
			}

			if (empty($toUpdate)) {
				$this->_sendMessage(sprintf(_('An error occured during selection. <a href="%s">Please restart update process</a>.'), url() . 'maintenance/update'), 'info');
				return;
			}
		}

		Maintenance::start();

		// ok we have in $toUpdate array the list of packages to update


		if (ake('do/backup', $this->data)) {
			if (!$futil->dirExists(AE_TMP . 'backup') && !$futil->createDir(AE_TMP, 'backup')) {
				$this->_sendMessage(_('Backup directory not created. Check file authorizations.'), 'error');
				$this->_sendMessage(_('Update process failed.'), 'error');
				return;
			}

			foreach ($toUpdate as &$pack) {
				$this->_sendMessage(sprintf(_('Backuping package %s'), $pack->getName()), 'progress', $pack->getPackageName() . '_backup');

				if ($futil->dirExists(AE_TMP . 'backup' . DS . $pack->getName())) {
					$this->_sendMessage(_('Backuping package %s'), 'progress', 'rem_backups');
					if (!$futil->removeDir(AE_TMP . 'backup' . DS . $pack->getName())) {
						$this->_sendMessage(_('Old backup directory not deleted. Check file authorizations.'), 'error');
						$this->_sendMessage(_('Update process failed.'), 'error');
					}
					$this->_sendMessage('', 'progressDone', 'rem_backups');
				}


				if (!$futil->createDir(AE_TMP, 'backup' . DS . $pack->getName())) {
					$this->_sendMessage(sprintf(_('Backup directory not created for package %s. Check file authorizations.'), $pack->getPackageName()), 'error');
					$this->_sendMessage(_('Update process failed.'), 'error');
					return;
				}


				if ($futil->copy($trueRoot . $pack->getName(), AE_TMP . 'backup' . DS . $pack->getName())) {
					$this->_sendMessage(sprintf(_('Package %s backuped'), $pack->getPackageName()), 'success');
				} else {
					$this->_sendMessage(sprintf(_('Package %s not backuped'), $pack->getPackageName()), 'warning');
				}

				$this->_sendMessage('', 'progressDone', $pack->getPackageName() . '_backup');
			}
		} else if (!empty($this->data)) {

			$this->_sendMessage(_('No backup done'), 'info');
		}


		$error = false;
		$serverPack = null;
		foreach ($toUpdate as &$pack) {
			if ($futil->dirExists($pack->getName() . DS . 'CVS')) {

				$this->_sendMessage(sprintf(_('Package %s not updated: there is CVS data inside !'), $pack->getPackageName()), 'warning');
				continue;
			}

			$this->_sendMessage(sprintf(_('Downloading package %s'), $pack->getPackageName()), 'progress', $pack->getPackageName());

			if ($ftp->get(AE_TMP . $pack->getUpdateFile(), $pack->getUpdateFile())) {
				$this->_sendMessage(sprintf(_('Package %s downloaded'), $pack->getPackageName()), 'success');
				$this->_sendMessage('', 'progressDone', $pack->getPackageName());
			} else {
				$error = true;
				$this->_sendMessage(sprintf(_('Package %s not downloaded'), $pack->getPackageName()), 'warning');
				$this->_sendMessage('', 'progressDone', $pack->getPackageName());
				continue;
			}

			$this->_sendMessage(sprintf(_('Extracting package %s'), $pack->getPackageName()), 'progress', $pack->getPackageName() . '_extract');
			$this->_extractError = '';
			if ($this->extract(AE_TMP . $pack->getUpdateFile(), $trueRoot . $pack->getName() . DS, $pack)) {
				$this->_sendMessage(sprintf(_('Package %s extracted'), $pack->getPackageName()), 'success');

				if ($pack->getPackageName() == 'aenoaserver') {
					$serverPack = $pack;
				}
			} else {
				$error = true;
				$this->_sendMessage(sprintf(_('Package %s not extracted, server returned: %s'), $pack->getPackageName(), $this->_extractError), 'warning');
			}
			$this->_sendMessage('', 'progressDone', $pack->getPackageName() . '_extract');
		}




		if ($error) {
			$this->_sendMessage(_('Some package updates failed.'), 'error');
		} else {

			App::$session->uset('Server.hasUpdate');

			if (!is_null($serverPack)) {
				$this->_sendMessage(_('Launching Aenoa Server update post process.'), 'notice');
				$postProcess = new UpdatePostProcess($this, $serverPack->getLocalVersion(), $serverPack->getRemoteUpdatedVersion());
			}

			if (!$error) {
				$this->_sendMessage(_('Update all done. You site is now updated and is no more under maintenance.'), 'success');
			} else {
				$this->_sendMessage(_('A problem occured during update post processing. Please contact administrator.'), 'success');
			}
		}

		Maintenance::stop();
	}

	function debug() {
		if (!App::getUser()->isGod()) {
			App::do403(_('You have no right to do this action'));
		}
	}

	function debugOn() {
		if (!App::getUser()->isGod()) {
			App::do403(_('You have no right to do this action'));
		}

		$this->_debug(true);

		App::redirectGlobal(url() . 'maintenance/debug');
	}

	function debugOff() {
		if (!App::getUser()->isGod()) {
			App::do403(_('You have no right to do this action'));
		}

		$this->_debug(false);

		App::redirectGlobal(url() . 'maintenance/debug');
	}

	private function _debug($debugModeOn = false) {

		if (debuggin() && $debugModeOn != false) {
			return;
		} else if (!debuggin() && $debugModeOn == false) {
			return;
		}

		$bf = new File(ROOT . 'app-conf.php', false);

		if ($bf->exists()) {
			$str = $bf->read();

			if (debuggin()) {
				$val = 'true';
				$new = 'false';
			} else {
				$val = 'false';
				$new = 'true';
			}

			preg_match_all('/(define[\s]{0,}\([\s]{0,}\'DEBUG\'[\s]{0,},[\s]{0,}' . $val . '[\s]{0,}\))/', $str, $matches);

			if (!empty($matches[0])) {
				$str = preg_replace('/(define[\s]{0,}\([\s]{0,}\'DEBUG\'[\s]{0,},[\s]{0,}' . $val . '[\s]{0,}\))/', 'define ( \'DEBUG\' , ' . $new . ' )', $str, 1);

				if ($bf->write($str)) {
					if (debuggin()) {
						$this->addResponse(_('Aenoa Server debug mode is now OFF.'), 'success');
					} else {
						$this->addResponse(_('Aenoa Server debug mode is now ON.'), 'success');
					}
				} else {
					$this->addResponse(_('DEBUG MODE has not been modified. Bootstrap file may be unwritable.'), 'error');
				}
				$bf->close();
			}
		}
	}

	private $_extractError = '';

	private function extract($file, $to, &$package, $overwrite = true) {
		$futil = new FSUtil($to);
		$to = setTrailingDS($to);

		$needed_dirs = array();
		$archive = new PclZip($file);
		return $archive->extract(PCLZIP_OPT_PATH, $to, PCLZIP_OPT_REMOVE_PATH, $package->getName()) !== 0;

		pr($archive_files);
		// Is the archive valid?
		if (0 === $archive_files) {
			$this->_extractError = _('Archive is not valid');
			return false;
		}

		if (0 == count($archive_files)) {
			$this->_extractError = _('There is no file in archive');
			return false;
		}

		// Determine any children directories needed (From within the archive)
		foreach ($archive_files as $_file) {
			if ('__MACOSX/' === substr($_file['filename'], 0, 9)) // Skip the OS X-created __MACOSX directory
				continue;

			$needed_dirs[] = $to . unsetTrailingDS($_file['folder'] ? $_file['filename'] : dirname($_file['filename']) );
		}
		$needed_dirs = array_unique($needed_dirs);
		foreach ($needed_dirs as $dir) {
			// Check the parent folders of the folders all exist within the creation array.
			if (unsetTrailingSlash($to) == $dir) // Skip over the working directory, We know this exists (or will exist)
				continue;
			if (strpos($dir, $to) === false) // If the directory is not within the working directory, Skip it
				continue;

			$parent_folder = dirname($dir);

			while (!empty($parent_folder) && unsetTrailingSlash($to) != $parent_folder && !in_array($parent_folder, $needed_dirs)) {
				$needed_dirs[] = $parent_folder;
				$parent_folder = dirname($parent_folder);
			}
		}

		asort($needed_dirs);

		pr($needed_dirs);
		return;
		// Create those directories if need be:
		foreach ($needed_dirs as $_dir) {
			if (!$futil->createDir(dirname($_dir), basename($_dir))) {
				// Only check to see if the dir exists upon creation failure. Less I/O this way.
				$this->_extractError = sprintf(_('Unable to create a required folder (%s)'), $_dir);
				return false;
			}
		}
		unset($needed_dirs);


		// Extract the files from the zip
		foreach ($archive_files as $_file) {
			if ($_file['folder'] || '__MACOSX/' === substr($_file['filename'], 0, 9)) { // Don't extract the OS X-created __MACOSX directory files
				continue;
			}

			$f = new File($to . $_file['filename'], true);
			$f->write($_file['content']);
			$f->close();
		}

		return true;
	}

	private function mysqlDumps() {
		$dbs = App::getAllDBs();
		$errors = array();
		foreach ($dbs as $id => &$db) {
			if ($db['engine']->isUsable()) {
				$ids = $db['engine']->getIdentifiers();
				$host = $ids['host'];
				if (strpos($host, ':') !== false) {
					list ( $host, $port ) = explode(':', $host);
					$host .= ' --port=' . $port;
				}
				$filename = ROOT . '.private' . DS . 'tmp' . DS . 'backup' . DS . $id . '-' . date('Y-m-d_H-i-s') . '.sql';
				$cmd = Config::get(Maintenance::DUMP_CMD) . ' --opt --host=' . $host . ' --user=' . $ids['login'] . ' --password=' . $ids['password'] . ' --databases ' . $ids['database'] . ' > ' . $filename;
				$res = exec($cmd);

				$f = new File($filename, false);
				if (!$f->exists() || $f->isEmpty()) {
					$errors[] = $id;
				}
			} else {
				$errors[] = $id;
			}
		}

		if (empty($errors)) {
			return true;
		}

		return $errors;
	}

	function _sendMessage($msg, $type, $id = null) {

		$this->view->setAll(array(
			'type' => $type,
			'message' => $msg
		));

		if ($type == 'progress' || $type == 'progressDone') {
			$this->view->set('id', $id);
		}

		$this->view->append('messages', 'message');
	}

	function checkContext($key = null, $nocheck = '') {
		$this->checkMaintenanceKey($key);

		$result = true;
		$warning = false;

		$futil = new FSUtil(ROOT);

		if ($nocheck == 'nocheck') {
			$nocheck = true;
		} else {
			$nocheck = false;
		}

		// Input validations
		$validate = array();

		if ($futil->fileExists('app-conf.php') || $nocheck == true) {
			$setConfiguration = false;
		} else {
			$setConfiguration = true;
			$validate['autosetup/config/app_name'] = DBValidator::NOT_EMPTY;
			$validate['autosetup/config/app_email'] = DBValidator::EMAIL;
		}

		// We check what to initialize : database, superadmin user, ...
		if (is_null($this->db) && Config::get(App::NO_DB) !== true && $nocheck != true && !$futil->isEmpty('app' . DS . 'structures')) {
			$setDatabase = true;
			$validate['autosetup/database/identifier'] = '^main$';
			$validate['autosetup/database/user'] = DBValidator::NOT_EMPTY;
			$validate['autosetup/database/password'] = DBValidator::NOT_EMPTY;
			$validate['autosetup/database/host'] = DBValidator::NOT_EMPTY;
			$validate['autosetup/database/db'] = DBValidator::NOT_EMPTY;
		} else {
			$setDatabase = false;
		}


		$hasToSetSomething = $setConfiguration || $setDatabase;
		$result = true;
		$db = null;
		if (!empty($this->data) && $this->validateInputs($validate) === true && $hasToSetSomething) {

			if ($setDatabase) {
				$db = new MySQLEngine ();
				if ($db->sourceExists(array(
						'host' => $this->data['autosetup/database/host'],
						'login' => $this->data['autosetup/database/user'],
						'password' => $this->data['autosetup/database/password'],
						'database' => $this->data['autosetup/database/db'],
					)) == false) {
					$result = false;
					$this->addResponse(_('System cannot connect to database'), self::RESPONSE_ERROR);
				}
			}

			if ($setConfiguration && $this->data['autosetup/config/app_name'] == 'New Application') {
				$result = false;
				$this->addResponse(_('Please give a new name to your application'), self::RESPONSE_ERROR);
			}


			if ($result) {
				if ($setConfiguration) {

					$this->config[] = '';
					$this->config[] = 'Config::set(App::APP_NAME,\'' . $this->data['autosetup/config/app_name'] . '\');';
					$this->config[] = 'Config::set(App::APP_EMAIL,\'' . $this->data['autosetup/config/app_email'] . '\');';
					$this->config[] = 'Config::set(App::SESS_STRING,\'' . User::getClearNewPassword(30) . '\');';
					$this->config[] = 'Config::set(App::USER_CORE_SYSTEM,' . (ake('autosetup/config/core_user_system', $this->data) && $this->data['autosetup/config/core_user_system'] == 'true' ? 'true' : 'false') . ');';
					$this->config[] = 'Config::set(App::USER_REGISTER_AUTH,' . (ake('autosetup/config/core_user_system_register', $this->data) && $this->data['autosetup/config/core_user_system_register'] == 'true' ? 'true' : 'false') . ');';

					$this->config[] = '';
				}

				if ($setDatabase) {

					$this->config[] = '';
					$this->config[] = 'App::declareDatabase ( \'main\' , \'MySQLEngine\' , array ( \'host\' => \'' . $this->data['autosetup/database/host'] . '\' ,';
					$this->config[] = "\t" . '\'login\' => \'' . $this->data['autosetup/database/user'] . '\' , ';
					$this->config[] = "\t" . '\'password\' => \'' . $this->data['autosetup/database/password'] . '\' , ';
					$this->config[] = "\t" . '\'database\' => \'' . $this->data['autosetup/database/db'] . '\' ) , null , true ) ; ';
					$this->config[] = '';
				}

				if (!empty($this->config)) {
					if ($futil->fileExists('app-conf.php')) {
						$filename = 'app-conf-' . date('Y-m-d-H-i', time()) . '.php';

						$this->addResponse(_('A configuration file has been found. You should merge the different config files into one.'), self::RESPONSE_WARNING);
					} else {
						$filename = 'app-conf.php';
					}

					$f = new File(ROOT . $filename, true);
					if ($f->write("<?php \n\n " . implode("\n", $this->config) . "\n\n?>")) {
						$this->addResponse(_('Config file has been written'), self::RESPONSE_SUCCESS);

						$f = new File(ROOT . 'index.php');
						$f->write(str_replace('App::start', 'require_once(\'' . $filename . '\') ;' . "\n\n" . 'App::start', $f->read()));
					} else {
						$this->addResponse(_('Config file has NOT been written'), self::RESPONSE_ERROR);
					}
					$f->close();
				}
			}
		} else if ($hasToSetSomething) {
			$result = false;
		}

		$this->createView('html/maintenance/auto-setup.thtml');
		$this->view->set('key', $key);
		if ($result == false) {
			$this->view->set('setDatabase', $setDatabase);
			$this->view->set('setConfiguration', $setConfiguration);

			$this->renderView();
			App::end();
		} else {
			$this->view->set('setDatabase', false);
			$this->view->set('setConfiguration', false);

			$this->renderView();
		}


		$this->_sendMessage('Starting deployment of application. DO NOT CLOSE THIS PAGE until end message.', 'notice');

		if (App::getUser()->isGod()) {
			$this->_sendMessage('ROOT path is ' . ROOT, 'notice');
		}





		/*
		 * DATABASE SETUP
		 */
		$dbs_to_install = &App::getAllDBs();

		foreach ($dbs_to_install as $id => &$db) {
			$this->_sendMessage('Preparing to deploy ' . count($db['structure']) . ' table(s) in database ' . $id, 'info');

			if ($db['engine']->setStructure($db['structure'], true) == false) {
				$result = false;

				$this->_sendMessage('Deployment of ' . count($db['structure']) . ' table(s) in database ' . $id . ' failed.', 'error');
				$this->_sendMessage(implode('<br />', $db['engine']->getLog()), 'error');
			} else {
				$this->_sendMessage('Deployment of ' . count($db['structure']) . ' table(s) in database ' . $id . ' done.', 'info');
			}
		}






		/*
		 * CREATE FOLDERS
		 */
		$folders = array(
			'assets',
			'assets' . DS . 'css',
			'assets' . DS . 'js',
			'app',
			'app' . DS . 'structures',
			'app' . DS . 'controllers',
			'app' . DS . 'locale',
			'app' . DS . 'libs',
			'app' . DS . 'services',
			'app' . DS . 'hooks',
			'plugins',
			'app' . DS . 'templates',
			'app' . DS . 'templates' . DS . 'behaviors',
			'app' . DS . 'templates' . DS . 'html',
			'app' . DS . 'webpages',
			'downloads',
			'.private',
			'.private' . DS . 'cache',
			'.private' . DS . 'logs',
			'.private' . DS . 'tmp',
			'.private' . DS . 'sessions');

		$files = array();

		foreach ($folders as $folder) {
			if ($futil->dirExists($folder) == false) {
				if ($futil->createDir(ROOT, $folder) == true) {
					switch ($folder) {
						case 'webpages':
							$files[$folder . DS . 'index.html'] = AE_TEMPLATES . 'index.html';
							break;
					}
					$this->_sendMessage('Folder "' . $folder . '" has been created.', 'success');
				} else {

					$this->_sendMessage('Folder "' . $folder . '" has NOT been created.', 'error');
					$result = false;
				}
			}

			@chmod(ROOT . $folder, 0777);
		}







		/*
		 * FILES COPY IF REQUIRED
		 */
		foreach ($files as $to => $from) {
			$f2 = new File($from, false);
			if ($futil->fileExists($to) == false && $f2->exists() == true) {
				$f = new File(ROOT . $to, true);

				if ($f->write($f2->read())) {
					$this->_sendMessage('File "' . $folder . '" has been created.', 'success');
					$this->view->setAll(array(
						'type' => 'success',
						'message' => 'File "' . $to . '" has been created.'
					));
				} else {

					$this->_sendMessage('File "' . $folder . '" has NOT been created.', 'error');
					$result = false;
				}
			} else {
				$this->_sendMessage('File "' . $to . '" exists yet or file "' . $from . '" does not exist.', 'notice');
			}
		}




		/*
		 * PROTECT PRIVATE DIRECTORY
		 */
		if ($futil->dirExists('.private') == true) {
			if ($futil->fileExists('.private' . DS . '.htaccess') == false) {
				$f = new File(ROOT . '.private' . DS . '.htaccess', true);
				if ($f && $f->write($this->getCommonHTACCESSProtection()) && $f->close()) {
					$this->_sendMessage('Private folder has been protected.', 'success');
				} else {

					$result = false;
					$this->_sendMessage('Private folder has NOT been protected. Please use AutoProtect to recreate security context.', 'error');
				}
			} else {

				$this->_sendMessage('Private folder SEEMS to be protected. If you have modified the .htaccess file in the ".private" folder, you may have broke protection.', 'info');
			}
		}




		/*
		 * Create root HTACCESS
		 */
		if ($futil->fileExists('.htaccess') == false) {
			$f = new File(ROOT . '.htaccess', true);
			if ($f && $f->write($this->getRootHTACCESS()) && $f->close()) {
				$this->_sendMessage('Root htaccess file has been written.', 'success');
			} else {

				$this->_sendMessage('Root htaccess file has NOT been written. You should check right management. This htaccess is required for Aenoa Server.', 'error');
			}
		} else {
			$this->_sendMessage('Root htaccess file SEEMS to be setuped. If you have modified this .htaccess, you may have broke URL rewriting rules.', 'info');
		}





		/*
		 * PROTECT APP DIRECTORY
		 */
		if ($futil->fileExists('app' . DS . '.htaccess') == false) {
			$f = new File(ROOT . 'app' . DS . '.htaccess', true);
			if ($f && $f->write($this->getCommonHTACCESSProtection()) && $f->close()) {
				$this->_sendMessage('App folder has been protected.', 'info');
			} else {
				$this->_sendMessage('App folder has NOT been protected, and is unable to acces to .htaccess file in App folder. Please check out rights management.', 'error');
			}
		} else {
			$this->_sendMessage('App folder SEEMS to be protected. If you have modified the .htaccess file in the "app" folder, you may have broke protection.', 'info');
		}



		/**
		 * Adding favicon if no one :)
		 */
		if ($futil->fileExists('favicon.ico') == false && $futil->fileExists('favicon.ico') == false) {
			$f = new File(AE_ASSETS . 'favicon.png', false);
			if ($f->exists()) {
				$f->copy(ROOT . 'favicon.png');
				$f->close();
			}
		}


		/*
		 * PROTECT AENOA-SERVER DIRECTORY
		 */
		$futil = new FSUtil(AE_SERVER);
		if ($futil->fileExists('.htaccess') == false) {
			$f = new File(AE_SERVER . '.htaccess', true);
			if ($f && $f->write($this->getCommonHTACCESSProtection()) && $f->close()) {
				$this->_sendMessage('Aenoa Server folder has been protected.', 'info');
			} else {

				$result = false;
				$this->_sendMessage('Aenoa Server folder has NOT been protected, and is unable to acces to .htaccess file in Aenoa Server Folder. Please check out rights management.', 'error');
			}
		} else {
			$this->_sendMessage('Aenoa Server folder SEEMS to be protected. If you have modified the .htaccess file in the "aenoa-server" folder, you may have broke protection.', 'info');
		}




		/*
		 * REGENERATE SOME CACHES
		 */
		// Hook cache
		if (Hook::regeneratePathsCache()) {
			$this->_sendMessage('Hooks path cache regenerated.', 'info');
		} else {
			$this->_sendMessage('Hooks path cache NOT regenerated. Please check file authorizations for PHP in .private folder.', 'error');
			$result = false;
		}



		/*
		 * CREATE FIRST GROUP AND FIRST USER
		 */
		if (!is_null($this->db) && Config::get(App::USER_CORE_SYSTEM) == true && $this->db->tableExists('ae_users')) {

			$group = $this->db->findFirst('ae_groups', array('level' => 0));
			if (empty($group)) {
				$group = array(
					'label' => _('Super administrator'),
					'level' => 0
				);

				if ($this->db->add('ae_groups', $group)) {
					$this->_sendMessage('A first group has been created. It is the super administrator group.', 'success');
				} else {
					$result = false;
					$this->_sendMessage('First group (super administrators, level:0) has not been created. Please create it manually.', 'error');
				}
			}


			$group = $this->db->findFirst('ae_groups', array('level' => 99));
			if (empty($group)) {
				$group = array(
					'label' => _('Registrated'),
					'level' => 99
				);

				if ($this->db->add('ae_groups', $group)) {
					$this->_sendMessage('A second group has been created. It is the registrated users group.', 'success');
				} else {
					$result = false;
					$this->_sendMessage('Second group (registrated users, level:99) has not been created. Please create it manually.', 'error');
				}
			}

			$user = $this->db->findFirst('ae_users');
			if (empty($user)) {
				$password = User::getClearNewPassword();
				$user = array(
					'email' => Config::get(App::APP_EMAIL),
					'password' => $password,
					'group' => 1,
					'firstname' => 'Admin',
					'lastname' => 'Istrator'
				);

				$content = array();
				$content[] = 'Hello on ' . Config::get(App::APP_NAME);
				$content[] = '<br />';
				$content[] = '<br />';
				$content[] = 'An administrator has been created on your platform. Here are his identifiers:';
				$content[] = 'Login: ' . Config::get(App::APP_EMAIL);
				$content[] = 'Password: ' . $password;
				$content[] = '<br />';
				$content[] = '<br />';
				$content[] = 'Your platform is now located at <a href="' . url() . '">' . url() . '</a>.';
				$content[] = '<br />';
				$content[] = '<br />';
				$content[] = 'Friendly,';
				$content[] = 'Aenoa Server';


				$mail = array(
					'to' => Config::get(App::APP_EMAIL),
					'content' => implode("\n", $content),
					'subject' => '[' . Config::get(App::APP_NAME) . '] First administrator created'
				);

				$mailer = new AeMail ();
				if ($this->db->add('ae_users', $user) && $mailer->sendThis($mail)) {
					$this->_sendMessage('A first administrator has been created. An email has been send to the contact address, containing identifiers to log in the application.', 'success');
				} else {
					$result = false;
					$this->_sendMessage('First administrator has not been created. Please create it manually.', 'error');
				}
			} else {
				$this->_sendMessage('Application does not require any new user.', 'info');
			}
		}



		/*
		 * CHECK FOR GPC QUOTES
		 */
		if (get_magic_quotes_gpc() == 1) {
			$this->_sendMessage('System detected that ini.php "magic_quotes_gpc" directive is set to on. It should be set to off. Please correct ini.php.', 'warning');
			$warning = true;
		}


		/*
		 * CHECK FOR GD PHP Extension
		 */
		if (!function_exists('imagecreate')) {
			$this->_sendMessage('Aenoa Server requires GD library for captcha feature. It seems that GD library is not deployed in this PHP install.', 'warning');
			$warning = true;
		}

		/*
		 * CHECK FOR PHP-gettext
		 */
		if (_('en_US') == 'en_US') {
			$this->_sendMessage('Aenoa Server requires PHP-Gettext. It seems that PHP-Gettext is not deployed in this PHP install, or base local "en_US" is not available.', 'warning');
			$warning = true;
		}


		/*
		 * Call check context hooks in application or plugins
		 */
		new Hook('CheckContext');


		if (defined('DEBUG') && DEBUG == true) {
			$this->_sendMessage('Please notice that you are in debug mode for the moment.', 'warning');
		}

		/*
		 * END
		 */

		if ($result) {
			if ($warning) {
				$this->_sendMessage('Deployment of application done, but warnings have been triggered. Please check.', 'warning');
			} else if ($setDatabase) {
				$this->_sendMessage('Deployment of application almost done.', 'success');
			} else {
				$this->_sendMessage('Deployment of application done.', 'success');
			}
		} else {
			$this->_sendMessage('We are sorry to notice that some failures occured during deployement of application. Check messages below to identify reasons of failure, and solve problems before running application.', 'critic');
		}
	}

	private $config = array();

	private function getCommonHTACCESSProtection() {
		$str = array('# Auto generated HTACCESS protection / Aenoa Server / Generated : ' . date('Y-m-d H:i:s', time()));
		$str[] = 'order allow,deny';
		$str[] = 'deny from all';
		$str[] = '';
		$str[] = '';

		return implode("\n", $str);
	}

	private function getRootHTACCESS() {
		$relative = retrieveBasePath();

		$str = array('# Auto generated HTACCESS root / Aenoa Server / Generated : ' . date('Y-m-d H:i:s', time()));
		$str[] = 'Options +FollowSymlinks';
		$str[] = '';
		$str[] = 'ErrorDocument 404 ' . $relative . 'do/error/404';
		$str[] = 'ErrorDocument 403 ' . $relative . 'do/error/403';
		$str[] = '';
		$str[] = '<IfModule mod_rewrite.c>';
		$str[] = 'RewriteEngine on';
		$str[] = '';
		$str[] = '#If you want to ensure that the www. subdomain will be used, uncomment the two lines below.';
		$str[] = '#RewriteCond		%{HTTP_HOST} !^www.*$';
		$str[] = '#RewriteRule		^(.*) http://www.%{HTTP_HOST}/$1 [L,R=301]';
		$str[] = '';
		$str[] = 'RewriteCond %{REQUEST_FILENAME} !-d';
		$str[] = 'RewriteCond %{REQUEST_FILENAME} !-f';
		$str[] = 'RewriteRule ^(.*)$ index.php?query=$1 [QSA,L]';
		$str[] = '';
		$str[] = '</IfModule>';

		return implode("\n", $str);
	}

}
