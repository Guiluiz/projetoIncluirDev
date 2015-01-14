<?php

class PeriodoController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {
        $usuario = Zend_Auth::getInstance()->getIdentity();
        
        $this->view->title = "Projeto Incluir - Período de Atividades";
        $form_periodo = new Application_Form_FormPeriodo();

        $periodo_atual = new Application_Model_Periodo();
        $form_periodo->populate($periodo_atual->parseArray());

        $this->view->form = $form_periodo;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if (isset($dados['cancelar']))
                $this->_helper->redirector->goToRoute($usuario->getUserIndex(), null, true);

            if ($form_periodo->isValid($dados)) {
                if ($periodo_atual->gerenciaPeriodo($form_periodo->getValues()))
                    $this->view->mensagem = "O período foi alterado com sucesso!";
                else
                    $this->view->mensagem = "Houve algum problema, o período não foi alterado. Por favor, procure o administrador do sistema.";
            } else
                $form_periodo->populate($periodo_atual->parseArray());
        }
    }

}
