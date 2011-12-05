<?php

class GenerateStructureDocFile extends Task {


	private $rendering = array() ;

	private $group = null ;

	function getOptions ()
	{
		$options = array () ;

		$opt = new Field () ;
		$opt->label = 'Include core Aenoa users structure' ;
		$opt->name = 'include_users' ;
		$opt->type = 'checkbox' ;
		$opt->attributes['value'] = 'true' ;

		$options[] = $opt ;

		$opt = new Field () ;
		$opt->label = 'Include core Aenoa API Authentication control structure' ;
		$opt->name = 'include_api' ;
		$opt->type = 'checkbox' ;
		$opt->attributes['value'] = 'true' ;

		$options[] = $opt ;



		$lg = $this->futil->getDirsList(ROOT.'app'.DS.'locale');

		$langs = array ( 'nolang' => 'No language binding' ) ;

		foreach ( $lg as $l )
		{
			$langs[$l['name']] = $l['name'];
		}

		$opt = new Field () ;
		$opt->label = 'Select language' ;
		$opt->name = 'language' ;
		$opt->type = 'select' ;
		$opt->values = $langs ;

		$options[] = $opt ;


		return $options ;
	}


	function process ()
	{

		if ( $this->params['language'] != 'nolang' )
		{
			$this->view->setStatus ( 'Starting structure doc generation with language: ' . $this->params['language'] ) ;

			$_i18n = new I18n ( $this->params['language'] ,'default', 'UTF8', ROOT.'app'.DS.'locale' ) ;
		} else {
			$this->view->setStatus ( 'Starting structure doc generation with core language' ) ;
		}

		$this->renderFileHeader ()  ;


		$structures = $this->futil->getFilesList(ROOT.'app'.DS.'structures', false);

		foreach($structures as $structure)
		{
			$tables = null ;
				
			if ( strpos($structure['name'],'.doc.php') !== false )
			{
				continue;
			}
				
			$this->view->setStatus ('Opening structure ' . $structure['name']) ;
				
			include(ROOT.'app'.DS.'structures'.DS.$structure['name']) ;
				

			$this->rendering[] = '/*' ;
			$this->rendering[] = '*/' ;
			$this->rendering[] = '' ;
			$this->rendering[] = '' ;
				
			if ( !is_null($tables) )
			{
				$this->renderStructure ( array_shift(explode('.', $structure['name'] )) , $tables) ;
			}
		}

		if ( @$this->params['include_users'] === 'true' )
		{
				
			$this->view->setStatus ('Opening core Aenoa Users structure') ;
				
			include( AE_STRUCTURES.'users.php') ;
				
			$this->renderStructure ( 'Core Aenoa structure' , $users ) ;
		}

		if ( @$this->params['include_api'] === 'true' )
		{
			$this->view->setStatus ('Opening core Aenoa API structure') ;
				
			include( AE_STRUCTURES.'api.php') ;

			$this->renderStructure ( 'Core Aenoa structure' , $api ) ;
		}

		$this->renderFileFooter ()  ;

		$f = new File ( ROOT.'app'.DS.'structures'.DS.'structures.doc.php' , true) ;
		$f->write(implode("\n",$this->rendering));
		$f->close () ;

		$this->view->setSuccess ('Structures documentation file generated') ;
	}


	function renderStructure ( $structure , $tables )
	{
		foreach( $tables as $tablename => $table )
		{
			$this->rendering[] = '/*' ;
			$this->rendering[] = "\t" . 'Table: ' . $tablename ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" . sprintf(_('This table is part of structure *%s*'), $structure ) ;
			$this->rendering[] = '*/' ;
				
			$this->renderFields ( $table ) ;
		}
		$this->rendering[] = '' ;
		$this->rendering[] = '' ;
	}

	function renderFileHeader ()
	{
		$this->rendering[] = '<?php' ;
		$this->rendering[] = '' ;
		$this->rendering[] = '/*' ;
		$this->rendering[] = "\t" . 'File: ' . ucfirst($this->project->name) . ' DB structures' ;
		$this->rendering[] = "\t" ;

		$this->rendering[] = "\t" ;
		$this->rendering[] = '*/' ;
		$this->rendering[] = '' ;
		$this->rendering[] = '' ;
	}

	function renderFileFooter ()
	{

		$this->rendering[] = '' ;
		$this->rendering[] = '' ;
		$this->rendering[] = '?>' ;
	}


	function renderFields ( $fields )
	{
		$this->group = null ;
			
		foreach ( $fields as $field )
		{
			$this->renderField ( $field ) ;
		}

	}

