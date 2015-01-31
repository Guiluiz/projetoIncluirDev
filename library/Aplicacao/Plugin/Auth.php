<?php

/**
 * Classe que controla o acesso dos usuários de acordo com seus privilégios
 * @author Projeto Incluir
 */
class Aplicacao_Plugin_Auth extends Zend_Controller_Plugin_Abstract {

    protected $_auth = null;
    protected $_acl = null;
    protected $_notLoggedRoute = array(
        'controller' => 'login',
        'action' => 'index'
    );
    protected $_forbiddenRoute = array(
        'controller' => 'error',
        'action' => 'error'
    );

    public function __construct() {
        $this->_auth = Zend_Auth::getInstance();
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        if (!$this->_auth->hasIdentity()) {
            if ($controller != "login") {
                $controller = $this->_notLoggedRoute['controller'];
                $action = $this->_notLoggedRoute['action'];
            }
        } 
        
        elseif (!$this->_isAuthorized($request->getControllerName(), $request->getActionName())) {
            $controller = $this->_forbiddenRoute['controller'];
            $action = $this->_forbiddenRoute['action'];
        }

        $request->setControllerName($controller);
        $request->setActionName($action);
    }

    protected function _isAuthorized($controller, $action) {
        $this->_acl = Zend_Registry::get('acl');
        $user = $this->_auth->getIdentity();

        if (!$this->_acl->has($controller) || !$this->_acl->isAllowed($user, $controller, $action))
            return false;
        return true;
    }

}

?>
