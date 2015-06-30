<?php

use Application_Model_Patient as patient;
require 'PHPMailer-master/PHPMailerAutoload.php';
class Patient_PatientController extends Zend_Controller_Action {
    public $userObj;
    /**
     * Check authorizatioin. if user is not logged in redirect him to login
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
    public function indexAction() {
        
    }
/**
 * Order detail of patient is viewed by this action.
 */
        public function vieworderAction() {

        $this->view->id = $id = $this->getRequest()->getParam('id', '');
        $patient = patient::getOrderById($id);
        $this->view->userType = $this->userObj['user_type'];
        $this->view->patient = $patient;
        }
        /**
         * Report view is rendered by this method
         */
    public function viewreportAction() {
        $id = $this->getRequest()->getParam('id', '');
        $this->view->patients = $patient = patient::getOrderById($id);
        $age = '';
        if(!empty($patient[0]['birthday']) && $patient[0]['birthday'] != ''){
            $age = $this->calculatePatientAge($patient[0]['birthday']);
        }
        $this->view->age = $age;
    }
/**
 * PDF of a test order is downloaded by this method
 */
    public function downloadpdfAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->getRequest()->getParam('id', '');
        $email = $this->getRequest()->getParam('email', 0);
        require_once 'mpdf/mpdf.php';
        $html .= $this->view->action('viewreport', 'patient', 'patient', array('id' => $id));
        $mpdf = new mPDF('+aCJK', 'A4', '', '', 15, 15, 15, 0, 0, 0);
        $mpdf->mirrorMargins = 0;
        $mpdf->setAutoBottomMargin = 'stretch';
        $mpdf->SetDisplayMode('fullwidth');
        $mpdf->WriteHTML($html);
        $fileName = 'PDF_Form' . time() . '.pdf';
        $mpdf->Output('tmp/'.$fileName, ($email)?'F':'D');
        if($email){
            $patient = patient::getOrderById($id);
            $mail = new PHPMailer();
            $mail->From = 'kashif.ir@gmail.com';
            $mail->FromName = 'Lab';
            $mail->addAddress($patient[0]['email'], ''); 
            $mail->addAttachment('tmp/'.$fileName); 
            $mail->Subject = 'Your Test Report';
            $mail->Body    = 'Please find attached report';   
            if(!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                unlink('tmp/'.$fileName);
                $flashMessenger = $this->_helper->getHelper('FlashMessenger');
                $flashMessenger->addMessage('mail_sent');
                $this->_redirect('/patient/orders');
            }
        }
    }
/**
 * Patient Age is calculated by this method based upon birthday
 * @param type $dob
 * @return type
 */
    public function calculatePatientAge($dob) {
        $dob = date('m/d/Y', strtotime($dob));
        if(empty($dob)){
            return '-';
        }
        $tz  = new DateTimeZone('Europe/Brussels');
        $dt = DateTime::createFromFormat('d/m/Y', $dob, $tz);
        if(empty($dt)){
            return '-';
        }
        return $age = $dt->diff(new DateTime('now', $tz))->y;
    }

}
