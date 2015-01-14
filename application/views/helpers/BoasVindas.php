<?php

/**
 * Helper para exibição de mensagem de boas vindas
 *
 * @author Pablo Augusto
 */
class Zend_View_Helper_BoasVindas extends Zend_View_Helper_Abstract {

    public function boasVindas() {
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            $usuario = $auth->getIdentity();
            
            if ($usuario instanceof Application_Model_Administrador)
                return "Bem vindo, Administrador(a) <b>" . $usuario->getNomeUsuario() . '</b>';

            elseif ($usuario instanceof Application_Model_Professor)
                return "<p id='inf-usuario'>Bem vindo, <b>" . $usuario->getNomeUsuario() . '</b>';
        }
        return "";
    }

}

?>
