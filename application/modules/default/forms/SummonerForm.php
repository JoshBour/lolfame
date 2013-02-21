<?php
class Form_SummonerForm extends Zend_Form{
	public function __construct($options = null){
		$this->setMethod('post')
		->setAttrib('class','standard-form')
		->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().$options['action'])
		->setEnctype(Zend_Form::ENCTYPE_MULTIPART);

		$user = Zend_Auth::getInstance()->getIdentity();

		$sumName = new Zend_Form_Element_Text('name');
		$sumName->setAttribs(array(
				'placeholder' => 'Please enter the Summoner\'s name.')
		)
		->setRequired()
		->addErrorMessage("Please check the name.")
		->addValidator('StringLength',false,array(4,15))
		->addValidator('Alnum')
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag');

		$regions = array("euw" => "EUW",
				"eune" => "EUNE",
				"na"=>"NA",
				"br" => "Brazil");

		$region = new Zend_Form_Element_Select('region');
		$region->addMultiOptions($regions)
		->addErrorMessage('Please check the region.')
		->removeDecorator('label')
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');

		$file = new Zend_Form_Element_File('file');
		$file->setDestination(APPLICATION_PATH . '/../public/images/users/'.$user->getId())
		->addValidator('Count', false, 1)
		->addValidator('Extension',false,'jpg,gif,png')
		->addValidator('Size',false,1024000)
		->removeDecorator('label')
		->setRequired(false)
		->removeDecorator('Errors')
		->removeDecorator('htmlTag')
		->removeDecorator('DtDdWrapper');
		
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Add')
		->removeDecorator('DtDdWrapper');
		
		$this->addElements(array($sumName,$region,$file, $submit));
		
		
		// if the summoner exists in the options array, it means that this is an update
		if(isset($options['summoner'])){
			$summoner = Default_Model_Summoner::findByName($options['summoner'],$options['region']);
			$sumName->setValue($summoner->getName());
			$region->setValue($summoner->getRegion());
			$file->setValue($summoner->getValidationImage());
			$submit->setLabel('Update');
			
			// if he is validated, set the file input to readonly
			if($summoner->getStatus() == 2){
				$file->setAttrib('class','verified');
				$this->removeElement('file');
			}else if($summoner->getStatus() == 1){
				$file->setAttrib('class','pending');
				$this->removeElement('file');
			}
			
		}		
		
		//$this->clearDecorators();
		$this->setDecorators(array(
				array(
						'ViewScript', array(
								'viewScript' => 'forms/_form_add_summoner.phtml'
						)
				)
		)
		);
	}
}