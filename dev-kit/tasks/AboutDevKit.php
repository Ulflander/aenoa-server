<?php


class AboutDevKit extends Task {
	function process () {

		$this->view->setStatus(_('You must read the messages of tasks from bottom to top. <strong>The last message is allways at the top of all messages.</strong>'));

		$this->view->setStatus(_('Some tasks require configuration (for complex tasks) or confirmation (for critical/process-consuming tasks) or both before starting.'));

		$this->view->setStatus(_('The major part of the tasks return only messages of errors/success status messages.'));

		$this->view->setStatus(_('<strong>Task</strong>s and <strong>Wizard</strong>s are managed by the <strong>TaskManager</strong>.'));

		$this->view->setStatus(_('The Dev-Kit is composed of <strong>Tasks</strong>, that can be run one after another, using <strong>Wizards</strong>.'));

		$this->view->setStatus(_('The Aenoa Server Dev-Kit is only available in debug mode. You have to switch to debug mode, each time you need to run the Dev-Kit.'));

	}
}

?>