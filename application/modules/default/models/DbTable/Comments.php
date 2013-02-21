<?php

class Default_Model_DbTable_Comments extends Zend_Db_Table_Abstract
{

	protected $_name = 'comments';
	
	protected $_referenceMap = array(
			'Posts' => array(
					'columns' => array('post_id'),
					'refTableClass' => 'Default_Model_DbTable_Posts',
					'refColumns' => array('id'),
					'onDelete'          => self::CASCADE
					),
			'Account' => array(
					'columns' => array('author_id'),
					'refTableClass' => 'Default_Model_DbTable_Account',
					'refColumns' => array('id'),
					'onDelete' => self::CASCADE
					)
	);	
	
	public static function findById($id){
		if(!empty($id)){
			$com = new self();
			$comArray = $com->find($id)->current();
			if(!empty($comArray)){
				return Default_Model_Object::cast(new Default_Model_Comment(), $comArray->toArray());
			}else{
				return null;
			}
		}
	}	

}