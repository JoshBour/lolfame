<?php
abstract class Default_Model_Object{
	
	/**
	 * @desc
	 * This function accepts an Zend_Db_Table_Row that has the toArray() method applied, and
	 * converts it into an object of type $object
	 * @param object $object
	 * @param array $source
	 */
	public static function cast($object, array $source)
	{
		foreach($source as $key => $value){
			$keyArray = explode('_', $key);
			if(is_array($keyArray) && !empty($keyArray)){
				$newKey = '';
				foreach($keyArray as $sKey){
					$newKey .= ucfirst($sKey);
				}
				$key = $newKey;
			}else{
				$key = ucfirst($key);
			}
			if(method_exists($object, 'set'.$key)){
				$object->{'set'.$key}($value);
			}else{
				return false;
			}
			
		}
		return $object;
	}
	
	
	public static function salt($var){
		$salt = Zend_Registry::get('config')->random->salt;
		return $var.$salt;
	}
	
	public static function checkEmpty(array $array){
		$empty = array();
		foreach($array as $desc => $element){
			if(empty($element)){
				$empty[] = $desc;
			}
		}
		return $empty;
	}
	
	protected function _populate(array $vars){
		foreach($vars as $var => $value){
			if(in_array($var,get_object_vars($this))){
				$this->{'set'.$var}($value);
			}else{
				throw new InvalidArgumentException('Something went wrong with the variable assignment.');
			}
		}
	}
	
	protected function _valuesMatch($var1,$var2){
		if($var1 == $var2){
			return true;
		}else{
			return false;
		}
	}
	
}