<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	private $_acl = null;
	
	protected function _initLoaderResource()
	{
		$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
				'basePath'  => APPLICATION_PATH . '/modules/default',
				'namespace' => 'Default'
		));
		$resourceLoader->addResourceTypes(array(
				'model' => array(
						'namespace' => 'Model',
						'path'      => 'models/'
				)
		));
	}

	protected function _initAutoload() {
		$modelLoader = new Zend_Application_Module_Autoloader(array(
				'namespace' => '',
				'basePath' => APPLICATION_PATH.'/modules/default'));
		if(Zend_Auth::getInstance()->hasIdentity()) {
			Zend_Registry::set('group', Zend_Auth::getInstance()->getStorage()->read()->getGroup());
		} else {
			Zend_Registry::set('group', 'guests');
		}

		//         $this->_acl = new Model_LibraryAcl;
		//         $this->_auth = Zend_Auth::getInstance();

		//         $fc = Zend_Controller_Front::getInstance();
		//         $fc->registerPlugin(new Plugin_AccessCheck($this->_acl));

		return $modelLoader;
	}

	function _initViewHelpers() {
		$this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();

		$view->setHelperPath(APPLICATION_PATH.'/helpers', '');

		$view->doctype('XHTML1_STRICT');
		$view->headMeta()->appendHttpEquiv('Content-type', 'text/html;charset=utf-8')
						 ->appendName('description', 'Using view helpers in Zend_view');


		#$navContainerConfig = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml', 'nav');
		#$navContainer = new Zend_Navigation($navContainerConfig);

		#$view->navigation($navContainer);#->setAcl($this->_acl)->setRole(Zend_Registry::get('role'));
	}
	
	function _initConfig(){
		$config = new Zend_Config($this->getoptions());
		Zend_Registry::set('config',$config);
	}
	
	function _initActionHelpers(){
		Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH.'/helpers','Helper');
	}

	function _initRoutes(){
		$fc = Zend_Controller_Front::getInstance();
		$router = $fc->getRouter();	
		
		// acount related routes
		
		
		$route = new Zend_Controller_Router_Route(
				'/:name',
				array('controller' => 'account', 'action' => 'profile'));
		
		$router->addRoute('profile',$route);

// 		$route = new Zend_Controller_Router_Route(
// 				'/account/profile/:name',
// 				array('controller' => 'account', 'action' => 'profile'));
// 		$router->addRoute('profile',$route);
		
		$route = new Zend_Controller_Router_Route_Static(
				'/login',
				array('controller' => 'account','action'=>'login'));

		$router->addRoute('login',$route);

		$route = new Zend_Controller_Router_Route_Static(
				'/logout',
				array('controller' => 'account', 'action' => 'logout'));
		
		$router->addRoute('logout', $route);
		
		$route = new Zend_Controller_Router_Route_Static(
				'/register',
				array('controller' => 'account', 'action' => 'register'));
		
		$router->addRoute('register', $route);
		
		// summoner related routes
		$route = new Zend_Controller_Router_Route(
				'/summoner/edit/:region/:name',
				array('controller' => 'summoner', 'action' => 'edit'));
		
		$router->addRoute('edit-summoner', $route);
		
		
		$route = new Zend_Controller_Router_Route(
				'/summoner/view/:region/:name',
				array('controller' => 'summoner', 'action' => 'view'));
		$router->addRoute('view-summoner', $route);
			
	}
	
	function _initEmail()
	{
	
		$emailConfig = array('auth'     => 'login',
				'username' => Zend_Registry::get('config')->email->username,
				'password' => Zend_Registry::get('config')->email->password,
				'ssl'      => 'tls',
				'port'     => Zend_Registry::get('config')->email->port);
	
		$mailTransport = new Zend_Mail_Transport_Smtp(Zend_Registry::get('config')->email->server, $emailConfig);
	
		Zend_Mail::setDefaultTransport($mailTransport);
	
	}

}

