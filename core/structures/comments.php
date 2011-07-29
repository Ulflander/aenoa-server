<?php




$users = array (

			'ae_comments' => array (
				array (
					'name' => 'id',
					'type' => AbstractDB::TYPE_INT,
					'behavior' => AbstractDB::BHR_INCREMENT,
					'label' => _('Id'),
					'hide-from-table' => true
				),
				array (
					'name' => 'structure',
					'type' => AbstractDB::TYPE_STRING,
					'label' => _('Structure'),
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Comments must refer to a database structure')
					)
				),
				array (
					'name' => 'table',
					'type' => AbstractDB::TYPE_STRING,
					'label' => _('Table'),
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Comments must refer to a table')
					)
				),
				array (
					'name' => 'id',
					'label' => _('Identifier'),
					'type' => AbstractDB::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Comments must refer to an element')
					)
				),
				array (
					'name' => 'comment',
					'label' => _('Comment'),
					'type' => AbstractDB::TYPE_TEXT,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Please provide a comment text')
					)
				),
				array (
					'name' => 'ae_user',
					'label' => _('Associated user'),
					'type' => AbstractDB::TYPE_INT,
					'behavior' => AbstractDB::BHR_PICK_IN,
					'source' => 'ae_users',
					'source-main-field' => 'email',
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Please provide a comment text')
					)
				),
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
			),
			
		);



?>