<?php

class PeriodoController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {
        $usuario = Zend_Auth::getInstance()->getIdentity();

        $this->view->title = "Projeto Incluir - Período de Atividades";
        $form_periodo = new Application_Form_FormPeriodo();
        $periodo = new Application_Model_Mappers_Periodo();

        $this->view->form = $form_periodo;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if (isset($dados['cancelar']))
                $this->_helper->redirector->goToRoute($usuario->getUserIndex(), null, true);

            if ($form_periodo->isValid($dados)) {
                $obj_periodo = new Application_Model_Periodo((int) base64_decode($form_periodo->getValue('id_periodo')), true, $form_periodo->getValue('nome_periodo'), $form_periodo->getValue('data_inicio'), $form_periodo->getValue('data_termino'), $form_periodo->getValue('valor_liberacao_periodo'), $form_periodo->getValue('freq_min_aprov'), $form_periodo->getValue('total_pts_periodo'), $form_periodo->getValue('min_pts_aprov'), $form_periodo->getValue('quantidade_alimentos'));

                if ($periodo->gerenciaPeriodo($obj_periodo, new Application_Model_Mappers_DatasAtividade()))
                    $this->view->mensagem = "O período foi incluído/alterado com sucesso!";
                else
                    $this->view->mensagem = "Houve algum problema, o período não foi alterado. Por favor, verifique se o período incluído não interfere em outros, ou procure o administrador do sistema.";
            }
        }

        if (!$periodo->verificaFimPeriodo()) {
            $periodo_atual = $periodo->getPeriodoAtual();
            $form_periodo->populate($periodo_atual->parseArray(true));
        } else
            $this->view->novo_periodo = true;
    }

    public function configuraFimPeriodoAction() {
        $this->view->title = "Projeto Incluir - Configurar Fim do Período";

        $form_fim_periodo = new Application_Form_FormConfigFimPeriodo();
        $this->view->form = $form_fim_periodo;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if ($form_fim_periodo->isValid($dados)) {
                $mapper_periodo = new Application_Model_Mappers_Periodo();

                if ($mapper_periodo->adiarFimPeriodo((int)base64_decode($form_fim_periodo->getValue('opcoes_adiamento'))))
                    $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'index'), null, true);
                else
                    $this->view->mensagem = 'Houve algum problema, contate o administrador do sistema';
            } else
                $form_fim_periodo->populate($dados);
        }
    }

}
