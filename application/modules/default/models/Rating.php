<?php

class Default_Model_Rating extends Default_Model_Object
{
	private $_id = null;
	private $_postId = null;
	private $_accountId = null;
	private $_action = null;

	public static $errors = array();

	public function __construct(){
	}

	public static function create($userId,$postId,$action){
		$empty = Default_Model_Object::checkEmpty(array("post id" => $postId,"author id" => $userId,"action"=>$action));
		if(empty($empty)){	
			$rating = new self();
			$rating->_postId = intval($postId);
			$rating->_accountId = intval($userId);
			$rating->_action = $action;
			try{
				$rating->save();
			}catch(RuntimeException $e){
				throw new RuntimeException(null, null, $e);
			}
			return $rating;
		}else{
			throw new InvalidArgumentException(implode(',',$empty));
		}
	}
	
	public function save(){
		return isset($this->_id)? $this->_update() : $this->_insert();
	}

	protected function _insert(){
		$ratingTable = new Default_Model_DbTable_Ratings();
		$rateId = $ratingTable->insert(array(
					'post_id' => $this->_postId,
					'account_id' => $this->_accountId,
					'action' => $this->_action
					)
				);
		if(!empty($rateId)){
			return self::cast($this,$ratingTable->find($rateId)->current()->toArray());
		}else{
			throw new RuntimeException('There was a problem with the rating creation, please try again.');
		}
	}

	protected function _update(){
		$ratingTable = new Default_Model_DbTable_Ratings();
		#$rating = $ratingTable->find($this->_id)->current();
		$where = array('id = ?' => $this->_id);
		$data = array(
				'post_id' => $this->_postId,
				'account_id' => $this->_accountId,
				'action' => $this->_action
			);
		$update = $ratingTable->update($data,$where);
		if($update > 0){
			return true;
		}else{
			throw new RuntimeException('There was a problem with the Rating update, please try again.');
		}

	}
	
	public function delete(){
		$comTable = new Default_Model_DbTable_Comments();
		$com = $comTable->find($this->_id)->current();
		if($com->delete()){
			return true;
		}else{
			throw new RuntimeException("The was an error with the comment deletion");
		}
	}	
	

	public function setId($id){
		$this->_id = $id;
	}

	public function setPostId($id){
		$this->_postId = $id;
	}

	public function setAccountId($id){
		$this->_accountId = $id;
	}
	
	public function setAction($action){
		$this->_action = $action;
	}

	public function getId(){
		return $this->_id;
	}
	
	public function getPostId(){
		return $this->_postId;
	}

	public function getAccountId(){
		return $this->_accountId;
	}

	public function getAction(){
		return $this->_action;
	}
	
}

