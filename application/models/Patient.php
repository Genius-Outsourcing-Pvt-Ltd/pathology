<?php

class Application_Model_Patient extends Application_Model_DbTable_Patient {

//    public function init() {
//        parent::init();        
//    }
/**
 * This Method is used to return the list the Orders of Patients
 * If id is passed only orders of that patient/user will be returned 
 * else orders of all users/patients will be returned of which results are not completly entred.
 * This method is used to list orders in admin as well as list patient's order in patient portal
 * @param user_id[optional] $id
 * @return type
 */
    public function getAllPatient($id) {
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $select = $obj->from('patients as p', array('id'))
                ->join('users as u', 'p.user_id=u.id', array(new Zend_Db_Expr('CONCAT(first_name," ",last_name) as name')))
                ->join('patient_orders as po', 'u.id=po.user_id', array('id as order_id', 'total_tests', 'total_results_calculated','created_at'))
                ->order('po.id desc')
                ->group('po.id');
        if(!empty($id)){
            $select->where('u.id=?',$id);
        }else{
            $select->where('po.total_tests !='.new Zend_Db_Expr('IFNULL(po.total_results_calculated,0)'));
        }
        $result = $select->query()->fetchAll();
        return $result;
    }
/**
 * This method is used to return the detail of a test order based on the 
 * order id passed to it. this method is used in patient portal as well as in 
 * operator user portal where he enters results
 * @param order_id $id
 * @return type
 */
        public static function getOrderById($id) {
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $select = $obj->from('patients as p', array('id','ref_by_doctor','m_r_no','created_at'))
                ->join('users as u', 'p.user_id=u.id', array(new Zend_Db_Expr('CONCAT(first_name," ",last_name) as name'),'u.*','u.id as userId'))
                ->join('patient_orders as po', 'u.id=po.user_id', array('id as order_id', 'total_tests', 'total_results_calculated','created_at as date_in'))
                ->join('order_tests as ot', 'po.id=ot.order_id',array('results','test_id as test_id'))
                ->join('tests as t', 't.id=ot.test_id', array('name as test_name', 'reference_value'))
                ->where('po.id=?',$id);
        $result = $select->query()->fetchAll();
        return $result;
    }
    /**
     * this method is used to return the number of results which are entered
     * for tests of an order
     * @param order-id $orderId
     * @return type
     */
    public static function resultCalculated($orderId){
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        return $obj->from('order_tests as ot', array('test_id'))->where('order_id=?',$orderId)->where('results != ""')->query()->rowCount();
    }
    /**
     * This method is used to save the results of a test order.
     * @param array $data
     * @return string
     */
    public static function saveTestResult($data){
        
        $orderId = $data['order_id'];
        foreach($data['test_id'] as $i=>$testId){
            if($data['result'][$i] == ''){
                $result = null; 
                $at = null;
            }else{
                $result = $data['result'][$i];
                 $at = date('Y-m-d H:i:s');
            }
         

        $orderTest = new Application_Model_DbTable_OrderTests();
        $orderTest->update(['results'=>$result, 'result_calculated_at'=>$at], "test_id=$testId AND order_id=$orderId");
        }
        $resultCalculated = self::resultCalculated($orderId);
        $po = new Application_Model_DbTable_PatientOrders();
        $po->update(['total_results_calculated'=>$resultCalculated], 'id='.$orderId);
        return 'success';
    }
    /**
     * This method is used to save the new patient or update the information of 
     * an existing patient. a user with the mrn.no is create with the username and
     * password same as mrn. no.
     * it also enters the order of patient
     * @param type $dataPosted
     * @return array id of patient and order
     */
    public function save($dataPosted){
        $userObj = new Application_Model_User();
        $arrayCols = $userObj->fetchNew()->toArray();
        
        $patientObj = new Application_Model_Patient();
        $arrayColsPat = $patientObj->fetchNew()->toArray();
        
        $dataPosted['updated_at'] = date('Y-m-d H:i:s');
        $dataPosted['created_at'] = date('Y-m-d H:i:s');
        $user_data = array_intersect_key($dataPosted, $arrayCols);
        // filter the posted data to model attributes
        $patient_data = array_intersect_key($dataPosted, $arrayColsPat);
        unset($patient_data['id']);
        
        $user_data['username']=$dataPosted['m_r_no'];
        $user_data['password']= md5($dataPosted['m_r_no']);
        $user_data['user_type']= 'patient';
        //update user and patient if id  passed
        if(!empty($user_data['id'])){
             unset($user_data['created_at']);
             unset($patient_data['created_at']);
            $userObj->update($user_data, 'id=' . $user_data['id']);
            $user_id = $user_data['id'];
            $patient_data['user_id'] = $user_id;
            $patientObj->update($patient_data, 'user_id=' . $user_id);
        }else{
           //insert new user and patient if id not passed
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
                // Delete the removed tests
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
    /**
     * This method is used to return the data set of users which match the 
     * tag passed to their name and mrn no
     * from search.
     * @param string $tag
     * @return array of users
     */
    public function search($tag){
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $select = $obj->from('patients as p', array('m_r_no'))
                ->join('users as u', 'p.user_id=u.id', array(new Zend_Db_Expr('CONCAT(first_name," ",last_name) as name'), 'id'))
                ->where('CONCAT(first_name," ",last_name) like \'%'.$tag.'%\' or m_r_no like \'%'.$tag.'%\' ')
                ;
        $q_result = $select->query()->fetchAll();
        $result = [];
        foreach($q_result as $row){
            $result[]=['key'=>$row['id'], 'value'=>'Name: '.$row['name'].' M.R.N.NO:'.$row['m_r_no']];
        }
        return $result;
        
        }
        /**
         * This method is used to return the patient information which is passed
         * by id
         * @param patient-id $id
         * @return patient-record
         */
     public function getById($id){
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $select = $obj->from('patients as p', array('m_r_no'))
                ->join('users as u', 'p.user_id=u.id', array('id', 'first_name', 'last_name', 'phone_number', 'address', 'birthday', 'sex' , 'email'))
                ->where('u.id = '.$id)
                ;
        $q_result = $select->query()->fetchAll();
        if(isset($q_result[0])){
            $q_result = $q_result[0];
        }
        return $q_result;
     }
     /**
      * This method is used to return true if the mrn.no. is not already assigned
      * to any other user except the current user
      * @param type $data
      * @return boolean
      */
     public function check_mrn($data){
         $id = empty($data['id'])?0:$data['id'];
         $m_r_n_no = empty($data['m_r_no'])?0:$data['m_r_no'];
         $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $result = $obj->from('patients as p', array('id'))
                ->where('user_id != ' .$id . ' and m_r_no = '.$m_r_n_no )
                ->query()->fetchAll();
        
        $obj_user = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $result_user = $obj_user->from('users as u', array('id'))
                ->where('id != ' .$id . ' and username = '.$m_r_n_no )
                ->query()->fetchAll();
        
         if(count($result) || count($result_user)){
             return false;
         }else{
             return true;
         }
     }
}
