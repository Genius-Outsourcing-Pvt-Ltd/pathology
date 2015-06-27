<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public static function _initSet() {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        Zend_Registry::set('config', $config);

        $forms = new Zend_Config_Ini(APPLICATION_PATH . '/configs/forms.ini', 'production');
        Zend_Registry::set('forms', $forms);

        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();


            $routeConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini');
            $router->addConfig($routeConfig, 'routes');
    }

}
