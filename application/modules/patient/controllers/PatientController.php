<?php
use Application_Model_Patient as patient;
class Patient_PatientController extends Zend_Controller_Action {

    public function indexAction() {
        
    }

    public function viewreportAction() {
        $id = $this->getRequest()->getParam('id', '');
        $patient = patient::getPatientById($id);
//        print_r($patient); die;
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

}
