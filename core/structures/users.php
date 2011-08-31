<?php




$users = array (

			'ae_users' => array (
				array (
					'name' => 'id',
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_INCREMENT,
					'label' => _('Id'),
					'hide-from-table' => true
				),
				array (
					'name' => 'email',
					'type' => DBSchema::TYPE_STRING,
					'label' => _('Email address'),
					'validation' => array (
						'rule' => DBValidator::EMAIL,
						'message' => _('Your email must be a valid email address')
					)
				),
				array (
					'name' => 'firstname',
					'label' => _('Firstname'),
					'type' => DBSchema::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Please provide your firstname')
					)
				),
				array (
					'name' => 'lastname',
					'label' => _('Lastname'),
					'type' => DBSchema::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Please provide your lastname')
					)
				),
				array (
					'name' => 'password',
					'label' => _('Password'),
					'type' => DBSchema::TYPE_STRING,
					'behavior' => DBSchema::BHR_SHA1 ,
					'validation' => array (
						'rule' => DBValidator::PASSWORD,
						'message' => _('Your password cannot remain empty')
					),
					'uneditable' => true,
					'hide-from-table' => true
				),
				array ( 
					'name' => 'group',
					'label' => _('Group'),
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_PICK_ONE,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('An user has to be associated to a group.')
					),
					'source' => 'ae_groups',
					'source-main-field' => 'label'
				) ,
				array ( 
					'name' => 'user_info',
					'label' => _('User informations'),
					'type' => DBSchema::TYPE_PARENT,
					'source' => 'ae_users_info',
					'source-main-field' => 'id',
					'source-link-field' => 'user_id'
				) ,
				array ( 
					'name' => 'created',
					'label' => _('Created'),
					'type' =>  DBSchema::TYPE_DATETIME,
					'hide-from-table' => true
				),
				array ( 
					'name' => 'updated',
					'label' => _('Updated'),
					'type' =>  DBSchema::TYPE_DATETIME,
					'hide-from-table' => true
				),
				array ( 
					'name' => 'last_connection',
					'label' => _('Last connection'),
					'type' =>  DBSchema::TYPE_DATETIME
				),
				array ( 
					'name' => 'app_properties',
					'label' => _('App properties'),
					'type' =>  DBSchema::TYPE_TEXT,
					'behavior' => DBSchema::BHR_SERIALIZED,
					'uneditable' => true,
					'hide-from-table' => true
				),
			),
			
			'ae_groups' => array (
				array (
					'name' => 'id',
					'label' => _('Id'),
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_INCREMENT,
					'hide-from-table' => true
				),
				array (
					'name' => 'label',
					'label' => _('Label'),
					'type' => DBSchema::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Your email must be a valid email address')
					)
				),
				array (
					'name' => 'level',
					'label' => _('Level'),
					'type' => DBSchema::TYPE_INT,
					'validation' => array (
						'rule' => '^[0-9]{1,2}$',
						'message' => _('Level must be a number between 1 and 99')
					),
					'length' => 2
				),
			),
			
			'ae_users_info' => array (
				array (
					'name' => 'id',
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_INCREMENT,
					'label' => _('Id'),
					'hide-from-table' => true
				),
				array ( 
					'name' => 'user_info',
					'label' => _('User informations'),
					'type' => DBSchema::TYPE_CHILD,
					'source' => 'ae_users',
					'source-main-field' => 'email',
					'source-link-field' => 'user_info',
					'hide-from-table' => true
				) ,
			), 
			
			'ae_confirmations' => array (
				array (
					'name' => 'id',
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_INCREMENT,
					'hide-from-table' => true
				),
				array (
					'name' => 'user',
					'label' => _('User'),
					'type' => DBSchema::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::EMAIL,
						'message' => _('Your email must be a valid email address')
					)
				),
				array (
					'name' => 'hash',
					'label' => _('Hash'),
					'type' => DBSchema::TYPE_STRING,
					'hide-from-table' => true
				),
				array (
					'name' => 'action',
					'label' => _('Action'),
					'type' => DBSchema::TYPE_STRING
				),
				array (
					'name' => 'expiry',
					'label' => _('Expiration date'),
					'type' =>  DBSchema::TYPE_TIMESTAMP,
					'hide-from-table' => true
				),
			),
			
		);



?>