<?php

class CommentController extends Zend_Controller_Action
{
	
	private $_redirector = null;

    public function init()
    {
    	$this->_redirector = $this->_helper->getHelper('Redirector');
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
        			->addActionContext('add', 'json')
        			->addActionContext('remove', 'json')
        			->addActionContext('get-time','json')
        			->initContext();
    }

    public function indexAction()
    {
        // action body
    }

    public function listAction(){
    	$postId = $this->getRequest()->getParam('postId');
    	if(!empty($postId)){
    		$post = Default_Model_DbTable_Posts::findById(intval($postId));
    		$this->view->comments = $post->getComments();
    		$this->view->user = Zend_Auth::getInstance()->getIdentity();
    	}else{
    		$this->_helper->flashMessenger->addMessage('Something went wrong with the comment loading.');
    		$this->_redirector->gotoSimple('index','index');
    	}
    }
    
    public function addAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getParams();
    		$postId = $data['postId'];
    		$content = trim($data['content']);
    		$account = Zend_Auth::getInstance()->getIdentity();
    		try{
    			$comment = Default_Model_Comment::create($postId,$account->getId(),$content);
    			$this->view->message = "The comment has been added successfully.";
    			$this->view->success = 1;
    		}catch(Exception $e){
    			$this->view->message = "skata";
    			$this->view->success = 0;
    		}
    	}else{
    		$this->view->message = "Something went wrong.";
    		$this->view->success = 0;
    	}
    }
    
    public function removeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getParams();
    		$author = trim($data['author']);
			$postId = $data['postId'];
			$post = Default_Model_DbTable_Posts::findById($postId);
			$postAuthor = $post->getAuthor();
    		$user = Zend_Auth::getInstance()->getIdentity();
    		if($author == $user->getUsername() || $user->getUsername() == $post->getUsername()){
    			$id = $data['id'];
    			$comment = Default_Model_DbTable_Comments::findById($id);
    			try{
    				$comment->delete();
    				$this->view->message = "The comment has been deleted successfully.";
    				$this->view->success = 1;
    			}catch(RuntimeException $e){
    				$this->view->message = $e->getMessage();
    				$this->view->success = 0;
    			}
    		}else{
    			$this->view->success = 0;
    			$this->view->message = "You are not the owner of the comment.";
    		}
    	}else{
    		$this->view->message = "Something went wrong with the request.";
    		$this->view->success = 0;
    	}
    } 
    
    public function getTimeAction(){
    	$comId = $this->getRequest()->getParam('comId');
    	$comment = Default_Model_DbTable_Comments::findById(intval($comId));
    	if($post){
    		$time = $comment->getPostTime();
    		if(!empty($time)){
    			$this->view->time = $time;
    			$this->view->success = 1;
    		}else{
    			$this->view->success = 0;
    			$this->view->message = "No time was found";
    		}
    	}else{
    		$this->view->success = 0;
    		$this->view->message = "The comment was not found";
    	}
    }    

}

