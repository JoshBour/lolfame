<?php
class Form_UpdateForm extends Zend_Form{
	public function __construct($options = null){
		$this->setMethod('post')
		->setAttrib('class','standard-form')
		->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().$options['action'])
		->setEnctype(Zend_Form::ENCTYPE_MULTIPART);

		$user = Zend_Auth::getInstance()->getIdentity();

		$avatar = new Zend_Form_Element_File('avatar');
		$avatar->setDestination(APPLICATION_PATH . '/../public/images/users/'.$user->getId())
		->addValidator('Count', false, 1)
		->addValidator('Extension',false,'jpg,gif,png')
		->addValidator('Size',false,1024000)
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');

		$password = new Zend_Form_Element_Password('password');
		$password->setAttribs(array(
				'placeholder' => 'Please enter your password')
		)
		->addErrorMessage("Enter your new password.")
		->addValidator('StringLength',false,array(4,15))
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');

		$repassword = new Zend_Form_Element_Password('repassword');
		$repassword->setAttribs(array(
				'placeholder' => 'Please enter your password')
		)
		->addErrorMessage("Re-type your new password.")
		->addValidator('StringLength',false,array(4,15))
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');

		$email = new Zend_Form_Element_Text('email');
		$email->setAttribs(array(
				'placeholder' => 'Provide a valid email.'))
				->setValue($user->getEmail())
				->addErrorMessage("Enter your new email.")
				->addValidator('EmailAddress')
				->removeDecorator('label')
				->removeDecorator('Errors')
				->removeDecorator('htmlTag')
				->removeDecorator('DtDdWrapper');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Update')
		->removeDecorator('DtDdWrapper');

		$this->setDecorators(array(
				array(
						'ViewScript', array(
								'viewScript' => 'forms/_form_update.phtml'
						)
				)
		)
		);

		$this->addElements(array($avatar,$password, $repassword,$email, $submit));
	}
}