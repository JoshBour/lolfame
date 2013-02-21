<?php

class PostController extends Zend_Controller_Action
{

	private $_redirector = null;
	private $_user = null;

	public function init()
	{
		$this->_redirector = $this->_helper->getHelper('Redirector');
		$this->_user = Zend_Auth::getInstance()->getIdentity();
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('list', 'html')
		->addActionContext('add', 'json')
		->addActionContext('remove','json')
		->addActionContext('rate','json')
		->addActionContext('get-rating','json')
		->addActionContext('get-time', 'json')
		->initContext();
	}

	public function indexAction()
	{
		// action body
	}

	public function listAction(){
		if($this->getRequest()->isGet()){
			$username = $this->getRequest()->getParam('user');
			if(!empty($username)){
				$user = Default_Model_DbTable_Account::findByUsername($username);
				$commentForm = new Form_CommentForm(array(
						'action' => '/comment/add/'
				));
				$this->view->user = $user;
				$this->view->commentForm = $commentForm;
			}else{
				#$this->_redirector->gotoSimple('index','index');
			}
		}else{
			$this->_redirector->gotoSimple('index','index');
		}
	}

	public function addAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getParams();
			$name = $data['targetName'];
			$status = trim($data['status']);
			// if the target name equals the user, then it's his own profile
			if($name == $this->_user->getUsername()){
				$coAuthor = null;
			}else{
				// else we have an "co author"
				$coAuthor = Default_Model_DbTable_Account::findByUsername($name);
				$coAuthor = $coAuthor->getId();
			}
				try{
					$post = Default_Model_Post::create($status, $this->_user->getId(),$coAuthor);
					$this->view->message = "The post has been added successfully.";
					$this->view->success = 1;
				}catch(Exception $e){
					do{
						$this->view->errors = $e->getMessage() . '<br />' . $e->getTraceAsString();
					}while($e = $e->getPrevious());
					$this->view->success = 0;
				}
		}else{
			$this->view->success = 0;
			$this->_redirector->gotoSimple('index','index');
		}
	}

	public function removeAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getParams();
			$author = $data['author'];
			$user = Zend_Auth::getInstance()->getIdentity();
			if($author == $user->getUsername()){
				$postId = $data['postId'];
				$post = Default_Model_DbTable_Posts::findById($postId);
				try{
					$post->delete();
					$this->view->message = "The post has been deleted successfully.";
					$this->view->success = 1;
				}catch(RuntimeException $e){
					do{
						$this->view->message = $e->getMessage() . '<br />' . $e->getTraceAsString();
					}while($e = $e->getPrevious());
					$this->view->success = 0;
				}
			}else{
				$this->view->success = 0;
				$this->view->message = "You are not the owner of the post.";
			}
		}else{
			$this->view->success = 0;
			$this->_redirector->gotoSimple('index','index');
		}
	}

	public function rateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getParams();
			$user = Zend_Auth::getInstance()->getIdentity();
			$postId = trim($data['postId']);
			$action = $data['actionId'];
			$rating = $user->hasRated($postId);
			if($rating){
				$rating->setAction($action);
				try{
					$rating->save();
					$this->view->success = 1;
				}catch(Exception $e){
					$this->view->success = 0;
					$this->view->message = $e->getMessage() . $action;
				}
			}else{
				try{
					$rate = Default_Model_Rating::create($user->getId(), $postId, $action);
					$this->view->success = 1;
				}catch(Exception $e){
					do{
						$this->view->message = $e->getMessage() . '<br />';
					}while($e = $e->getPrevious());
					$this->view->success = 0;
				}
			}
		}else{
			$this->view->success = 0;
			$this->_redirector->gotoSimple('index','index');
		}
	}
	
	public function getRatingAction(){
		$postId = $this->getRequest()->getParam('postId');
		$post = Default_Model_DbTable_Posts::findById(intval($postId));
		if($post){
			$ratings = $post->getRatingArray();
			if(!empty($ratings)){
				$likeNum = count($ratings['likes']);
				$dislikeNum = count($ratings['dislikes']);
				$total = $likeNum + $dislikeNum;
				$likeWidth = (100/$total) * $likeNum;
				$dislikeWidth = (100/$total) * $dislikeNum;
				$this->view->likeWidth = $likeWidth;
				$this->view->dislikeWidth = $dislikeWidth;
				$this->view->success = 1;
			}else{
				$this->view->success = 0;
				$this->view->message = "No ratings found";
			}
		}else{
				$this->view->success = 0;
				$this->view->message = "The post was not found";
		}
	}
	
	public function getTimeAction(){
		$postId = $this->getRequest()->getParam('postId');
		$post = Default_Model_DbTable_Posts::findById(intval($postId));
		if($post){
			$time = $post->getPostTime();
			if(!empty($time)){
				$this->view->time = $time;
				$this->view->success = 1;
			}else{
				$this->view->success = 0;
				$this->view->message = "No time was found";
			}
		}else{
			$this->view->success = 0;
			$this->view->message = "The post was not found";
		}		
	}
}

