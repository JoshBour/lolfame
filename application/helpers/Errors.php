<?php 
class Zend_View_Helper_Errors extends Zend_View_Helper_Abstract{
	public function Errors($errors){
		echo '<ul id="errors">';
		if(is_array($errors)){
			foreach($errors as $error => $message){
				if(is_array($message) && !empty($message)){
					printf('<li>%s</li>',$message[0]);
				}else if(!empty($message)){
					printf('<li>%s</li>',$message);
				}
			}
		}else{
			printf('<li>%s</li>',$errors);
		}
		echo '</ul>';
	}
}