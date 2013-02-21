<?php

class AccountController extends Zend_Controller_Action
{
	private $auth = null;
	protected $_redirector = null;
	protected $_user = null;

	public function init()
	{
		if ($this->_helper->FlashMessenger->hasMessages()) {
			$this->view->messages = $this->_helper->FlashMessenger->getMessages();
		}
		$this->_redirector = $this->_helper->getHelper('Redirector');
		$this->auth = Zend_Auth::getInstance();
		if($this->auth->hasIdentity()){
			$this->_user = $this->auth->getIdentity();
		}
	}

	public function indexAction()
	{

	}

	public function loginAction()
	{
		if(!empty($this->_user)){
			$this->_redirector->gotoSimple('index','index');
		}
		$this->view->headTitle = "LoLFame - Login to your account.";
			
		$loginForm = new Form_LoginForm(array(
				'action'=> '/account/login')
		);
		if($this->getRequest()->isPost()){
			$formData = $this->_request->getPost();
			if($loginForm->isValid($formData)){
				$username = trim($loginForm->getValue('username'));
				$password = trim($loginForm->getValue('password'));
				$duration = $loginForm->getValue('duration');

				$password = sha1(Default_Model_Object::salt($password));

				$authAdapter = $this->getAuthAdapter();
				$authAdapter->setIdentity($username)
				->setCredential($password);
				$result = $this->auth->authenticate($authAdapter);
				if(!$result->isValid()){
					$this->view->errors = $result->getMessages();
				}else{
					$user = Default_Model_DbTable_Account::findById($authAdapter->getResultRowObject()->id);
					$this->loginUser($user,$duration);
					$this->_helper->flashMessenger->addMessage('Welcome back, ' . $user->getUsername());
					$this->_redirector->gotoSimple('index','index');
				}
			}else{
				$loginForm->populate($formData);
				$this->view->errors = $loginForm->getErrors();
			}
		}
		$this->view->form = $loginForm;
	}

	public function logoutAction()
	{
		if(!empty($this->_user)){
			$this->auth->clearIdentity();
		}
		$this->_redirector->gotoSimple('index','index');
			
	}
	// needs rework
	public function registerAction()
	{
		$this->view->headTitle = "LoLFame - Create a new account.";
			
		$registerForm = new Form_RegisterForm(array(
				'action'=> '/account/register')
		);

		if($this->getRequest()->isPost()){
			$formData = $this->_request->getPost();
			if($registerForm->isValid($formData)){

				// get the values
				$username = trim($registerForm->getValue('username'));
				$password = trim($registerForm->getValue('password'));
				$repassword = trim($registerForm->getValue('repassword'));
				$email = trim($registerForm->getValue('email'));
				$ip = $this->getRequest()->getServer('REMOTE_ADDR');

				// create the user
				try{
					$user = Default_Model_User::create($username, $password, $repassword, $email, $ip);
						
					// login the user
					$this->loginUser($user);
					$this->_helper->flashMessenger->addMessage(
							'You have been successfully registered!');

					$this->_redirector->gotoSimple('index','index');
				}catch(Exception $e){
					$this->view->errors = $e->getMessage();
				}
			}else{
				$registerForm->populate($formData);
				$this->view->errors = $registerForm->getErrors();
			}
		}
		$this->view->form = $registerForm;
	}

	public function updateAction()
	{
		if(empty($this->_user)){
			$this->_redirector->gotoSimple('index','index');
		}
		// set some required variables
		$user = $this->auth->getIdentity();
		$account = new Default_Model_DbTable_Account();
		$errors = array();
		$updateForm = new Form_UpdateForm(array('action'=>'update'));
		// do the request related checkings
		if($this->getRequest()->isPost()){
			$formData = $this->_request->getPost();
			if($updateForm->isValid($formData)){
				$password = trim($updateForm->getValue('password'));
				$repassword = trim($updateForm->getValue('repassword'));
				$email = trim($updateForm->getValue('email'));
				$avatar = $updateForm->avatar;
				
				// check if the passwords match
				if($password != $repassword){
					$errors[] = 'The passwords are not the same.';
				}

				// check if the email exists
				if($email != $user->getEmail()){
					$result = $account->findByUsernameOrEmail('', $email);
					if(!empty($result)){
						$errors[] = 'The email already exists, please try a different one.';
					}
				}

				if(!$avatar->receive()){
					$errors[] = 'Something went worng with the file upload, please try again.';
				}

				if(empty($errors)){
					if(!empty($password)){
						$user->setPassword(Default_Model_Object::salt($password));
					}
					$user->setEmail($email);
					$value = $avatar->getValue();
					if(!empty($value)){
						$user->setAvatar($avatar->getValue());
					}
					try{
						$user->save();
						$this->_helper->flashMessenger->addMessage('Your account has been successfully updated.');
						$this->_redirector->gotoSimple('update','account');
					}catch(RuntimeException $e){
						$this->view->errors = $e->getMessage();
						$this->_helper->flashMessenger->addMessage('There was an error with the account update.');
						$this->_redirector->gotoSimple('update','account');
					}
				}else{
					$this->view->errors = $errors;
				}
			}else{
				$updateForm->populate($formData);
				$this->view->errors = $updateForm->getErrors();
			}
		}
		$this->view->form = $updateForm;
	}

	public function lostAction(){
	}

	public function profileAction(){
		$name = $this->getRequest()->getParam('name');
		$this->view->bodyClass = 'profile';
		$account = Default_Model_DbTable_Account::findByUsername($name);
		if($account){
			$this->view->headTitle = $name;
			if(!empty($this->_user) && $this->_user == $account){
				$this->view->isOwner = true;
			}
			$this->view->user = $account;
			$postForm = new Form_PostForm(array(
											'action' => '/post/add',
											'id' => 'post-add-form',
											'class' => 'post-form'
											));
			$this->view->postForm = $postForm;
		}else{
			$this->_helper->flashMessenger->addMessage('The page you requested does not exist.');
			$this->_redirector->gotoSimple('index','index');
		}
	}

	private function getAuthAdapter(){
		$authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
		$authAdapter->setTableName('accounts')
		->setIdentityColumn('username')
		->setCredentialColumn('password');
			
		return $authAdapter;
	}

	private function loginUser($user,$duration = 1){
		$auth = Zend_Auth::getInstance();
		$auth->getStorage()->write($user);
		if($duration == 1){
			Zend_Session::rememberMe(31536000);
		}else{
			Zend_Session::forgetMe();
		}
	}

}









