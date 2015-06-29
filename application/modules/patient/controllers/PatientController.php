<?php

use Application_Model_Patient as patient;
require 'PHPMailer-master/PHPMailerAutoload.php';
class Patient_PatientController extends Zend_Controller_Action {
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
        
    }

        public function vieworderAction() {

        $this->view->id = $id = $this->getRequest()->getParam('id', '');
//        $patient = patient::getPatientById($id);
        $patient = patient::getOrderById($id);
//        echo '<pre>'; print_r($patient); die;
        $this->view->userType = $this->userObj['user_type'];
        $this->view->patient = $patient;
        }
    public function viewreportAction() {
        $id = $this->getRequest()->getParam('id', '');
//        $this->view->patients = $patient = patient::getPatientById($id);
        $this->view->patients = $patient = patient::getOrderById($id);
//        print_r($patient); die;
        $age = '';
        if(!empty($patient[0]['birthday']) && $patient[0]['birthday'] != ''){
            $age = $this->calculatePatientAge($patient[0]['birthday']);
        }
        $this->view->age = $age;
    }

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
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!';   
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

    public function calculatePatientAge($dob) {
        $dob = date('m/d/Y', strtotime($dob));
        $tz  = new DateTimeZone('Europe/Brussels');
        return $age = DateTime::createFromFormat('d/m/Y', $dob, $tz)
                ->diff(new DateTime('now', $tz))->y;
    }

}
