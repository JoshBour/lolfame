<?php

class Default_Model_DbTable_AccountPositions extends Zend_Db_Table_Abstract
{

    protected $_name = 'account_positions';
    
    protected $_referenceMap = array(
    		'Account' => array(
    				'columns' => array('account_id'),
    				'refTableClass' => 'Default_Model_DbTable_Account',
    				'refColumns' => array('id'),
            		 'onDelete'          => self::CASCADE),
    		'Position' => array(
    				'columns' => array('position_id'),
    				'refTableClass' => 'Default_Model_DbTable_Position',
    				'refColumns' => array('id'),
            		 'onDelete'          => self::CASCADE)
    		);

}

