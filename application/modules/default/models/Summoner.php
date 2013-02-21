<?php
class Default_Model_Summoner extends Default_Model_Object{
	private $_id = null;
	private $_name = null;
	private $_region = null;
	private $_validationImage = null;
	private $_status = null;

	public static $errors = array();

	/**
	 * @desc Helper function that creates a new Summoner and stores it to the database.
	 * @param Default_Model_User $user
	 * The account owner
	 * @param String $name
	 * The Summoner's name
	 * @param String $region
	 * The Summoner's region
	 * @param String $validateImage
	 * The validation image
	 * @param Int $status
	 * The validation status
	 * @return Default_Model_Summoner
	 */
	public static function create($user, $name,$region,$validationImage,$status = '0'){
		// create the required objects
		$summoner = new self();
		$sumTable = new Default_Model_DbTable_Summoner();
		$accSumTable = new Default_Model_DbTable_AccountSummoners();

		// perform some basic error checking
		$errors = $summoner->process($name, $region,$validationImage);
		if(!empty($errors)){
			throw new InvalidArgumentException(implode('<br />',$errors));
		}
			
		if($user->hasSummoner($name)){
			throw new InvalidArgumentException("You have already added this Summoner.");
		}

		try{
			$summoner->setName($name);
			$summoner->setRegion($region);
			$summoner->setValidationImage($validationImage);
			$summoner->setStatus($status);
			$summoner = $summoner->save();

			$accSumTable->insert(array(
					'account_id' => $user->getId(),
					'summoner_id' => $summoner->getId()));
			return $summoner;
		}catch(Exception $e){
			throw new RuntimeException('Something went wrong with the Summoner insertion, please try again',null,$e);
		}

	}

	/**
	 * @desc Checks if a summoner exists
	 * @param string $summoner
	 * The Summoner's name
	 * @return Default_Model_Summoner
	 */
	public static function findByName($summoner,$region,$original = false){
		$sumTable = new Default_Model_DbTable_Summoner();
		$where = $sumTable->select()->where('name = ?',$summoner)->where('region = ?',$region);
		$result = $sumTable->fetchRow($where);
		if(empty($result)){
			return false;
		}else{
			if($original){
				return $result;
			}else{
				return self::cast(new self(),$result->toArray());
			}
		}

	}

	public static function isValidated($summoner,$region){
		$summoner = self::findByName($summoner,$region);
		if($summoner){
			if($summoner->getStatus() == 2){
				return true;
			}
		}
		return false;
	}

	public function save(){
		return isset($this->_id)? $this->_update() : $this->_insert();
	}

	protected function _insert(){
		$sumTable = new Default_Model_DbTable_Summoner();
		$accSumTable = new Default_Model_DbTable_AccountSummoners();
		$newId = $sumTable->insert(array(
				'name' => $this->_name,
				'region' => $this->_region,
				'validation_image' => $this->_validationImage,
				'status' => $this->_status));
			
		if(!empty($newId)){
			return Default_Model_Object::cast($this,$sumTable->find($newId)->current()->toArray());
		}else{
			throw new Exception("Something went wrong with the User Summoner creation.");
		}
	}

	protected function _update(){
		$sumTable = new Default_Model_DbTable_Summoner();
		$where = $sumTable->getAdapter()->quoteInto('id = ?', $this->_id);
		$data = array(
				'name' => $this->_name,
				'region' => $this->_region,
				'validation_image' => $this->_validationImage,
				'status' => $this->_status);
		if($sumTable->update($data,$where)){
			return true;
		}else{
			return false;
		}

	}

	public function process($name,$region,$validateImg=""){
		$errors = array();
		if(!empty($validateImg)){
			// is everything okay with the file upload?
			if(!$validateImg->receive()){
				$errors[] = "Something went wrong with the image upload.";
			}

			//does the summoner exist in the LoL database?
			// 			if(!Default_Model_LolApi::summonerExists($name, $region)){
			// 				$errors[] = "The Summoner does not exist. Did you type the name correctly?";
			// 			}

			$value = $validateImg->getValue();
			if(empty($value)){
				$this->_validationImage = 'unverified.png';
			}else{
				$this->_validationImage = $value;
				$this->_status = '1';
			}
		}

		// by checking if the name is set, we actually check if this
		// is an edit request
		$summoner = self::findByName($name,$region);
		if(isset($this->_name)){
			// check if the summoner exists and is validated
			if($this->_name == $name && $this->_region == $region){
				$errors[] = "There is nothing to update.";
			}else{
				if($summoner){
					$errors[] = "The summoner already exists.";
				}
				if(self::isValidated($name, $region) || ($summoner && $summoner->getStatus() == 1)){
					$errors[] = "The summoner has been verified by another account.";
				}
			}
		}else{
			if($summoner){
				$errors[] = "The summoner already exists.";
			}
			if(self::isValidated($name, $region) || ($summoner && $summoner->getStatus() == 1)){
				$errors[] = "The summoner has been verified by another account.";
			}
		}
		return $errors;
	}

	public function setId($id){
		$this->_id = $id;
	}

	public function setName($summoner){
		$this->_name = $summoner;
	}

	public function setRegion($region){
		$this->_region = $region;
	}

	public function setValidationImage($image){
		$this->_validationImage = $image;
	}

	public function setStatus($status){
		$this->_status = $status;
	}

	public function getId(){
		return $this->_id;
	}

	public function getName(){
		return $this->_name;
	}

	public function getRegion(){
		return $this->_region;
	}

	public function getValidationImage(){
		return $this->_validationImage;
	}

	public function getStatus(){
		return $this->_status;
	}

}