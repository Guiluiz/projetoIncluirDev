<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initPlugins() {
        $this->bootstrap("frontController");
        $this->frontController->registerPlugin(new Aplicacao_Plugin_ControleNavigation());
    }

    protected function _initAcl() {
        $aclSetup = new Aplicacao_Acl_Setup();
    }

    protected function _initPeriodo() {
        //$this->bootstrap('db');
        //$periodo = new Application_Model_Periodo();
    }

}
