<?php

$api = array(
    'ae_api_keys' => array(
	array(
	    'name' => 'id',
	    'type' => DBSchema::TYPE_INT,
	    'behavior' => DBSchema::BHR_INCREMENT,
	    'hide-from-table' => true
	),
	array(
	    'name' => 'label',
	    'label' => _('Key label'),
	    'type' => DBSchema::TYPE_STRING,
	    'validation' => array(
		'rule' => DBValidator::NOT_EMPTY,
		'message' => _('Give a name to the new API security key')
	    )
	),
	array(
	    'name' => 'public',
	    'label' => _('Public key'),
	    'type' => DBSchema::TYPE_STRING,
	    'length' => 16,
	    'validation' => array(
		'rule' => '[A-Za-z0-9]{8,20}',
		'message' => _('First key must contain only A-Z, a-z and 0-8 characters')
	    ),
	    'description' => _('Create the first key — this is the public key'),
	    'js_plugin_edit' => 'ae-str-gen/AeStringGenerator/length:20'
	),
	array(
	    'name' => 'private',
	    'label' => _('Private key'),
	    'type' => DBSchema::TYPE_STRING,
	    'hide-from-table' => true,
	    'length' => 16,
	    'validation' => array(
		'rule' => '[A-Za-z0-9]{8,20}',
		'message' => _('Your email must be a valid email address')
	    ),
	    'description' => _('Create the second key — this is the private key'),
	    'js_plugin_edit' => 'ae-str-gen/AeStringGenerator/length:20'
	),
	array (
	    'name' => 'uses',
	    'label' => _('Authorized uses'),
	    'type' => DBSchema::TYPE_ENUM ,
	    'values' => array ( 'all' , 'rest' , 'services' , 'other' ),
	    'labels' => array ( _('All') , _('REST API') , _('Services') , _('Other') ) ,
	    'default' => 'all'
	)
    ),
);
?>