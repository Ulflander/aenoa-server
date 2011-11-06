<?php

class WebpagesController extends Controller {

	function beforeAction($action) {
		if (App::getUser()->getTrueLevel() > 0) {
			App::do401('Attempt to access webpages edition');
		}

		$this->view->layoutName = 'layout-backend';
	}

	function create() {
		if (!empty($this->data)) {

			if (ake('webpage_filename', $this->data) && $this->data['webpage_filename'] != '' && ake('webpage_folder', $this->data)) {

				$filename = $this->data['webpage_filename'];
				$folder = '';

				if ($this->data['webpage_folder'] != 'ROOT') {
					$folder .= $this->data['webpage_folder'];
				}

				if (ake('webpage_new_folder', $this->data) && $this->data['webpage_new_folder'] != '') {

					if ($this->futil->createDir(AE_APP_WEBPAGES . $folder, $this->data['webpage_new_folder'])) {
						$this->addResponse(sprintf(_('A new folder named %s has been created'), $this->data['webpage_new_folder']));

						$folder .= DS . $this->data['webpage_new_folder'];
					} else {
						$this->addResponse(sprintf(_('The new folder named %s has NOT been created. However, the new webpage will be created into "app/webpages/%s" folder. Please move it manually later.'), $this->data['webpage_new_folder'], $folder), self::RESPONSE_WARNING);
					}
				}

				$f = new File(AE_APP_WEBPAGES . $folder . DS . $filename . '.html', true);
				$f->write('<h1>' . _('This is a new webpage') . '</h1>');
				$f->close();

				App::redirect(url() . 'webpages/edit/' . $folder . DS . $filename);
			} else {
				$this->addResponse(_('Some data is missing to create the new webpage. Please give at least the new webpage name.'), self::RESPONSE_ERROR);
			}
		}

		$folders = $this->futil->getFolderTree(AE_APP_WEBPAGES, false, array('ROOT'));


		foreach ($folders as &$folder) {
			$folder = str_replace(AE_APP_WEBPAGES, '', $folder);
		}


		$this->view->set('folders', $folders);
	}

	function edit() {
		$webpage = implode('/', func_get_args());

		if (strpos($webpage, '.html') === false) {
			$webpage .= '.html';
		}

		if (!$this->futil->fileExists(AE_APP_WEBPAGES . $webpage)) {
			App::do404('Required webpage does not exist');
		}



		if (!empty($this->data) && ake('webpage_content', $this->data)) {
			$f = new File(AE_APP_WEBPAGES . $webpage);
			if ($f->write($this->data['webpage_content'])) {
				$this->addResponse(_('Webpage has been successfully edited'));
			} else {
				$this->addResponse(_('Webpage has NOT been edited. Check out file rights.'));
			}
			$f->close();
		}

		$f = new File(AE_APP_WEBPAGES . $webpage);

		$this->view->set('webpage_content', $f->read());
		$this->view->set('webpage_filename', $webpage);
	}

	function preview() {
		$webpage = implode('/', func_get_args());

		App::setAjax(false);

		if (!empty($this->data) && ake('webpage_content', $this->data)) {
			$hash = sha1($webpage);

			App::$query = $hash;

			$f = new File(AE_APP_WEBPAGES . $hash . '.html', true);

			$f->write($this->data['webpage_content']);

			$f->close();

			$this->setView(new Webpage());

			$this->view->layoutName = 'webpage';

			$this->view->render();

			$f->delete();
		} else {
			App::do404('No preview available');
		}

		App::end();
	}

}

?>