<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initPlugins() {
        $this->bootstrap('db');
        $this->bootstrap("frontController");
        $this->frontController->registerPlugin(new Aplicacao_Plugin_ControleNavigation());
        $this->frontController->registerPlugin(new Aplicacao_Plugin_Periodo());
    }

    protected function _initAcl() {
        $aclSetup = new Aplicacao_Acl_Setup();
    }
}
