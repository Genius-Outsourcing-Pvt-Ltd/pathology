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

}
