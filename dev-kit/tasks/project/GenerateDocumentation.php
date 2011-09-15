<?php

class GenerateDocumentation extends Task {

    function getOptions() {

	$opt = new Field ();
	$opt->label = 'Select package';
	$opt->name = 'package';
	$opt->type = 'select';
	$opt->values = array(
	    'app' => $this->project->name,
	    'aenoa-server' => 'Aenoa Server',
	    'ajsf' => 'AJSF',
	    'acf' => 'ACF'
		);

	$options[] = $opt;


	return $options;
    }

    function process() {


	$root = ROOT;

	switch ($this->params['package']) {
	    case 'aenoa-server':
	    case 'acf':
	    case 'ajsf':
		$this->project->name = $this->params['package'];
		$root = dirname(ROOT) . DS ;
		$this->project->path = $root . $this->project->name ;
		break;
	}

			
	// Setup documentation root dir
	if (!$this->futil->dirExists($root . 'documentations' . DS . $this->project->name)) {
	    $this->futil->createDir($root . 'documentations', $this->project->name);
	}

	// Setup documentation config dir
	if (!$this->futil->dirExists($root . 'documentations' . DS . $this->project->name . '-nd')) {
	    $this->futil->createDir($root . 'documentations', $this->project->name . '-nd');
	}

	$this->futil->copy(DK_ASSETS . 'css' . DS . 'natural-aenoa.css', $root . 'documentations' . DS . $this->project->name . '-nd' . DS . 'natural-aenoa.css');

	$n = ucwords(str_replace('-', ' ', $this->project->name));

	$cmd = DK_NDF . 'NaturalDocs -s natural-aenoa -at ' . $n . ' -r -oft -i ' . $this->project->path . ' -o HTML ' . $root . 'documentations' . DS . $this->project->name . '/ -p ' . $root . 'documentations' . DS . $this->project->name . '-nd/';

	$this->view->setStatus('Running command: ' . $cmd);

	exec($cmd, $output, $ret);

	$this->view->setStatus('<ul><li>' . implode('</li><li>', $output) . '</li></ul>');

	if ($ret == 0) {
	    $this->view->setSuccess('Documentation generated: <a href="' . url() . '/documentations/' . $this->project->name . '/">' . $this->project->name . ' documentation</a>');
	} else {
	    $this->view->setError('Documentation NOT generated: ' . $this->project->name . ' / Returned: ' . $ret);
	}
    }

}

?>