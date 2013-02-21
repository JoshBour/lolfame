<?php
class Form_PostForm extends Zend_Form{
	public function __construct($options = null){
		$this->setMethod('post');
		if(!empty($options['id'])){
			$this->setAttrib('id', $options['id']);
		}
		$this->setAttrib('class','post-form')
		->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().$options['action']);

		$status = new Zend_Form_Element_Text('status');
		$status->setAttribs(array(
				'placeholder' => 'How are you?')
		)
		->setRequired()
		->addErrorMessage("Please check the username.")
		->addValidator('StringLength',false,array(4,15))
		->addValidator('Alnum')
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');

		$submit = new Zend_Form_Element_Submit('post-submit');
		$submit->setLabel('Post')
		->removeDecorator('DtDdWrapper');

		$this->setDecorators(array(  
				array('ViewScript', array(
				// the view template script
				'viewScript' => 'forms/_form_post.phtml'
			))
		));

		$this->addElements(array($status, $submit));
	}
}