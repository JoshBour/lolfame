<?php

class Default_Model_DbTable_Ratings extends Zend_Db_Table_Abstract
{

	protected $_name = 'post_rating';
	
	protected $_referenceMap = array(
			'Posts' => array(
					'columns' => array('post_id'),
					'refTableClass' => 'Default_Model_DbTable_Posts',
					'refColumns' => array('id'),
					'onDelete'          => self::CASCADE
					),
			'Account' => array(
					'columns' => array('account_id'),
					'refTableClass' => 'Default_Model_DbTable_Account',
					'refColumns' => array('id'),
					'onDelete' => self::CASCADE
					)
	);	
	

}