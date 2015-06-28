<?php

class Login_LoginController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('user'));
        if ($auth->hasIdentity()) {
            $this->_redirect('dashboard');
        }
        
        $this->_helper->layout->setLayout('login');
        $forms = Zend_Registry::get('forms');
        $form = new Zend_Form($forms->user->login);
        $userManagement = new Application_Model_User();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            $error = array();
            if ($form->isValid($data)) {
                    $userName = $form->username->getValue();
                    $password = $form->password->getValue();
                    $remember = $this->_request->getParam('remember', 0);

                    $userTable = new Application_Model_DbTable_User();
                    $userExits = $userTable->fetchRow('username = "' . $userName . '" AND password= "' . md5($password) . '" AND deleted_at IS NULL');
                    $magUser = false;
                    if (!empty($userExits)) {
                        $userExits = $userExits->toArray();
                        if ($userExits['id'] == 0 || $userExits['id'] == '') {
                            $magUser = true;
                        }
                        $session = new Zend_Session_Namespace('userObj');
                        $session->__set('userObj', $userExits);
                        
                    }

                    if ($magUser) {
                        $form->username->setErrors(array(
                             'Invalid username or password'
                        ));
                    } else {
                        $response = $userManagement->login($userName, md5($password), $remember);
                    }
                    if ($response == 'success') {
                       $this->_redirect('dashboard');
                    } else {
                        $form->username->setErrors(array(
                            'Invalid username or password'
                        ));
                    }
            } 
        }
        $this->view->form = $form;
    }

    public function logoutAction() {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('user'));
        if ($auth->hasIdentity()) {
            $auth->clearIdentity();
            Zend_Session::forgetMe();
        }
        Zend_Session::destroy();
        $this->_redirect('/');
    }
}
