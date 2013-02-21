<?php

class Default_Model_DbTable_Team extends Zend_Db_Table_Abstract
{

	protected $_name = 'teams';
	protected $_dependentTables = array('Default_Model_DbTable_Account');


}

