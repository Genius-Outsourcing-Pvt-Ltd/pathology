<?php

class Application_Model_Test extends Application_Model_DbTable_Test {

    public function init() {
        parent::init();        
    }
/**
 * Lists all tests which are available in system
 * @return dataset
 */
    public function getAll() {
        $obj = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $select = $obj->from('tests as t', array('id', 'name'))
                    ->order('t.name');
        $result = $select->query()->fetchAll();
        return $result;
    }

   

}
