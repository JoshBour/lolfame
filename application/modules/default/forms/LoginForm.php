<?php
class Form_LoginForm extends Zend_Form{
	public function __construct($options = null){
		$this->setMethod('post')
			 ->setAttrib('class','standard-form')
			 ->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().$options['action']);
		
		$username = new Zend_Form_Element_Text('username');
		$username->setAttribs(array(
								'placeholder' => 'Please enter your username')
								)
				 ->setRequired()
				 ->addErrorMessage("Please check the username.")
				 ->addValidator('StringLength',false,array(4,15))
				 ->addValidator('Alnum')
				 ->removeDecorator('label')
				 ->removeDecorator('Errors')
				 ->removeDecorator('htmlTag')
				 ->removeDecorator('DtDdWrapper');
		
		$password = new Zend_Form_Element_Password('password');
		$password->setAttribs(array(
								'placeholder' => 'Please enter your password')
							)
				->setRequired()
				 ->addErrorMessage("Please check the password.")
				->addValidator('StringLength',false,array(4,15))
				->removeDecorator('label')
				->removeDecorator('Errors')
				->removeDecorator('htmlTag')
				->removeDecorator('DtDdWrapper');
		
		$duration = new Zend_Form_Element_Checkbox('duration');
		$duration->removeDecorator('Errors')
				 ->removeDecorator('htmlTag')
				 ->removeDecorator('label')
				 ->removeDecorator('DtDdWrapper');
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Login')
			   ->removeDecorator('DtDdWrapper');
		
		$this->setDecorators(array(
								array(
									'ViewScript', array(
											'viewScript' => 'forms/_form_login.phtml'
											)
									)
								)
							);

		$this->addElements(array($username, $password, $duration, $submit));
	}
}