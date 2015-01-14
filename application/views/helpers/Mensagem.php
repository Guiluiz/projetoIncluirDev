<?php

/**
 * Helper para exibir mensagem de status da tarefa, se foi realizada com sucesso
 * ou se houve falhas
 */
class Zend_View_Helper_Mensagem extends Zend_View_Helper_Abstract {

    public function mensagem($controller, $mensagem, $action) {
        if (!empty($mensagem)) {
            if (empty($action))
                $action = 'index';

            $url = new Zend_View_Helper_Url();
            $mensagem .= "<br/><a href='" . $url->url(array(
                        'controller' => $controller,
                        'action' => $action), null, true)
                    . "'>Voltar</a>";

            return $mensagem;
        }
    }

}

?>
