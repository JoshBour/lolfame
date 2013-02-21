<?php

class SummonerController extends Zend_Controller_Action
{

	private $_user = null;
	protected $_redirector = null;

	public function init()
	{
		$auth = Zend_Auth::getInstance();
		$this->_redirector = $this->_helper->getHelper('Redirector');

		if($auth->hasIdentity()){
			$this->_user = $auth->getIdentity();
		}else{
			$this->_redirector->gotoSimple('index','index');
		}
		if ($this->_helper->FlashMessenger->hasMessages()) {
			$this->view->messages = $this->_helper->FlashMessenger->getMessages();
		}
		$this->view->user = $this->_user;
	}

	public function indexAction()
	{
		$this->_redirector->gotoSimple('list','summoner');
	}

	public function addAction()
	{
		$errors = array();
		$addForm = new Form_SummonerForm(array(
				'action'=> '/summoner/add')
		);

		if($this->getRequest()->isPost()){
			$formData = $this->_request->getPost();
			if($addForm->isValid($formData)){
				$name = $addForm->getValue('name');
				$region = $addForm->getValue('region');
				$validateImg = $addForm->file;
				try{
					$summoner = Default_Model_Summoner::create($this->_user,$name,$region,$validateImg);
					
					// set the flash message
					$message = 'Summoner ' . $summoner->getName() . ' has been added successfully.<br />';
					if($summoner->getStatus() == 'pending'){
						$message .= 'We will validate the image along with the account really soon.';
					}else{
						$message .= 'However you need to validate it, otherwise someone else can claim it.';
					}
					// redirect to the list
					$this->_helper->flashMessenger->addMessage($message);
					$this->_redirector->gotoSimple('list','summoner');
				}catch(Exception $e){
					
					do{
						$this->view->errors = $e->getMessage() . '<br />' . $e->getTraceAsString();
					}while($e = $e->getPrevious());
				}
			}else{
				$this->view->errors = $addForm->getErrors();
				$addForm->populate($formData);
			}
		}
			$this->view->form = $addForm;
	}
	
	public function editAction()
	{
		$curName = $this->getRequest()->getParam('name');
		$region = $this->getRequest()->getParam('region');
		if(!$this->_user->hasSummoner($curName,$region)){
			$this->_helper->flashMessenger->addMessage('You are not the owner of this summoner.');
			$this->_redirector->gotoSimple('list','summoner');
		}else{
			$errors = array();
			$summoner = Default_Model_Summoner::findByName($curName,$region);
			$editForm = new Form_SummonerForm(array('action'=>'/summoner/edit/name/' . $curName . '/region/' . $region,'summoner'=>$curName,'region'=>$region));
			if($this->getRequest()->isPost()){
				$formData = $this->_request->getPost();
				if($editForm->isValid($formData)){
					$name = $editForm->getValue('name');
					$region = $editForm->getValue('region');
					// check if the user is validated and process accordingly
					if(!$summoner->getStatus() == 2){
						$validateImg = $editForm->file;
					
						$errors = $summoner->process($name, $region,$validateImg);
					}else{
						$errors = $summoner->process($name, $region);
					}
					if(empty($errors)){
						try{
							$summoner->setName($name);
							$summoner->setRegion($region);
							$summoner->save();
							
							$this->_helper->flashMessenger->addMessage('The Summoner has been updated successfully.');
							$this->_redirector->gotoSimple('list','summoner');
						}catch(Exception $e){
							do{
								$this->view->errors = $e->getMessage() . '<br />' . $e->getTraceAsString();
							}while($e = $e->getPrevious());
						}
					}else{
						$this->view->errors = $errors;
					}
				}else{
					$this->view->errors = $editForm->getErrors();
					$editForm->populate($formData);
				}
			}


			$this->view->summoner = $summoner;
			$this->view->form = $editForm;
		}

		// action body
	}



	public function viewAction()
	{
		$summoner = $this->getRequest()->getParam('name');
		$region = $this->getRequest()->getParam('region');
		if(Default_Model_Summoner::findByName($summoner,$region)){

		}else{
			$this->_helper->flashMessenger->addMessage('The summoner with name ["' .$region . "]" . $summoner . '" does not exist.');
			$this->_redirector->gotoSimple('index','summoner');
		}
	}

	public function listAction()
	{
		$userSummoners = $this->_user->getSummoners();
		$this->view->userSummoners = $userSummoners;

	}

	public function deleteAction(){
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$name = $this->getRequest()->getParam('name');
		$region = $this->getRequest()->getParam('region');
		if(!$this->_user->hasSummoner($name,$region)){
			$this->_helper->flashMessenger->addMessage('You are not the owner of this summoner.');
			$this->_redirector->gotoSimple('index','summoner');
		}else{
			$sumTable = new Default_Model_DbTable_Summoner();
			$summoner = Default_Model_Summoner::findByName($name,$region,true);
			$summoner->delete();
			$this->_helper->flashMessenger->addMessage('The Summoner [' . $region . ']' . $name . ' has been successfully deleted.');
			$this->_redirector->gotoSimple('list','summoner');

		}
	}

}









