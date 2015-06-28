<?php

class Admin_AdminController extends Zend_Controller_Action {

    public function indexAction() {
        
    }
    
    public function ordersAction(){
         $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('user'));
        //check if user is logged in
        if ($auth->hasIdentity()) {
            $session = new Zend_Session_Namespace('userObj');
            $userObj = $session->__get('userObj');
            // This page only visible to operator
            if($userObj['user_type'] == 'operator'){
                
                $forms = Zend_Registry::get('forms');
                $form = new Zend_Form($forms->orders->adduser);
                $this->view->form = $form;
                
            }else{
                $this->_redirect('dashboard');
            }
        }else{
            $this->_redirect('dashboard');
        }
    }

    public function manageuserAction() {
        $model = new Application_Model_User();
        $select = $model->getallUser();
        $this->view->data = $this->_helper->Paginator($select);
    }

    public function changepasswordAction() {
        $forms = Zend_Registry::get('forms');
        $form = new Zend_Form($forms->acl->changepassword);
        $this->view->form = $form;
    }

}
