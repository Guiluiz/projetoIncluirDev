<?php

class CursoController extends Zend_Controller_Action {

    public function init() {
        $this->view->controller = "curso";
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Gerenciar Cursos";

        $form_consulta = new Application_Form_FormConsultaCurso();
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
                $mapper_curso = new Application_Model_Mappers_Curso();
                $paginator = $mapper_curso->buscaCursos($form_consulta->getValues(), true);
                $paginator->setItemCountPerPage(10);
                $paginator->setCurrentPageNumber($pagina);
                $this->view->resultado_busca = $paginator;
            }
        } else
            $form_consulta->populate($dados);
    }

    public function cadastrarAction() {
        $this->view->title = "Projeto Incluir - Cadastrar Curso";
        $form_cadastro = new Application_Form_FormCurso();
        $this->view->form = $form_cadastro;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if (isset($dados['cancelar']))
                $this->_helper->redirector->goToRoute(array('controller' => 'curso', 'action' => 'index'), null, true);

            if ($form_cadastro->isValid($dados)) {
                $mapper_curso = new Application_Model_Mappers_Curso();

                if ($mapper_curso->addCurso(new Application_Model_Curso(null, $form_cadastro->getValue('nome_curso'), $form_cadastro->getValue('descricao_curso')))) {
                    $form_cadastro->reset();
                    $this->view->mensagem = "Curso cadastrado com sucesso!";
                } else
                    $this->view->mensagem = "O curso não foi cadastrado.<br/>Por favor, verifique se há algum curso cadastrado com o nome especificado";
            } else
                $form_cadastro->populate($dados);
        }
    }

    public function alterarAction() {
        $id_curso = (int) base64_decode($this->getParam('curso'));

        if ($id_curso > 0) {
            $this->view->title = "Projeto Incluir - Alterar Curso";
            $form_alteracao = new Application_Form_FormCurso();

            $mapper_curso = new Application_Model_Mappers_Curso();
            $this->view->form = $form_alteracao;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'curso', 'action' => 'index'), null, true);

                if ($form_alteracao->isValid($dados)) {
                    if ($mapper_curso->alterarCurso(new Application_Model_Curso((int) base64_decode($form_alteracao->getValue('id_curso')), $form_alteracao->getValue('nome_curso'), $form_alteracao->getValue('descricao_curso'))))
                        $this->view->mensagem = "Curso alterado com sucesso!";
                    else
                        $this->view->mensagem = "O curso não foi alterado.<br/>Por favor, verifique se há algum curso cadastrado com o nome especificado";
                }
            }

            $curso = $mapper_curso->buscaCursoByID($id_curso);

            if ($curso instanceof Application_Model_Curso) {
                $form_alteracao->populate($curso->parseArray(true));
                return;
            }
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function excluirAction() {
        $id_curso = (int) base64_decode($this->getParam('curso'));

        if ($id_curso > 0) {
            $this->view->title = "Projeto Incluir - Excluir Curso";
            $form_exclusao = new Application_Form_FormCurso();
            $form_exclusao->limpaValidadores();

            $mapper_curso = new Application_Model_Mappers_Curso();
            $this->view->form = $form_exclusao;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'curso', 'action' => 'index'), null, true);

                if ($form_exclusao->isValid($dados)) {
                    if ($mapper_curso->excluirCurso((int) base64_decode($form_exclusao->getValue('id_curso')))) {
                        $form_exclusao->reset();
                        $this->view->mensagem = "Curso excluído com sucesso!";
                    } else
                        $this->view->mensagem = "O curso não foi excluído. Por favor, tente novamente ou contate o administrador do sistema.<br/>";
                }
            }
            
            $curso = $mapper_curso->buscaCursoByID($id_curso);
            
            if ($curso instanceof Application_Model_Curso)
                $form_exclusao->populate($curso->parseArray(true));
            else
                $this->view->not_found = true;
            return;
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

}
