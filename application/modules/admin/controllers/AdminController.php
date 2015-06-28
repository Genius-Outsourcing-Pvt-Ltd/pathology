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
    
    public function saveresultAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $testId = $this->getRequest()->getParam('testId', '');
        $result = $this->getRequest()->getParam('result', '');
        $orderId = $this->getRequest()->getParam('orderId', '');
        if(!empty($testId) && !empty($result)){
            $orderTest = new Application_Model_DbTable_OrderTests();
            $orderTest->update(['results'=>$result], "test_id=$testId AND order_id=$orderId");
            echo json_encode(['response'=>'success']);
            die;
        }
        echo json_encode(['response'=>'error']);
        die;
    }

}
