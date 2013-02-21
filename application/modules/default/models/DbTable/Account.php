<?php

class Default_Model_DbTable_Account extends Zend_Db_Table_Abstract
{

	protected $_name = 'accounts';
	protected $_dependentTables = array('Default_Model_DbTable_AccountSummoners', 'Default_Model_DbTable_AccountSummoners','Default_Model_DbTable_Posts');

	protected $_referenceMap = array(
			'Team' => array(
					'columns' => array('team_id'),
					'refTableClass' => 'Default_Model_DbTable_Team',
					'refColumns' => array('id'),
					'onDelete'          => self::CASCADE),
	);	
	
	public function findByUsernameOrEmail($username,$email){
		$query = $this->select()
		->where('username = ?',$username)
		->orWhere('email = ?', $email);
		return $this->fetchRow($query);
	}
	/**
	 *
	 * @param int $id
	 * @return Default_Model_User;
	 */
	public static function findById($id){
		if(!empty($id)){
			$acc = new self();
			$userArray = $acc->find($id)->current();
			if(!empty($userArray)){
				return Default_Model_Object::cast(new Default_Model_User(), $userArray->toArray());
			}else{
				return null;
			}
		}
	}

	public static function findByUsername($username){
		$acc = new self();
		$query = $acc->select()
		->where('username = ?',$username);
		$userArray = $acc->fetchRow($query);
		if(!empty($userArray)){
			return Default_Model_Object::cast(new Default_Model_User(), $userArray->toArray());
		}else{
			return false;
		}
	}
}

