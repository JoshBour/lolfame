<?php
abstract class Default_Model_LolApi{
	
	public static function summonerExists($name,$region){
		$key = Zend_Registry::get('config')->lol->elophant->key;
		set_time_limit(0);
		$result = file_get_contents("http://api.elophant.com/v2/" . $region . "/summoner/" . urlencode($name) . "?key=".$key);
		$summoner = json_decode($result);
		if(isset($summoner->success) && $summoner->success){
			return true;
		}		
		return false;
	}
	
}