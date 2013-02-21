<?php

class Default_Model_Comment extends Default_Model_Object
{
	private $_id = null;
	private $_postId = null;
	private $_authorId = null;
	private $_content = null;
	private $_created = null;
	private $_edited = null;

	public static $errors = array();

	public function __construct(){
	}

	public static function create($postId,$authorId,$content){
		$empty = Default_Model_Object::checkEmpty(array("post id" => $postId,"author id" => $authorId,"content"=>$content));
		if(empty($empty)){	
			$comment = new self();
			$comment->_postId = intval($postId);
			$comment->_authorId = intval($authorId);
			$comment->_content = $content;
			try{
				$comment->save();
			}catch(RuntimeException $e){
				throw new RuntimeException('There was a problem with the comment, please try again.', null, $e);
			}
			return $comment;
		}else{
			throw new InvalidArgumentException(implode(',',$empty));
		}
	}
	
	public function save(){
		return isset($this->_id)? $this->_update() : $this->_insert();
	}

	protected function _insert(){
		$comTable = new Default_Model_DbTable_Comments();
		$comId = $comTable->insert(array(
					'post_id' => $this->_postId,
					'author_id' => $this->_authorId,
					'content' => $this->_content,
					'created' => time(),
					'edited' => time()
					)
				);
		if(!empty($comId)){
			return self::cast($this,$comTable->find($comId)->current()->toArray());
		}else{
			throw new RuntimeException('There was a problem with the comment creation, please try again.');
		}
	}

	protected function _update(){
		$account = new Default_Model_DbTable_Account();
		$where = $account->getAdapter()->quoteInto('id = ?', $this->_id);
		$data = array(
				'password' => $this->_password,
				'email' => $this->_email,
				'avatar' => $this->_avatar,
				'fame' => $this->_fame,
				'experience' => $this->_experience);
		if($account->update($data,$where)){
			return true;
		}else{
			throw new RuntimeException('There was a problem with the Account creation, please try again.');
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
	
	public function getPostTime(){
		$etime = time() - $this->_created;
		
		if ($etime < 1) {
			return '0 seconds';
		}
		
		$a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
				30 * 24 * 60 * 60       =>  'month',
				24 * 60 * 60            =>  'day',
				60 * 60                 =>  'hour',
				60                      =>  'minute',
				1                       =>  'second'
		);
		
		foreach ($a as $secs => $str) {
			$d = $etime / $secs;
			if ($d >= 1) {
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's' : '');
			}
		}		

	}

	public function getAuthor(){
		$author = Default_Model_DbTable_Account::findById($this->_authorId);
		if(!empty($author)){
			return $author;
		}else{
			throw new RuntimeException('The author was not found!');
		}
	}

	public function setId($id){
		$this->_id = $id;
	}

	public function setAuthorId($id){
		$this->_authorId = $id;
	}

	public function setPostId($id){
		$this->_postId = $id;
	}
	
	public function setContent($content){
		$this->_content = $content;
	}

	public function setCreated($created){
		$this->_created = $created;
	}

	public function setEdited($edited){
		$this->_edited = $edited;
	}

	public function getId(){
		return $this->_id;
	}
	
	public function getAuthorId(){
		return $this->_authorId;
	}
	
	public function getPostId(){
		return $this->_postId;
	}

	public function getContent(){
		return $this->_content;
	}

	public function getCreated(){
		return $this->_created;
	}

	public function getEdited(){
		return $this->_edited;
	}
}

