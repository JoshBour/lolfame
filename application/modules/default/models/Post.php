<?php

class Default_Model_Post extends Default_Model_Object
{
	private $_id = null;
	private $_author = null;
	private $_coAuthor = null;
	private $_subject = null;
	private $_created = null;
	private $_edited = null;

	public static $errors = array();

	public function __construct(){
	}

	public static function create($status,$author,$coAuthor=null){
		$post = new self();
		if(!empty($status) && !empty($author)){
			$post->_author = intval($author);
			$post->_subject = $status;
			$post->_coAuthor = $coAuthor;
			
			try{
				$post->save();
			}catch(RuntimeException $e){
				throw new RuntimeException('There was a problem with the post, please try again.', null, $e);
			}
			return $post;
		}else{
			throw new InvalidArgumentException("There was a problem with the input data, please try again.");
		}
	}

	public function save(){
		return isset($this->_id)? $this->_update() : $this->_insert();
	}

	protected function _insert(){
		$postTable = new Default_Model_DbTable_Posts();
		$postId = $postTable->insert(array(
					'author' => $this->_author,
					'co_author' => $this->_coAuthor,
					'subject' => $this->_subject,
					'created' => time(),
					'edited' => time()
					)
				);
		if(!empty($postId)){
			return self::cast($this,$postTable->find($postId)->current()->toArray());
		}else{
			throw new RuntimeException('There was a problem with the Post creation, please try again.');
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
		$postTable = new Default_Model_DbTable_Posts();
		$post = $postTable->find($this->_id)->current();
		if($post->delete()){
			return true;
		}else{
			throw new RuntimeException("The was an error with the post deletion");
		}
	}

	public function getComments(){
		$postTable = new Default_Model_DbTable_Posts();
		$post = $postTable->find($this->_id)->current();
		$comments = $post->findDependentRowset("Default_Model_DbTable_Comments");
		if(!empty($comments)){
			$commentArray = array();
			foreach($comments->toArray() as $comment){
				$commentArray[] = self::cast(new Default_Model_Comment(), $comment);
			}
			return $commentArray;
		}else{
			return null;
		}
	}	

	public function getRatingArray(){
		$ratings = $this->getRatings();
		$likes = array(); 
		$dislikes = array();
		if(!empty($ratings)){
			foreach($ratings as $rating){
				if($rating->getAction()==1){
					$likes[] = $rating;
				}else{
					$dislikes[] = $rating;
				}
			}
			return array('likes'=>$likes,'dislikes'=>$dislikes);
		}else{
			return $ratings;
		}
	}
	
	private function getRatings(){
		$postTable = new Default_Model_DbTable_Posts();
		$post = $postTable->find($this->_id)->current();
		$ratings = $post->findDependentRowset("Default_Model_DbTable_Ratings");
		if(!empty($ratings)){
			$ratingArray = array();
			foreach($ratings->toArray() as $rating){
				$ratingArray[] = self::cast(new Default_Model_Rating(), $rating);
			}
			return $ratingArray;
		}else{
			return null;
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
	

	public function setId($id){
		$this->_id = $id;
	}

	public function setAuthor($name){
		$this->_author = $name;
	}
	
	public function setCoAuthor($coauthor){
		$this->_coAuthor = $coauthor;
	}

	public function setSubject($subject){
		$this->_subject = $subject;
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

	public function getAuthor(){
		$author = Default_Model_DbTable_Account::findById($this->_author);
		if(!empty($author)){
			return $author;
		}else{
			throw new RuntimeException('The author was not found!');
		}
	}
	
	public function getCoAuthor(){
		if(!is_null($this->_coAuthor)){
			$author = Default_Model_DbTable_Account::findById($this->_coAuthor);
			if(!empty($author)){
				return $author;
			}else{
				throw new RuntimeException('The co-author was not found!');
			}
		}
		return false;
	}

	public function getSubject(){
		return $this->_subject;
	}

	public function getCreated(){
		return $this->_created;
	}

	public function getEdited(){
		return $this->_edited;
	}
}

