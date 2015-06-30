<?php
use Application_Model_Patient as patient;
class Admin_AdminController extends Zend_Controller_Action {

    public $userObj;
    /**
     * Authorization goes over there
     */
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
    /**
     * Listing page of Operator User and Patient Portal
     * where all pending orders are listed for operator user
     * and all test are listed for patient
     */
    public function indexAction() {
        $messages = $this->_helper->flashMessenger->getMessages();
        $message = '';
        if(!empty($messages) && $messages[0] == 'mail_sent'){
            $message = $messages[0];
        }
        $this->view->mail_sent = $message;
        $model = new Application_Model_Patient();
        $id = 0;
        if($this->userObj['user_type'] == 'patient'){
            $id = $this->userObj['id'];
        }
        $patients = $model->getAllPatient($id);
        $this->view->user_type = $this->userObj['user_type'];
//        print_r($patients); die;
        $this->view->patients = $patients;
    }
    /**
     * The order detail page of an order for Operator User
     */
    public function vieworderAction() {

        $this->view->id = $id = $this->getRequest()->getParam('id', '');
        $patient = patient::getOrderById($id);
        $this->view->userType = $this->userObj['user_type'];
        $this->view->patient = $patient;
        }
        /**
         * The Orders page for Operator user from where operator user can enter
         * or update the patient information and can order his/her test
         */
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
    /**
     * The Ajax Post method where above method post request is submited to save
     * a patient and his tests
     */
    public function postorderAction(){
        $session = new Zend_Session_Namespace('userObj');
        $userObj = $session->__get('userObj');
        // This page only visible to operator
        if($userObj['user_type'] == 'operator'){
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
        }else{
            $erro_data['messages'] = 'You don\'t have permission to this page';
        }
            header('Content-type: application/json');
            echo json_encode($erro_data);
            die();
    }
    /**
     * Search request for existing patient is handled over here
     */
    public function searchAction(){
        $tag = $this->_request->getParam('tag',0);
        $pat_obj = new Application_Model_Patient();
        $result = $pat_obj->search($tag);
        header('Content-type: application/json');
        echo json_encode($result);
        die();
    }
    /**
     * A patient which is search his information is fetched by this method
     */
    public function getpatientAction(){
        $id = $this->_request->getParam('id',0);
         $pat_obj = new Application_Model_Patient();
         $patient = $pat_obj->getById($id);
            header('Content-type: application/json');
        echo json_encode($patient);
        die();
    }

    /**
     * When Operator user saves results request is posted to this method
     * it saves the results of test for which a patient has applied.
     */
    public function saveresultAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $data = $this->getRequest()->getPost();
            patient::saveTestResult($data);
            echo json_encode(['messages'=>'Results are saved.']);
            die;
    }

}
