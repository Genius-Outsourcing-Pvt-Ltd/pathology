<?php

class Application_Model_Patient extends Application_Model_DbTable_Patient {

//    public function init() {
//        parent::init();        
//    }

    public function getAllPatient() {
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $select = $obj->from('patients as p', array('id'))
                ->join('users as u', 'p.user_id=u.id', array(new Zend_Db_Expr('CONCAT(first_name," ",last_name) as name')))
                ->join('patient_orders as po', 'u.id=po.patient_id', array('id as order_id', 'total_tests', 'total_results_calculated','created_at'))
                ->join('order_tests as ot', 'po.id=ot.order_id',array('results'))
                ->join('tests as t', 't.id=ot.test_id', array('name as test_name'))
                ->where('po.total_tests != po.total_results_calculated')
                ->group('po.id');
        $result = $select->query()->fetchAll();
        return $result;
    }

    public static function getPatientById($id) {
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $select = $obj->from('patients as p', array('id'))
                ->join('users as u', 'p.user_id=u.id', array(new Zend_Db_Expr('CONCAT(first_name," ",last_name) as name')))
                ->join('patient_orders as po', 'u.id=po.patient_id', array('id as order_id', 'total_tests', 'total_results_calculated','created_at'))
                ->join('order_tests as ot', 'po.id=ot.order_id',array('results','test_id as test_id'))
                ->join('tests as t', 't.id=ot.test_id', array('name as test_name'))
                ->where('p.id=?',$id);
        $result = $select->query()->fetchAll();
        return $result;
    }
    
    public static function resultCalculated($orderId){
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        return $obj->from('order_tests as ot', array('test_id'))->where('order_id=?',$orderId)->where('results != ""')->query()->rowCount();
    }
    
    public static function saveTestResult($result, $orderId, $testId){
        $orderTest = new Application_Model_DbTable_OrderTests();
        $orderTest->update(['results'=>$result, 'result_calculated_at'=>date('Y-m-d H:i:s')], "test_id=$testId AND order_id=$orderId");
        $resultCalculated = self::resultCalculated($orderId);
        $po = new Application_Model_DbTable_PatientOrders();
        $po->update(['total_results_calculated'=>$resultCalculated], 'id='.$orderId);
        return 'success';
    }
    

}