	function renderField ( $field )
	{
		$this->rendering[] = '' ;
		$this->rendering[] = '/*' ;
		$this->rendering[] = "\t" . 'Field: ' . $field['name'] ;
		$this->rendering[] = "\t" ;

		if ( ake ( 'label', $field ) )
		{

			if ( ake ('validation' , $field ) && is_array($field['validation']))
			{
				$this->rendering[] = "\t" . $field['label'] . ' (' . _('Has validation') . ')';
			} else {
				$this->rendering[] = "\t" . $field['label'] ;
			}
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		}

		if ( ake ('description' , $field ) )
		{
			$this->rendering[] = "\t" . $field['description'] ;
				
			if ( ake ('main' , $field ) && $field['main'] === true )
			{
				$this->rendering[] = "\t" . _('This field is the main field of the table.' ) ;
			}
				
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		}

		if ( ake ('main' , $field ) && $field['main'] === true )
		{
			$this->rendering[] = "\t" . _('This field is the main field of the table.' ) ;
			$this->rendering[] = "\t" ;
		}



		$this->rendering[] = "\t" . _('Type') . ':' ;
		$this->rendering[] = "\t" ;
		$this->rendering[] = "\t" . '> ' .$field['type'] ;
		$this->rendering[] = "\t" ;

		switch ( $field['type'] )
		{
			case DBSchema::TYPE_CHILD:
				$this->rendering[] = "\t" . _('Field type specifications') .':' ;
				$this->rendering[] = "\t" .sprintf(_('This field links each table row to *%s* parent table.'), $field['source']);
				$this->rendering[] = "\t" ;
				$this->rendering[] = "\t" ;
				break;
			case DBSchema::TYPE_PARENT:
				$this->rendering[] = "\t" . _('Field type specifications') .':' ;
				$this->rendering[] = "\t" .sprintf(_('This field links the current table to the *%s* child table.'), $field['source']);
				$this->rendering[] = "\t" ;
				$this->rendering[] = "\t" ;
				break;
			case DBSchema::TYPE_ENUM:
				$this->rendering[] = "\t" . _('Field available values') .':' ;
				$l = count($field['values']);
				$default = 'No default value' ;
				for ( $i = 0 ; $i < $l ; $i ++ )
				{
					if ($field['values'][$i] == $field['default'])
					{
						$default = $field['labels'][$i];
					}
					$this->rendering[] = "\t\t > " . $field['values'][$i] . ' => ' . $field['labels'][$i] ;
				}
				$this->rendering[] = "\t" ;
				$this->rendering[] = "\t" . _('Default value is:') ;
				$this->rendering[] = "\t\t > " . $field['default'] . ' => ' . $default ;
				$this->rendering[] = "\t" ;
				break;

			case DBSchema::TYPE_FILE:
				$this->rendering[] = "\t" . _('File specifications') .':' ;
				$this->rendering[] = "\t - " . _('This field links to a file in the filesystem.');
				if ( ake('requirements',$field) )
				{
					$req = $field['requirements'] ;
					if ( $req['mimetypes'] == 'webimage' )
					{
						$mimes = File::$webImageMimes ;
					} else {
						$mimes = $req['mimetypes'] ;
					}
						
					if ( is_array($mimes) )
					{
						$mimes = implode(', ' , $mimes);
					}
						
					$this->rendering[] = "\t - " . sprintf(_('The file must have one of the following mimetypes: *%s*'), $mimes ) ;
						
					if ( ake ('filesize', $req ) )
					{
						$this->rendering[] = "\t - " . sprintf(_('The file must have a max weight of: *%s* ko'), $req['filesize'] / 1024 ) ;
					}
						
					if ( ake ('minSize', $req ) )
					{
						$this->rendering[] = "\t - " . sprintf(_('In case of image, the minimum size of the image must be *%s* pixels'), implode('x',$req['minSize']) ) ;
					}
						
					if ( ake ('maxSize', $req ) )
					{
						$this->rendering[] = "\t - " . sprintf(_('In case of image, the maximum size of the image must be *%s* pixels'), implode('x',$req['maxSize']) ) ;
					}
				}

				if ( ake('auto_rename', $field ) && $field['auto_rename'] === true )
				{
						
					$this->rendering[] = "\t - " . _('The uploaded file will be automatically renamed using a hash.');
				}

				$this->rendering[] = "\t" ;
				$this->rendering[] = "\t" ;

				if ( ake('convert_webimage', $field ) && is_array($field['convert_webimage']) && count ($field['convert_webimage']) > 0 )
				{
					$this->rendering[] = "\t". 'File conversions:';
						
					foreach ( $field['convert_webimage'] as $conversion )
					{
						$this->rendering[] = "\t - " . sprintf(_('The uploaded file will be automatically converted to *%s* format, cropped to *%s* pixels size, suffixed by *%s* token, and this conversion will be saved in *%s* folder.'),
						@$conversion['type'],
						@implode('x',@$conversion['size']),
						@$conversion['suffix'],
						@$conversion['path']
						);
					}
						
					$this->rendering[] = "\t" ;
					$this->rendering[] = "\t" ;
				}

				break;
		}

		if ( ake ( 'behavior', $field ) )
		{
			switch ( true )
			{
				case $field['behavior'] & DBSchema::BHR_UNIQUE:
					$this->rendering[] = "\t" . _('Field behavior') .':' ;
					$this->rendering[] = "\t" . _('The value of this field must be unique in the table (Behavior BHR_UNIQUE).');
					$this->rendering[] = "\t" ;
					$this->rendering[] = "\t" ;
					break;
				case $field['behavior'] & DBSchema::BHR_PICK_IN:
					$this->rendering[] = "\t" . _('Field behavior') .':' ;
					$this->rendering[] = "\t" . sprintf(_('The value of this field is a selection of rows identifiers of table *%s* (Behavior BHR_PICK_IN).'), $field['source']) ;
					$this->rendering[] = "\t" ;
					$this->rendering[] = "\t" ;
					break;
				case $field['behavior'] & DBSchema::BHR_PICK_ONE:
					$this->rendering[] = "\t" . _('Field behavior') .':' ;
					$this->rendering[] = "\t" . sprintf(_('The value of this field is a row identifier of table *%s* (Behavior BHR_PICK_ONE).'), $field['source']) ;
					$this->rendering[] = "\t" ;
					$this->rendering[] = "\t" ;
					break;
				case $field['behavior'] & DBSchema::BHR_URLIZE:
					$this->rendering[] = "\t" . _('Field behavior') .':' ;
					$this->rendering[] = "\t" . _('The value of this field will be urlized before DB insertion (Behavior BHR_URLIZE).') ;
					$this->rendering[] = "\t" ;
					$this->rendering[] = "\t" ;
					break;
				case $field['behavior'] & DBSchema::BHR_SERIALIZED:
					$this->rendering[] = "\t" . _('Field behavior') .':' ;
					$this->rendering[] = "\t" . _('The value of this field will be serialized before DB insertion and unserialized after DB selection. Serialization algorithm is the PHP core one. (Behavior BHR_SERIALIZED).') ;
					$this->rendering[] = "\t" ;
					$this->rendering[] = "\t" ;
					break;
			}
		}
		if ( $field['name'] == 'created' )
		{
			$this->rendering[] = "\t" . _('Automatic field behavior') .':' ;
			$this->rendering[] = "\t" . sprintf(_('As this field is named *%s*, its value will be automatically set at each insertion to the current datetime value.'), $field['name']) ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		} else
		if ( $field['name'] == 'updated' || $field['name'] == 'modified' )
		{
			$this->rendering[] = "\t" . _('Automatic field behavior') .':' ;
			$this->rendering[] = "\t" . sprintf(_('As this field is named *%s*, its value will be automatically set at each edition to the current datetime value.'), $field['name']) ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		}

		if ( ake ('length' , $field ))
		{
			$this->rendering[] = "\t" . _('Max field length') .':' ;
			$this->rendering[] = "\t" .sprintf(_('The max length of this field is *%s* chars.'), $field['length']);

			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		}


		if ( ake ('validation' , $field ) && is_array($field['validation']))
		{
			$this->rendering[] = "\t" . _('Validation') .':' ;
			$this->rendering[] = "\t" . _('The following rule is applied to value:') ;
			$this->rendering[] = "\t > " . $field['validation']['rule'];
			$this->rendering[] = "\t" . _('The following message is shown in case of validation error:') ;
			$this->rendering[] = "\t > " . $field['validation']['message'];
			 
			$this->rendering[] = "\t" ;
		}


		if ( ake('js_plugin_edit', $field) )
		{
			$plug = explode ('/',$field['js_plugin_edit']) ;
			$this->rendering[] = "\t" . _('Edition JS plugin') .':' ;
			$this->rendering[] = "\t" . _('At edition, a Javascript plugin will be used.' ) ;
			$plugfilename = array_shift($plug) ;
			$this->rendering[] = "\t - " . sprintf(_('Plugin filename: *%s*' ), $plugfilename ) ;
			$this->rendering[] = "\t - " . sprintf(_('Plugin name: *%s*' ), array_shift($plug) ) ;
			$this->rendering[] = "\t - " . sprintf(_('Plugin parameters: *%s*' ), str_replace(':', ' => ' ,implode(', ' ,$plug) ) ) ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" . sprintf(_('Refer to AJSF documentation to know more about this plugin: http://www.aenoa-systems.com/docs/ajsf/1.0/files/plugins/%s-js.html' ), $plugfilename) ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		}

		if ( (ake ('uneditable' , $field ) && $field['uneditable'] === true) || $field['name'] == 'created' || $field['name'] == 'updated' || $field['name'] == 'modified' )
		{
			$this->rendering[] = "\t" . _('Edition') .':' ;
			$this->rendering[] = "\t" . _('This field will not be editable by any user of the system. Value of the field may be automatically generated by the system or application.' ) ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		}


		if ( ake ('hide-from-table' , $field ) && $field['hide-from-table'] === true )
		{
			$this->rendering[] = "\t" . _('Display') .':' ;
			$this->rendering[] = "\t" . _('This field will not be displayed in generated tables (HTML template uses only).' ) ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		}
		
		if ( ake ('group' , $field ) )
		{
			$this->group = $field['group'] ;
		}
		
		
		if ( !is_null($this->group) )
		{
			$this->rendering[] = "\t" . _('Field group') .':' ;
			$this->rendering[] = "\t" . sprintf(_('This field will be displayed *%s*' ), $this->group ) ;
			$this->rendering[] = "\t" ;
			$this->rendering[] = "\t" ;
		}



		$this->rendering[] = '*/' ;
		$this->rendering[] = '' ;
	}
}

?>