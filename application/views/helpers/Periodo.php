<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PeriodoReserva
 *
 * @author Pablo
 */
class Zend_View_Helper_Periodo extends Zend_View_Helper_Abstract {

    public function periodo() {
        $auth = Zend_Auth::getInstance();
        $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

        if ($auth->hasIdentity() && $controller != "periodo" && $controller != "error") {
            $periodo = new Application_Model_Mappers_Periodo();
            
            if ($periodo->verificaFimPeriodo()) {
                $usuario = $auth->getIdentity();

                if ($usuario instanceof Application_Model_Administrador) {
                    $url = new Zend_View_Helper_Url();
                    return '<div class="container-aviso aviso-periodo"><div class="ui-widget">
                                <div style="padding: 0 .7em; width:90%; margin: 0 auto; margin-bottom: 2em" class="ui-state-default ui-corner-all">
                                    <p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span>
                                        <strong>Atenção:</strong> Caro(a) <i>' . $usuario->getNomeUsuario() . '</i>, é necessário definir um período de atividades.
                                        Se isso não for feito é impossível cadastrar as novas turmas, alunos ou fazer qualquer tipo de lançamento.
                                        Resolva isso clicando <b><a href="' . $url->url(array('controller' => 'periodo', 'action' => 'index')) . '">aqui</a></b>.</p>
                                </div>
                            </div></div>';
                }
            }
        }
        return '';
    }

}

?>
