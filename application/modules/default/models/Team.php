<?php

class Default_Model_Team extends Default_Model_Object
{
	private $_id = null;
	private $_name = null;
	private $_points = null;

	public static $errors = array();

	public function __construct(){
	}

	public static function create($username,$password,$repassword,$email,$ip){
		$account = new Default_Model_DbTable_Account();

		// do the passwords match
		if($password != $repassword){
			throw new InvalidArgumentException('The passwords are not the same');
		}

		// does the username/email exist
		$result = $account->findByUsernameOrEmail($username, $password);
		if(!empty($result)){
			throw new InvalidArgumentException('The username or the email already exists.');
		}
		$user = new self();
		$user->setUsername($username);
		$user->setPassword(sha1(Default_Model_Object::salt($password)));
		$user->setEmail($email);
		$user->setIp($ip);
		$user->setRetrieve(md5(Default_Model_Object::salt($username)));

		$user->save();
		return $user;
	}

	public function save(){
		return isset($this->_id)? $this->_update() : $this->_insert();
	}

	protected function _insert(){
		$account = new Default_Model_DbTable_Account();
		$accId = $account->insert(array(
				'username' => $this->_username,
				'password' => $this->_password,
				'email' => $this->_email,
				'ip' => $this->_ip,
				'registered' => date("Y-m-d H:i:s", time()),
				'group' => 'users',
				'fame' => 0,
				'experience' => 10,
				'team_id' => 0,
				'avatar' => '',
				'retrieve' => $this->_retrieve));
		if(!empty($accId)){
			// make the unique folder for the user's images
			if(!file_exists(APPLICATION_PATH.'/../public/images/users/' . $accId)){
				mkdir(APPLICATION_PATH.'/../public/images/users/' . $accId);
			}
			return self::cast($this,$account->find($accId)->current()->toArray());
		}else{
			throw new RuntimeException('There was a problem with the Account creation, please try again.');
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

	public function setId($id){
		$this->_id = $id;
	}
	
	public function setName($name){
		$this->_name = $name;
	}
	
	public function setPoints($points){
		$this->_points = $points;
	}
	
	public function getId(){
		return $this->_id;
	}
	
	public function getName(){
		return $this->_name;
	}
	
	public function getPoints(){
		return $this->_points;
	}

}

