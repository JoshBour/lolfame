<?php

class Default_Model_DbTable_Posts extends Zend_Db_Table_Abstract
{

	protected $_name = 'posts';

	protected $_dependentTables = array("Default_Model_DbTable_Comments","Default_Model_DbTable_Ratings");
	
	protected $_referenceMap = array(
			'Account' => array(
					'columns' => array('author','co_author'),
					'refTableClass' => 'Default_Model_DbTable_Account',
					'refColumns' => array('id'),
					'onDelete'          => self::CASCADE),
	);	
	
	public static function findById($id){
		if(!empty($id)){
			$post = new self();
			$postArray = $post->find($id)->current();
			if(!empty($postArray)){
				return Default_Model_Object::cast(new Default_Model_Post(), $postArray->toArray());
			}else{
				return null;
			}
		}
	}
	
}