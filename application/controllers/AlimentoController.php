<?php

class AlimentoController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Gerenciar Alimentos";

        $form_consulta = new Application_Form_FormConsultaAlimento();
        $this->view->form = $form_consulta;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();
            $pagina = 1;
        } else {
            $dados = $this->getRequest()->getParams();
            $pagina = $this->_getParam('pagina');
        }

        if ($form_consulta->isValid($dados)) {
            if ($this->getRequest()->isPost() || !empty($pagina)) {
                $mapper_alimento = new Application_Model_Mappers_Alimento();
                $paginator = $mapper_alimento->buscaAlimentos($form_consulta->getValues(), true);

                $paginator->setItemCountPerPage(10);
                $paginator->setCurrentPageNumber($pagina);
                $this->view->resultado_busca = $paginator;
            }
        } else
            $form_consulta->populate($dados);
    }

    public function cadastrarAction() {
        $this->view->title = "Projeto Incluir - Cadastrar Alimento";

        $form_cadastro = new Application_Form_FormAlimento();
        $this->view->form = $form_cadastro;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if (isset($dados['cancelar']))
                $this->_helper->redirector->goToRoute(array('controller' => 'alimento', 'action' => 'index'), null, true);

            if ($form_cadastro->isValid($dados)) {
                $mapper_alimento = new Application_Model_Mappers_Alimento();

                if ($mapper_alimento->addAlimento(new Application_Model_Alimento(null, $form_cadastro->getValue('nome_alimento')))) {
                    $form_cadastro->reset();
                    $this->view->mensagem = "Alimento cadastrado com sucesso!";
                } 
                
                else
                    $this->view->mensagem = "O alimento não foi cadastrado.<br/>Por favor, verifique se há algum outro cadastrado com o nome especificado";
            } else
                $form_cadastro->populate($dados);
        }
    }

    public function alterarAction() {
        $id_alimento = (int) base64_decode($this->getParam('alimento'));

        if ($id_alimento > 0) {
            $this->view->title = "Projeto Incluir - Alterar Alimento";

            $form_alteracao = new Application_Form_FormAlimento();
            $mapper_alimento = new Application_Model_Mappers_Alimento();

            $this->view->form = $form_alteracao;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'curso', 'action' => 'index'), null, true);

                if ($form_alteracao->isValid($dados)) {
                    if ($mapper_alimento->alterarAlimento(new Application_Model_Alimento((int) base64_decode($form_alteracao->getValue('id_alimento')), $form_alteracao->getValue('nome_alimento'))))
                        $this->view->mensagem = "Alimento alterado com sucesso!";
                    else
                        $this->view->mensagem = "O alimento não foi alterado.<br/>Por favor, verifique se há algum alimento cadastrado com o nome especificado";
                }
            }

            $alimento = $mapper_alimento->buscaAlimentoByID($id_alimento);

            if ($alimento instanceof Application_Model_Alimento) {
                $form_alteracao->populate($alimento->parseArray(true));
                return;
            }
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function excluirAction() {
        $id_alimento = (int) base64_decode($this->getParam('alimento'));

        if ($id_alimento > 0) {
            $this->view->title = "Projeto Incluir - Excluir Alimento";

            $form_exclusao = new Application_Form_FormAlimento();
            $form_exclusao->limpaValidadores();

            $mapper_alimento = new Application_Model_Mappers_Alimento();
            $this->view->form = $form_exclusao;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'alimento', 'action' => 'index'), null, true);

                if ($form_exclusao->isValid($dados)) {
                    if ($mapper_alimento->excluirAlimento((int) base64_decode($form_exclusao->getValue('id_alimento')))) {
                        $form_exclusao->reset();
                        $this->view->mensagem = "Alimento excluído com sucesso!";
                    } else
                        $this->view->mensagem = "O alimento não foi excluído. Por favor, tente novamente ou contate o administrador do sistema.<br/>";
                }
            }
            $alimento = $mapper_alimento->buscaAlimentoByID($id_alimento);

            if ($alimento instanceof Application_Model_Alimento)
                $form_exclusao->populate($alimento->parseArray(true));
            else
                $this->view->not_found = true;
            return;
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function buscarAlimentosAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->_request->isPost()) {
                $mapper_alimento = new Application_Model_Mappers_Alimento();
                $alimentos = $mapper_alimento->buscaAlimentos();

                $array_alimentos = array();

                if (!empty($alimentos)) {
                    $i = 0;
                    foreach ($alimentos as $alimento) {
                        $array_alimentos[$i]['id_alimento'] = $alimento->getIdAlimento(true);
                        $array_alimentos[$i]['nome_alimento'] = $alimento->getNomeAlimento();
                        $i++;
                    }
                }
                echo json_encode($array_alimentos);
                return;
            }
            echo json_encode(null);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            echo json_encode(null);
        }
    }

}
