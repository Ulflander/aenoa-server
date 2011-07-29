<?php




$users = array (

			'ae_users' => array (
				array (
					'name' => 'id',
					'type' => AbstractDB::TYPE_INT,
					'behavior' => AbstractDB::BHR_INCREMENT,
					'label' => _('Id'),
					'hide-from-table' => true
				),
				array (
					'name' => 'email',
					'type' => AbstractDB::TYPE_STRING,
					'label' => _('Email address'),
					'validation' => array (
						'rule' => DBValidator::EMAIL,
						'message' => _('Your email must be a valid email address')
					)
				),
				array (
					'name' => 'firstname',
					'label' => _('Firstname'),
					'type' => AbstractDB::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Please provide your firstname')
					)
				),
				array (
					'name' => 'lastname',
					'label' => _('Lastname'),
					'type' => AbstractDB::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Please provide your lastname')
					)
				),
				array (
					'name' => 'password',
					'label' => _('Password'),
					'type' => AbstractDB::TYPE_STRING,
					'behavior' => AbstractDB::BHR_SHA1 ,
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
					'type' => AbstractDB::TYPE_INT,
					'behavior' => AbstractDB::BHR_PICK_ONE,
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
					'type' => AbstractDB::TYPE_PARENT,
					'source' => 'ae_users_info',
					'source-main-field' => 'id',
					'source-link-field' => 'user_id'
				) ,
				array ( 
					'name' => 'created',
					'label' => _('Created'),
					'type' =>  AbstractDB::TYPE_DATETIME,
					'hide-from-table' => true
				),
				array ( 
					'name' => 'updated',
					'label' => _('Updated'),
					'type' =>  AbstractDB::TYPE_DATETIME,
					'hide-from-table' => true
				),
				array ( 
					'name' => 'last_connection',
					'label' => _('Last connection'),
					'type' =>  AbstractDB::TYPE_DATETIME
				),
				array ( 
					'name' => 'app_properties',
					'label' => _('App properties'),
					'type' =>  AbstractDB::TYPE_TEXT,
					'uneditable' => true,
					'hide-from-table' => true
				),
			),
			
			'ae_groups' => array (
				array (
					'name' => 'id',
					'label' => _('Id'),
					'type' => AbstractDB::TYPE_INT,
					'behavior' => AbstractDB::BHR_INCREMENT,
					'hide-from-table' => true
				),
				array (
					'name' => 'label',
					'label' => _('Label'),
					'type' => AbstractDB::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Your email must be a valid email address')
					)
				),
				array (
					'name' => 'level',
					'label' => _('Level'),
					'type' => AbstractDB::TYPE_INT,
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
					'type' => AbstractDB::TYPE_INT,
					'behavior' => AbstractDB::BHR_INCREMENT,
					'label' => _('Id'),
					'hide-from-table' => true
				),
				array ( 
					'name' => 'user_info',
					'label' => _('User informations'),
					'type' => AbstractDB::TYPE_CHILD,
					'source' => 'ae_users',
					'source-main-field' => 'email',
					'source-link-field' => 'user_info',
					'hide-from-table' => true
				) ,
			), 
			
			'ae_confirmations' => array (
				array (
					'name' => 'id',
					'type' => AbstractDB::TYPE_INT,
					'behavior' => AbstractDB::BHR_INCREMENT,
					'hide-from-table' => true
				),
				array (
					'name' => 'user',
					'label' => _('User'),
					'type' => AbstractDB::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::EMAIL,
						'message' => _('Your email must be a valid email address')
					)
				),
				array (
					'name' => 'hash',
					'label' => _('Hash'),
					'type' => AbstractDB::TYPE_STRING,
					'hide-from-table' => true
				),
				array (
					'name' => 'action',
					'label' => _('Action'),
					'type' => AbstractDB::TYPE_STRING
				),
				array (
					'name' => 'expiry',
					'label' => _('Expiration date'),
					'type' =>  AbstractDB::TYPE_TIMESTAMP,
					'hide-from-table' => true
				),
			),
			
		);



?>