<?php




$users = array (

			'ae_comments' => array (
				array (
					'name' => 'id',
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_INCREMENT,
					'label' => _('Id'),
					'hide-from-table' => true
				),
				array (
					'name' => 'structure',
					'type' => DBSchema::TYPE_STRING,
					'label' => _('Structure'),
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Comments must refer to a database structure')
					)
				),
				array (
					'name' => 'table',
					'type' => DBSchema::TYPE_STRING,
					'label' => _('Table'),
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Comments must refer to a table')
					)
				),
				array (
					'name' => 'id',
					'label' => _('Identifier'),
					'type' => DBSchema::TYPE_STRING,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Comments must refer to an element')
					)
				),
				array (
					'name' => 'comment',
					'label' => _('Comment'),
					'type' => DBSchema::TYPE_TEXT,
					'validation' => array (
						'rule' => DBValidator::NOT_EMPTY,
						'message' => _('Please provide a comment text')
					)
				),
				array (
					'name' => 'ae_user',
					'label' => _('Associated user'),
					'type' => DBSchema::TYPE_INT,
					'behavior' => DBSchema::BHR_PICK_IN,
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
					'type' =>  DBSchema::TYPE_DATETIME,
					'hide-from-table' => true
				),
				array ( 
					'name' => 'updated',
					'label' => _('Updated'),
					'type' =>  DBSchema::TYPE_DATETIME,
					'hide-from-table' => true
				),
			),
			
		);



?>