<?php

class Default_Model_User extends Default_Model_Object
{
	private $_id = null;
	private $_username = null;
	private $_password = null;
	private $_email = null;
	private $_ip = null;
	private $_registered = null;
	private $_last_seen = null;
	private $_group = null;
	private $_fame = null;
	private $_totalFame = null;
	private $_experience = null;
	private $_teamId = null;
	private $_avatar = null;
	private $_retrieve = null;

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
				'total_fame' => 0,
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
				'total_fame' => $this->_totalFame,
				'experience' => $this->_experience);
		if($account->update($data,$where)){
			return true;
		}else{
			throw new RuntimeException('There was a problem with the Account creation, please try again.');
		}

	}

	/**
	 * Returns an array with objects of type UserSummoner
	 *
	 * @return array Default_Model_Summoner
	 */
	public function getSummoners(){
		$accTable = new Default_Model_DbTable_Account();
		$user = $accTable->find($this->_id)->current();
		$userAccounts = $user->findManyToManyRowset('Default_Model_DbTable_Summoner', 'Default_Model_DbTable_AccountSummoners');
		if(!empty($userAccounts[0])){
			$sumArray = array();
			foreach($userAccounts->toArray() as $summoner){
				$userSum = new Default_Model_Summoner();
				$sumArray[] = self::cast($userSum, $summoner);
			}
			return $sumArray;
		}else{
			return null;
		}
	}

	public function getPositions(){
		$accTable = new Default_Model_DbTable_Account();
		$user = $accTable->find($this->_id)->current();
		$userPositions = $user->findManyToManyRowset('Default_Model_DbTable_Position', 'Default_Model_DbTable_AccountPositions');
		if(!empty($userPositions[0])){
			$posArray = array();
			foreach($userPositions as $position){
				$posArray[] = $position->name;
			}
			return $posArray;
		}else{
			return null;
		}
	}
	
	
	/**
	 * 
	 * @param int $postId
	 * 
	 * @return Default_Model_Rating
	 */
	public function hasRated($postId){
		$accTable = new Default_Model_DbTable_Account();
		$user = $accTable->find($this->_id)->current();
		$userRated = $user->findDependentRowset('Default_Model_DbTable_Ratings');
		if(!empty($userRated[0])){
			foreach($userRated as $rated){
				if($postId == $rated->post_id)
					return self::cast(new Default_Model_Rating(),$rated->toArray());
			}
		}
		return false;
	}
	
	public function getTeam(){
		$teamTable = new Default_Model_DbTable_Team();
		$team = $teamTable->find($this->_teamId)->current();
		if(!empty($team[0])){
			return self::cast(new Default_Model_Team(),$team->toArray());
		}else{
			return null;
		}
	}
	
	public function getPosts(){
		$accTable = new Default_Model_DbTable_Account();
		$postTable = new Default_Model_DbTable_Posts();
		$posts = $postTable->fetchAll(array('author = ? OR co_author = ?' => $this->_id));
		if(!empty($posts)){
			$postArray = array();
			foreach($posts->toArray() as $post){
				$postArray[] = self::cast(new Default_Model_Post(), $post);
			}
			return array_reverse($postArray);
		}else{
			return null;
		}
	}

	public function hasSummoner($name,$region){
		$userSumms = $this->getSummoners();
		foreach($userSumms as $sum){
			if(strtolower($sum->getName()) == strtolower($name) && $sum->getRegion() == $region){
				return true;
			}
		}
		return false;
	}

	public function setId($id){
		$this->_id = $id;
	}

	// setters-getters
	public function setUsername($username)
	{
		$this->_username = $username;
	}

	public function setPassword($password){
		$this->_password = $password;
	}

	public function setEmail($email){
		$this->_email = $email;
	}

	public function setRegistered($registerDate){
		$this->_registered = $registerDate;
	}

	public function setLastSeen($lastSeen){
		$this->_last_seen = $lastSeen;
	}

	public function setIp($ip){
		$this->_ip = $ip;
	}

	public function setGroup($group){
		$this->_group = $group;
	}

	public function setFame($fame){
		$this->_fame = $fame;
		$this->_totalFame += $fame;
	}
	
	public function setTotalFame($fame){
		$this->_totalFame = $fame;
	}

	public function setExperience($xp){
		if($this->_experience + $xp >= 100){
			$this->_fame++;
			$this->_experience = ($this->_experience + $xp) - 100;
		}else{
			$this->_experience += $xp;
		}
	}

	public function setTeamId($id){
		$this->_teamId = $id;
	}

	public function setAvatar($avatar){
		$this->_avatar = $avatar;
	}

	public function setRetrieve($code){
		$this->_retrieve = $code;
	}

	public function getId(){
		return $this->_id;
	}

	public function getUsername()
	{
		return $this->_username;
	}

	public function getPassword(){
		return $this->_password;
	}

	public function getEmail(){
		return $this->_email;
	}

	public function getRegistered(){
		return $this->_registered;
	}

	public function getIp(){
		return $this->_ip;
	}

	public function getLastSeen(){
		return $this->_last_seen;
	}

	public function getAvatar(){
		if(empty($this->_avatar)){
			return 'default.png';
		}else{
			return $this->_id .'/'. $this->_avatar;
		}
	}

	public function getGroup(){
		return $this->_group;
	}

	public function getFame(){
		return $this->_fame;
	}
	
	public function getTotalFame(){
		return $this->_totalFame;
	}

	public function getExperience(){
		return $this->_experience;
	}

	public function getTeamId(){
		return $this->_teamId;
	}

	public function getRetrieve(){
		return $this->_retrieve;
	}

}

