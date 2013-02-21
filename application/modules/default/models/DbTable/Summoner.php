<?php

class Default_Model_DbTable_Summoner extends Zend_Db_Table_Abstract
{

    protected $_name = 'summoners';
    
    protected $_dependentTables = array('Default_Model_DbTable_AccountSummoners');


}

