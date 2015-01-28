<?php

class DatasAtividadesController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $periodo = new Application_Model_Mappers_Periodo();
        $usuario = Zend_Auth::getInstance()->getIdentity();
        $this->view->title = "Projeto Incluir - Calendário Letivo";

        if (!$periodo->verificaFimPeriodo()) {
            $periodo_atual = $periodo->getPeriodoAtual();
            $form_funcionamento = new Application_Form_FormDatasAtividades();
            $datas_atividade = new Application_Model_Mappers_DatasAtividade();

            $this->view->form = $form_funcionamento;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute($usuario->getUserIndex(), null, true);

                if ($form_funcionamento->verificaDatas($dados)) {
                    $datas_atividade->gerenciaDatasPeriodoAtual($dados, $periodo_atual);
                    $this->view->mensagem = "Datas inseridas/alteradas com sucesso";
                } else
                    $this->view->mensagem = "Datas inseridas com sucesso";
            }

            $form_funcionamento->reset();
            
            $calendario = $datas_atividade->getDatasByPeriodo($periodo_atual);
            $this->view->datas = $calendario->parseArray(true);
        } else
            $this->view->inativo = true;
    }

}
