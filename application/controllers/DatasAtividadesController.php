<?php

class DatasAtividadesController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $periodo = new Application_Model_Periodo();
        $usuario = Zend_Auth::getInstance()->getIdentity();

        if (!$periodo->verificaFimPeriodo()) {
            $this->view->title = "Projeto Incluir - Calendário Letivo";

            $form_funcionamento = new Application_Form_FormDatasAtividades();
            $datas_atividade = new Application_Model_DatasAtividade();

            $this->view->form = $form_funcionamento;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute($usuario->getUserIndex(), null, true);

                if ($form_funcionamento->verificaDatas($dados)) {
                    $datas_atividade->gerenciaDatas($dados);
                    $this->view->mensagem = "Datas inseridas com sucesso";
                }
            }
            $form_funcionamento->reset();
            $this->view->datas = $datas_atividade->parseArray(true);
        } else
            $this->view->inativo = true;
    }

}
