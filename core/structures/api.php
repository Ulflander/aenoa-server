<?php




$api = array (

			'ae_api_keys' => array (
				array (
					'name' => 'id',
					'type' => AbstractDB::TYPE_INT,
					'behavior' => AbstractDB::BHR_INCREMENT,
					'hide-from-table' => true
				),
				array (
					'name' => 'label',
					'label' => _('Key label'),
					'type' => AbstractDB::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Give a name to the new API security key')
					)
				),
				array (
					'name' => 'public',
					'label' => _('Public key'),
					'type' => AbstractDB::TYPE_STRING,
					'length' => 16,
					'validation' => array (
						'rule' => '[A-Za-z0-9]{8,16}',
						'message' => _('First key must contain only A-Z, a-z and 0-8 characters')
					),
					'description' => _('Create the first key — this is the public key'),
					'js_plugin_edit' => 'ae-str-gen/AeStringGenerator/length:15'
				),
				array (
					'name' => 'private',
					'label' => _('Private key'),
					'type' => AbstractDB::TYPE_STRING,
					'hide-from-table' => true,
					'length' => 16,
					'validation' => array (
						'rule' => '[A-Za-z0-9]{8,16}',
						'message' => _('Your email must be a valid email address')
					),
					'description' => _('Create the second key — this is the public key'),
					'js_plugin_edit' => 'ae-str-gen/AeStringGenerator/length:15'
				)
			),
		);



?>