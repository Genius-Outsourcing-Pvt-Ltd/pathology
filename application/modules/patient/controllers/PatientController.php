<?php

use Application_Model_Patient as patient;

class Patient_PatientController extends Zend_Controller_Action {

    public function indexAction() {
        
    }

    public function viewreportAction() {
        $id = $this->getRequest()->getParam('id', '');
        $this->view->patients = $patient = patient::getPatientById($id);
        $age = '';
        if(!empty($patient[0]['birthday']) && $patient[0]['birthday'] != ''){
            $age = $this->calculatePatientAge($patient[0]['birthday']);
        }
        $this->view->age = $age;
    }

    public function downloadpdfAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        require_once 'mpdf/mpdf.php';
        $html .= $this->view->action('viewreport', 'patient', 'patient', array('id' => '1'));
        $mpdf = new mPDF('+aCJK', 'A4', '', '', 15, 15, 15, 0, 0, 0);
        $mpdf->mirrorMargins = 0;
        $mpdf->setAutoBottomMargin = 'stretch';
        $mpdf->SetDisplayMode('fullwidth');
        $mpdf->WriteHTML($html);
        $mpdf->Output('PDF_Form' . time() . '.pdf', 'D');
        die('End');
    }

    public function calculatePatientAge($dob) {
        $dob = date('m/d/Y', strtotime($dob));
        $tz  = new DateTimeZone('Europe/Brussels');
        return $age = DateTime::createFromFormat('d/m/Y', $dob, $tz)
                ->diff(new DateTime('now', $tz))->y;
    }

}
