<?php

class Admin_AdminController extends Zend_Controller_Action {

    public function indexAction() {
        
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
