<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        $user = new Application_Model_DbTable_User();
        $data = array(
            'created_at'      => '2007-03-22',
            'username' => 'testname'
        );
       $users = $user->insert($data);
 
    }


}

