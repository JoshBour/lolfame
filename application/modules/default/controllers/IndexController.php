<?php

class IndexController extends Zend_Controller_Action
{

	public function init()
	{
		if ($this->_helper->FlashMessenger->hasMessages()) {
			$this->view->messages = $this->_helper->FlashMessenger->getMessages();
		}
	}

	public function indexAction()
	{
		// action body
	}


}

