<?php

class Default_Model_DbTable_AccountSummoners extends Zend_Db_Table_Abstract
{

    protected $_name = 'account_summoners';
    
    protected $_referenceMap = array(
    		'Account' => array(
    				'columns' => array('account_id'),
    				'refTableClass' => 'Default_Model_DbTable_Account',
    				'refColumns' => array('id'),
            		 'onDelete'          => self::CASCADE),
    		'Summoner' => array(
    				'columns' => array('summoner_id'),
    				'refTableClass' => 'Default_Model_DbTable_Summoner',
    				'refColumns' => array('id'),
            		 'onDelete'          => self::CASCADE)
    		);

}

