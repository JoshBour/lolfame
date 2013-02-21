<?php
interface Default_Model_Interface_Creatable{
	public static function create();
	
	public function update();
	protected function _validate(array $values);
	
}