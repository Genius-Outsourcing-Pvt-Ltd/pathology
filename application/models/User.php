<?php

class Application_Model_User extends Application_Model_DbTable_User
{

    public function init() {
        parent::init();        
    }

    /**
     * This method logs in the user
     * Member login
     * @author Kashif Irshad
     * @param string $userName
     * @param string $password in md5 encryption
     * @return string return 'success' for successfully login and all other messages are error message 
     */
    public function login($userName, $password, $remember) {
        $userTable = new Application_Model_DbTable_User();

        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
        $authAdapter->setTableName('users');
        $authAdapter->setIdentityColumn('username');
        $authAdapter->setCredentialColumn('password');
        $authAdapter->setIdentity($userName);
        $authAdapter->setCredential($password);
        $authAdapter->setAmbiguityIdentity(true);

        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('user'));

        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()) {
                if ($remember > 0) {
                    $oneMinute = 60;
                    $oneHour = $oneMinute * 60;
                    $oneDay = $oneHour * 24;
                    $oneWeek = $oneDay * 7;
                    $oneMonth = $oneDay * 30;
                    Zend_Session::rememberMe($oneWeek);
                }

                return 'success';
            
        } else {
            $userRow = $userTable->fetchRow("username='$userName'");
            if (isset($userRow)) {
                return 'Invalid password';
            } else {
                return 'Invalid username or password';
            }
        }
    }
}

