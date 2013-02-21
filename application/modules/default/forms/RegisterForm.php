<?php
class Form_RegisterForm extends Zend_Form{
	public function __construct($options = null){
		$this->setAction($options['action'])
		->setMethod('post')
		->setAttrib('class','standard-form');

		$username = new Zend_Form_Element_Text('username');
		$username->setAttribs(array(
				'placeholder' => 'Please enter your username.')
		)
		->setRequired()
		->addErrorMessage("Please check the username.")
		->addValidator('StringLength',false,array(4,15))
		->addValidator('Alnum')
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag');

		$password = new Zend_Form_Element_Password('password');
		$password->setAttribs(array(
				'placeholder' => 'Please enter your password.')
		)
		->setRequired()
		->addErrorMessage("Please check the password.")
		->addValidator('StringLength',false,array(4,15))
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');
		
		$repassword = new Zend_Form_Element_Password('repassword');
		$repassword->setAttribs(array(
				'placeholder' => 'Retype the password.')
		)
		->setRequired()
		->addErrorMessage("You have to retype the password")
		->addValidator('StringLength',false,array(4,15))
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');		

		$email = new Zend_Form_Element_Text('email');
		$email->setAttribs(array(
				'placeholder' => 'Provide a valid email.'))
		->setRequired()
		->addErrorMessage("Please check the email.")
        ->addValidator('EmailAddress')
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Register')
		->removeDecorator('DtDdWrapper');
		
		$this->setDecorators(array(
				array(
						'ViewScript', array(
								'viewScript' => 'forms/_form_register.phtml'
						)
				)
		)
		);
		
		$this->addElements(array($username,$repassword, $password, $email, $submit));		
		
	}
}