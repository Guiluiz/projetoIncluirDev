<?php

/**
 * Classe para controle dos breadcrumb e menus dos usuários
 *
 * @author Projeto Incluir
 */
class Aplicacao_Plugin_ControleNavigation extends Zend_Controller_Plugin_Abstract {

    protected $_auth = null;
    protected $view_renderer = null;

    public function __construct() {
        $this->_auth = Zend_Auth::getInstance();
        $this->view_renderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $this->view_renderer->initView();
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $arq_menu = "menu_default.ini";
        $arq_breadcrumb = "breadcrumb_default.ini";

        if ($this->_auth->hasIdentity()) {
            $usuario = $this->_auth->getIdentity();
            $role = $usuario->getRoleId();

            if (file_exists(APPLICATION_PATH . '/configs/menu_' . strtolower($role) . '.ini'))
                $arq_menu = 'menu_' . strtolower($role) . '.ini';
            
            if (file_exists(APPLICATION_PATH . '/configs/breadcrumb_' . strtolower($role) . '.ini'))
                $arq_breadcrumb = 'breadcrumb_' . strtolower($role) . '.ini';
            
        }

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/' . $arq_menu);
        $navigation = new Zend_Navigation($config);
        $this->view_renderer->view->navigation($navigation);

        $config_breadcrumb = new Zend_Config_Ini(APPLICATION_PATH . '/configs/' .$arq_breadcrumb);
        $breadcrumb = new Zend_Navigation($config_breadcrumb);

        Zend_Registry::set('breadcrumb', $breadcrumb);
    }

}

?>
