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
    
    public function save($dataPosted){
        $userObj = new Application_Model_User();
        $arrayCols = $userObj->fetchNew()->toArray();
        
        $patientObj = new Application_Model_Patient();
        $arrayColsPat = $patientObj->fetchNew()->toArray();
        
        $dataPosted['updated_at'] = date('Y-m-d H:i:s');
        $dataPosted['created_at'] = date('Y-m-d H:i:s');
        $user_data = array_intersect_key($dataPosted, $arrayCols);
        $patient_data = array_intersect_key($dataPosted, $arrayColsPat);
        
        
        $user_data['username']=$dataPosted['m_r_no'];
        $user_data['password']= md5($dataPosted['m_r_no']);
        $user_data['user_type']= 'patient';
        if(!empty($user_data['id'])){
             unset($user_data['created_at']);
             unset($patient_data['created_at']);
            $userObj->update($user_data, 'id=' . $user_data['id']);
            $user_id = $user_data['id'];
            $patient_data['user_id'] = $user_id;
            $patientObj->update($patient_data, 'user_id=' . $user_id);
        }else{
            unset($user_data['id']);
            $user_id = $userObj->insert($user_data);
            $patient_data['user_id'] = $user_id;
            $patientObj->insert($patient_data);
        }
           $result['id'] = $user_id;
           $result['order_id'] = '';
           // Save patient Tests

            $patient_orders_obj = new Application_Model_DbTable_PatientOrders();
            if(isset($dataPosted['test_id'])){
                $testIds = $dataPosted['test_id'];
            }else{
                $testIds = [];
            }
           if(!empty($dataPosted['order_id']) || count($testIds) ){
                if(empty($dataPosted['order_id'])){
                    $dataOrder['user_id'] = $user_id;
                    $dataOrder['created_at'] = date('Y-m-d H:i:s');
                    $dataOrder['total_tests'] = count($testIds);
                    $order_id = $patient_orders_obj->insert($dataOrder);
                }else{
                    $order_id = $dataPosted['order_id'];
                    $dataOrder['total_tests'] = count($testIds);
                    $patient_orders_obj->update($dataOrder, 'id = '.$order_id);
                }
                 $result['order_id'] = $order_id;
                // Delete the deleted tests
                $OrderTestsObj = new Application_Model_DbTable_OrderTests();
                $oldTests = $OrderTestsObj->fetchAll('order_id = '.$order_id);
                $oldTestsArr = [];
                foreach($oldTests as $oldTest){
                    if(!in_array($oldTest['test_id'],$testIds )){
                        $OrderTestsObj->delete('test_id = '.$oldTest['test_id'].' and order_id = '.$order_id );
                    }
                    $oldTestsArr[] = $oldTest['test_id'];
                }
                // Add new tests
                foreach($testIds as $newTest){
                    if(!in_array($newTest,$oldTestsArr )){
                        $OrderTestsObj->insert(['test_id' =>$newTest, 'order_id'=>$order_id] );
                    }
                }
           }

           return $result;
        
    }
}
