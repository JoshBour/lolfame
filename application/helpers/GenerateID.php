<?php 
class Helper_GenerateID extends Zend_Controller_Action_Helper_Abstract{
	public function direct($length = 8){
		$seeds = "lolfamebyjoshbour";
		$code = '';
		for($i=0;$i<$length;$i++){
			$code .= $seeds[mt_rand(0,strlen($seeds)-1)];
		}
		
		return $code;
	}
}