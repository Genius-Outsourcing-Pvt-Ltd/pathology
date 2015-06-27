<?php

class Login_LoginController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('login');
        $forms = Zend_Registry::get('forms');
        $form = new Zend_Form($forms->user->login);
        $userManagement = new Application_Model_User();
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('user'));
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            
            $error = array();
            if ($form->isValid($data)) {
                    $userName = $form->username->getValue();
                    $password = $form->password->getValue();
                    $remember = $this->_request->getParam('remember', 0);

                    $userTable = new Application_Model_DbTable_User();
                    $userExits = $userTable->fetchRow('username = "' . $userName . '" AND password= "' . md5($password) . '" AND deleted_at IS NULL');
                    $magUser = false;
                    if (!empty($userExits)) {
                        $userExits = $userExits->toArray();
                        if ($userExits['id'] == 0 || $userExits['id'] == '') {
                            $magUser = true;
                        }
                    }

                    if ($magUser) {
                        $form->username->setErrors(array(
                             'Invalid username or password'
                        ));
                    } else {
                        $response = $userManagement->login($userName, md5($password), $remember);
                    }
                    if ($response == 'success') {
                       $this->_redirect('dashboard');
                    } else {
                        $form->username->setErrors(array(
                            'Invalid username or password'
                        ));
                    }
            } 
        }
        $this->view->form = $form;
    }

    public function logoutAction() {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('user'));
        if ($auth->hasIdentity()) {
            $auth->clearIdentity();
            Zend_Session::forgetMe();
        }
        Zend_Session::destroy();
        $this->_redirect('login/login/index');
    }

    public function takedbbackupAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $abs_path = getcwd();
//        $this->authSendEmail(); die('End');
        //ENTER THE RELEVANT INFO BELOW
        $mysqlDatabaseName = 'sparks_mindspark';
        $mysqlUserName = 'sparks_mindspark';
        $mysqlPassword = 'mindspark';
        $mysqlHostName = 'localhost';
		$fileName = 'backup_' . time() . '.sql';
		//$backupPath = APPLICATION_PATH . '/../public/databases_backups/'.$fileName;
		$backupPath ='public_html/databases_backups/'.$fileName;
        $mysqlExportPath = $backupPath;

        //DO NOT EDIT BELOW THIS LINE
        //Export the database and output the status to the page
        $command = 'mysqldump --opt -h' . $mysqlHostName . ' -u' . $mysqlUserName . ' -p' . $mysqlPassword . ' ' . $mysqlDatabaseName . ' > ~/' . $mysqlExportPath;
        exec($command, $output = array(), $worked);
        switch ($worked) {
            case 0:
				$remote_file = 'database_backup/'.$fileName;
				$conn_id = ftp_connect('ftp.backup.mindsparks.co.nz');
				$login_result = ftp_login($conn_id, 'uploads@backup.mindsparks.co.nz', '^_Ke8[{Oo9]y');
				$toUploadOnFtpPath = APPLICATION_PATH . '/../public_html/databases_backups/'.$fileName;
				// upload a file
					if (ftp_put($conn_id, $remote_file, $toUploadOnFtpPath, FTP_ASCII)) {
						echo "successfully uploaded $backupPath <br/>";
					} else {
						echo "There was a problem while uploading $backupPath\n";
					}
				// close the connection
				ftp_close($conn_id);
                echo 'Database <b>' . $mysqlDatabaseName . '</b> successfully exported to <b>~/' . $mysqlExportPath . '</b>';
                break;
            case 1:
                echo 'There was a warning during the export of <b>' . $mysqlDatabaseName . '</b> to <b>~/' . $mysqlExportPath . '</b>';
                break;
            case 2:
                echo 'There was an error during export. Please check your values:<br/><br/><table><tr><td>MySQL Database Name:</td><td><b>' . $mysqlDatabaseName . '</b></td></tr><tr><td>MySQL User Name:</td><td><b>' . $mysqlUserName . '</b></td></tr><tr><td>MySQL Password:</td><td><b>NOTSHOWN</b></td></tr><tr><td>MySQL Host Name:</td><td><b>' . $mysqlHostName . '</b></td></tr></table>';
                break;
        }
    }

    public function authSendEmail() {
        $config = array(
            'auth' => 'login',
            'username' => 'virtualvs2@gmail.com',
            'password' => 'svs123SVS',
            'ssl' => 'tls',
            'port' => 25);

        $transport = new Zend_Mail_Transport_Smtp('smtp.googlemail.com', $config);
        $mail = new Zend_Mail();
        $mail->setBodyText('This is the text of the mail.');
        $mail->setFrom('virtualvs2@gmail.com', 'Some Sender');
        $mail->addTo('medeveloper2@gmail.com', 'Some Recipient');
        $mail->setSubject('TestSubject');

        $content = file_get_contents(APPLICATION_PATH . '/../public/databases_backups/backup_1411486294.sql'); // e.g. ("attachment/abc.pdf")
        $attachment = new Zend_Mime_Part($content);
        $attachment->type = 'text/plain; charset=UTF-8';
        $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = Zend_Mime::ENCODING_BASE64;
        $attachment->filename = 'backup_1411486294.sql'; // name of file
        $mail->addAttachment($attachment);

        $mail->send($transport);
    }

}

//$('.drag').each(function() {
//    var id = parseInt($(this).offset().top, 10);
//    var cTop = parseInt($(this).height(), 10)
//    var nextHeight = parseInt($(this).next('div.drag').height(), 10)
//    var nextLeft = parseInt($(this).offset().left, 10);
//    if(nextLeft < 320){
//      $(this).next('div.drag').css('top',id+cTop)
//    }else{
//      
//    }
//  
//    //alert('cTop=>'+id+',nTop=>'+nextTop+',nHeight=>'+nextHeight+',nLeft=>'+nextLeft) 
//   // $(this).next('div.drag').css('top',id+nextHeight)                             
// });

