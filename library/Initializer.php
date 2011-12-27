<?php


/*
 * Class: Initializer
 *
 * Initializer runs a check-list on current Aenoa Server and application environment *when debugging mode in ON*.
 * 
 * Initializer is called by <App::start>.
 * 
 * Actions:
 * - create required folders
 * - set mode on file/folders
 * - setup Apache protections and url-rewriting (htaccess...)
 * - check some required PHP or modules features
 * 
 * 
 */
class Initializer extends Object {
	
	function __construct ()
	{
		/*
		 * DATABASE SETUP
		 
		$dbs_to_install = &App::getAllDBs();

		foreach ($dbs_to_install as $id => &$db) {
			$this->log('Preparing to deploy ' . count($db['structure']) . ' table(s) in database ' . $id, 'info');

			if ($db['engine']->setStructure($db['structure'], true) == false) {
				$result = false;

				$this->log('Deployment of ' . count($db['structure']) . ' table(s) in database ' . $id . ' failed.');
				$this->log(implode('<br />', $db['engine']->getLog()));
			} else {
				$this->log('Deployment of ' . count($db['structure']) . ' table(s) in database ' . $id . ' done.', 'info');
			}
		}
		*/

		$result = true ;
		$futil = new FSUtil(ROOT) ;


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
					
					chmod(ROOT . $folder, 0777);
				} else {
					$this->log('Folder "' . $folder . '" has NOT been created.');
					$result = false;
				}
			}
		}







		/*
		 * FILES COPY IF REQUIRED
		 */
		foreach ($files as $to => $from) {
			$f2 = new File($from, false);
			if ($futil->fileExists($to) == false && $f2->exists() == true) {
				$f = new File(ROOT . $to, true);

				if ( !$f->write($f2->read())) {

					$this->log('File "' . $folder . '" has NOT been created.');
					$result = false;
				}
			} else {
				$this->log('File "' . $to . '" exists yet or file "' . $from . '" does not exist.');
			}
		}




		/*
		 * PROTECT PRIVATE DIRECTORY
		 */
		if ($futil->dirExists('.private') == true) {
			if ($futil->fileExists('.private' . DS . '.htaccess') == false) {
				$f = new File(ROOT . '.private' . DS . '.htaccess', true);
				if (!$f || !$f->write($this->getCommonHTACCESSProtection()) || !$f->close()) {

					$result = false;
					$this->log('Private folder has NOT been protected. Please use AutoProtect to recreate security context.');
				}
			}
		}




		/*
		 * Create root HTACCESS
		 */
		if ($futil->fileExists('.htaccess') == false) {
			$f = new File(ROOT . '.htaccess', true);
			if (!$f || !$f->write($this->getRootHTACCESS()) || !$f->close()) {

				$this->log('Root htaccess file has NOT been written. You should check right management. This htaccess is required for Aenoa Server.');
			}
		}





		/*
		 * PROTECT APP DIRECTORY
		 */
		if ($futil->fileExists('app' . DS . '.htaccess') == false) {
			$f = new File(ROOT . 'app' . DS . '.htaccess', true);
			if (!$f || !$f->write($this->getCommonHTACCESSProtection()) || !$f->close()) {
				$this->log('App folder has NOT been protected, and is unable to acces to .htaccess file in App folder. Please check out rights management.');
			}
		}



		/*
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
			if (!$f || !$f->write($this->getCommonHTACCESSProtection()) || !$f->close() ) {
				$result = false;
				$this->log('Aenoa Server folder has NOT been protected, and is unable to acces to .htaccess file in Aenoa Server Folder. Please check out rights management.');
			}
		}




		/*
		 * REGENERATE SOME CACHES
		 */
		// Hook cache
		if (!Hook::regeneratePathsCache()) {
			$this->log('Hooks path cache NOT regenerated. Please check file authorizations for PHP in .private folder.');
			$result = false;
		}



		/*
		 * CREATE FIRST GROUP AND FIRST USER
		
		if (!is_null($this->db) && Config::get(App::USER_CORE_SYSTEM) == true && $this->db->tableExists('ae_users')) {

			$group = $this->db->findFirst('ae_groups', array('level' => 0));
			if (empty($group)) {
				$group = array(
					'label' => _('Super administrator'),
					'level' => 0
				);

				if ($this->db->add('ae_groups', $group)) {
					$this->log('A first group has been created. It is the super administrator group.', 'success');
				} else {
					$result = false;
					$this->log('First group (super administrators, level:0) has not been created. Please create it manually.');
				}
			}


			$group = $this->db->findFirst('ae_groups', array('level' => 99));
			if (empty($group)) {
				$group = array(
					'label' => _('Registrated'),
					'level' => 99
				);

				if ($this->db->add('ae_groups', $group)) {
					$this->log('A second group has been created. It is the registrated users group.', 'success');
				} else {
					$result = false;
					$this->log('Second group (registrated users, level:99) has not been created. Please create it manually.');
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
					$this->log('A first administrator has been created. An email has been send to the contact address, containing identifiers to log in the application.', 'success');
				} else {
					$result = false;
					$this->log('First administrator has not been created. Please create it manually.');
				}
			} else {
				$this->log('Application does not require any new user.', 'info');
			}
		}
 */


		/*
		 * CHECK FOR GPC QUOTES
		 */
		if (get_magic_quotes_gpc() == 1) {
			$this->log('System detected that ini.php "magic_quotes_gpc" directive is set to on. It should be set to off. Please correct ini.php.');
			$warning = true;
		}


		/*
		 * CHECK FOR GD PHP Extension
		 */
		if (!function_exists('imagecreate')) {
			$this->log('Aenoa Server requires GD library for captcha feature. It seems that GD library is not deployed in this PHP install.');
			$warning = true;
		}

		/*
		 * CHECK FOR PHP-gettext
		 */
		if (_('en_US') == 'en_US') {
			$this->log('Aenoa Server requires PHP-Gettext. It seems that PHP-Gettext is not deployed in this PHP install, or base local "en_US" is not available.');
			$warning = true;
		}


		/*
		 * Call check context hooks in application or plugins
		 */
		new Hook('CheckContext');

		
		/*
		 * END
		if ($result) {
			if ($warning) {
				$this->log('Deployment of application done, but warnings have been triggered. Please check.');
			} else if ($setDatabase) {
				$this->log('Deployment of application almost done.', 'success');
			} else {
				$this->log('Deployment of application done.', 'success');
			}
		} else {
			$this->log('We are sorry to notice that some failures occured during deployement of application. Check messages below to identify reasons of failure, and solve problems before running application.', 'critic');
		}
		 */

	}


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

?>