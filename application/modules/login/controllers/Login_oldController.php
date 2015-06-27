<?php

class Login_LoginController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('login');
        $forms = Zend_Registry::get('forms');
        $form = new Zend_Form($forms->user->login);
        $userManagement = new Application_Model_User();
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('user'));
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $error = array();
            if ($data['username'] == '') {
                $error['username'] = 'Username is required!';
            }
            if ($data['password'] == '') {
                $error['password'] = 'Password is required!';
            }
            if (empty($error)) {
                if (!$form->isErrors()) {

                    $userName = $this->_request->getParam('username', '');
                    $password = $this->_request->getParam('password', '');
                    $remember = $this->_request->getParam('remember');

                    if ($remember > 0) {
                        $rememberMe = 1;
                    } else {
                        $rememberMe = 1;
                    }

                    $userTable = new Application_Model_DbTable_User();
                    $userExits = $userTable->fetchRow('email = "' . $userName . '" AND status = "Active" AND password= "' . md5($password) . '"');
                    $magUser = false;
                    if (!empty($userExits)) {
                        $userExits = $userExits->toArray();
                        if ($userExits['id'] == 0 || $userExits['id'] == '') {
                            $magUser = true;
                        }
                    }
//                    echo '<pre>'; print_r($userExits);
//                    var_dump($magUser); die;
                    if ($magUser) {
                        $response = "Invalid username or password";
                    } else {
                        $response = $userManagement->login($userName, md5($password), $rememberMe);
                    }
//                    echo $response; die;
                    if ($response == 'success') {
                        $acl = new Application_Model_Acl();
                        $error['success'] = $acl->department; 
//                        $this->_redirect('admin/acl/reports');
                    } else {
                        $error['error'] = 'Invalid username or password';
                    }
                    echo Zend_Json::encode($error);
                    die;
                }
            } else {
                echo Zend_Json::encode($error);
                die;
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
        $this->_redirect('login/login/index');
    }

}

