<?php

class Default_Model_DbTable_Position extends Zend_Db_Table_Abstract
{

	protected $_name = 'positions';
	protected $_dependentTables = array('Default_Model_DbTable_AccountPositions');

}

