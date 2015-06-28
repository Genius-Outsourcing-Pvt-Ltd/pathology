<?php
use Application_Model_Patient as patient;
class Admin_AdminController extends Zend_Controller_Action {

    public function indexAction() {
        $model = new Application_Model_Patient();
        $patients = $model->getAllPatient();
        $this->view->patients = $patients;
    }
    
    public function vieworderAction() {
        $id = $this->getRequest()->getParam('id', '');
        $patient = patient::getPatientById($id);
//        echo '<pre>'; print_r($patient); die;
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
