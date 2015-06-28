<?php
use Application_Model_Patient as patient;
class Admin_AdminController extends Zend_Controller_Action {

    public $userObj;
    public function init() {
        parent::init();
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('user')); 
        if (!$auth->hasIdentity()) {
             $this->_redirect('/');
        }
        $session = new Zend_Session_Namespace('userObj');
        $this->userObj = $session->__get('userObj');
    }
    public function indexAction() {
        $model = new Application_Model_Patient();
        $id = 0;
        if($this->userObj['user_type'] == 'patient'){
            $id = $this->userObj['id'];
        }
        $patients = $model->getAllPatient($id);
//        print_r($patients); die;
        $this->view->patients = $patients;
    }
    
    public function vieworderAction() {
        $id = $this->getRequest()->getParam('id', '');
        $patient = patient::getPatientById($id);
//        echo '<pre>'; print_r($patient); die;
        $this->view->userType = $this->userObj['user_type'];
        $this->view->patient = $patient;
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
                
                $testObj = new Application_Model_Test();
                $tests = $testObj->getAll();
                $this->view->tests = $tests;
             
                
            }else{
                $this->_redirect('patient/orders');
            }
        }else{
            $this->_redirect('patient/orders');
        }
    }
    
    public function postorderAction(){
        $data = $this->getRequest()->getPost();
        $forms = Zend_Registry::get('forms');
        $form = new Zend_Form($forms->orders->adduser);
        if($form->isValid($data)){
            $patient = new Application_Model_Patient();
            if($patient->check_mrn($data)){
                $result = $patient->save($data);
                $erro_data['id'] = $result['id'];
                $erro_data['order_id'] = $result['order_id'];
                $erro_data['messages'] = ' Successfully saved';
            }else{
                 $erro_data['messages'] = ' MRN already exists';
            }
            

        }else{
            $erro_data['id'] = $this->_request->getParam('id',0);
            $erro_data['order_id'] = $this->_request->getParam('order_id',0);
            $erro_data['messages'] = '';
             $messages = $form->getMessages();
             foreach($messages as $row){
                $erro_data['messages'].= ((is_array($row))?(current ($row)) : $row).' ' ;
            }

        }
            header('Content-type: application/json');
            echo json_encode($erro_data);
            die();
    }
    public function searchAction(){
        $tag = $this->_request->getParam('tag',0);
        $pat_obj = new Application_Model_Patient();
        $result = $pat_obj->search($tag);
        header('Content-type: application/json');
        echo json_encode($result);
        die();
    }
    public function getpatientAction(){
        $id = $this->_request->getParam('id',0);
         $pat_obj = new Application_Model_Patient();
         $patient = $pat_obj->getById($id);
            header('Content-type: application/json');
        echo json_encode($patient);
        die();
    }
    public function manageuserAction() {
        $model = new Application_Model_User();
        $select = $model->getallUser();
        $this->view->data = $this->_helper->Paginator($select);
    }
    
    public function saveresultAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $testId = $this->getRequest()->getParam('testId', '');
        $result = $this->getRequest()->getParam('result', '');
        $orderId = $this->getRequest()->getParam('orderId', '');
        if(!empty($testId) && !empty($result)){
            patient::saveTestResult($result, $orderId, $testId);
            echo json_encode(['response'=>'success']);
            die;
        }
        echo json_encode(['response'=>'error']);
        die;
    }

}
