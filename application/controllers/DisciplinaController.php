<?php

class DisciplinaController extends Zend_Controller_Action {

    public function init() {
        $this->view->controller = "disciplina";
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Gerenciar Disciplinas";

        $form_consulta = new Application_Form_FormConsultaDisciplina();
        $mapper_cursos = new Application_Model_Mappers_Curso();

        $form_consulta->initializeCursos($mapper_cursos->buscaCursos());
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
                $mapper_disciplina = new Application_Model_Mappers_Disciplina();

                if (isset($dados['id_curso']))
                    $dados['id_curso'] = (int) base64_decode($dados['id_curso']);

                $paginator = $mapper_disciplina->buscaDisciplinas($dados, true);
                $paginator->setItemCountPerPage(10);
                $paginator->setCurrentPageNumber($pagina);

                $this->view->resultado_busca = $paginator;
            }
        }
    }

    public function cadastrarAction() {
        $this->view->title = "Projeto Incluir - Cadastrar Disciplina";

        $form_cadastro = new Application_Form_FormDisciplina();
        $mapper_cursos = new Application_Model_Mappers_Curso();
        $mapper_disciplina = new Application_Model_Mappers_Disciplina();

        $form_cadastro->initializeCursos($mapper_cursos->buscaCursos(array('status' => Application_Model_Curso::status_ativo)));
        $this->view->form = $form_cadastro;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if (isset($dados['cancelar']))
                $this->_helper->redirector->goToRoute(array('controller' => 'disciplina', 'action' => 'index'), null, true);

            if ($form_cadastro->isValid($dados)) {
                $disciplina = new Application_Model_Disciplina(null, $form_cadastro->getValue('nome_disciplina'), $form_cadastro->getValue('ementa_disciplina'), new Application_Model_Curso(base64_decode($form_cadastro->getValue('id_curso'))), null, Application_Model_Disciplina::status_ativo);

                if (!empty($dados['pre_requisitos'])) {
                    foreach ($dados['pre_requisitos'] as $pre_requisito)
                        $disciplina->addPreRequisitos(new Application_Model_Disciplina(base64_decode($pre_requisito)));
                }

                if ($mapper_disciplina->addDisciplina($disciplina)) {
                    $form_cadastro->reset();
                    $this->view->mensagem = "Disciplina cadastrada com sucesso!";
                    return;
                } else
                    $this->view->mensagem = "A disciplina não foi cadastrada.<br/>Por favor, verifique se há alguma disciplina cadastrado com o nome especificado";
            }
            //busca disciplinas escolhidas pelo usuário
            $form_cadastro->populate($dados);
            if (!empty($dados['pre_requisitos']))
                $this->view->pre_requisitos = $mapper_disciplina->buscaDisciplinasByID($dados['pre_requisitos']);

            if (!empty($dados['id_curso']))
                $form_cadastro->initializeDisciplinas($mapper_disciplina->buscaDisciplinas(array('id_curso' => (int) base64_decode($dados['id_curso']))));
        }
    }

    public function alterarAction() {
        $id_disciplina = (int) base64_decode($this->getParam('disciplina'));

        if ($id_disciplina > 0) {
            $this->view->title = "Projeto Incluir - Alterar Disciplina";
            $form_alteracao = new Application_Form_FormDisciplina();

            $mapper_disciplina = new Application_Model_Mappers_Disciplina();
            $this->view->form = $form_alteracao;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'disciplina', 'action' => 'index'), null, true);

                if ($form_alteracao->isValid($dados)) {
                    $disciplina = new Application_Model_Disciplina(base64_decode($form_alteracao->getValue('id_disciplina')), $form_alteracao->getValue('nome_disciplina'), $form_alteracao->getValue('ementa_disciplina'), new Application_Model_Curso(base64_decode($form_alteracao->getValue('id_curso'))), null, Application_Model_Disciplina::status_ativo);

                    if (!empty($dados['pre_requisitos'])) {
                        foreach ($dados['pre_requisitos'] as $pre_requisito)
                            $disciplina->addPreRequisitos(new Application_Model_Disciplina(base64_decode($pre_requisito)));
                    }

                    if ($mapper_disciplina->alterarDisciplina($disciplina))
                        $this->view->mensagem = "Disciplina alterada com sucesso!";
                    else
                        $this->view->mensagem = "A disciplina não foi alterada.<br/>Por favor, verifique se há alguma disciplina cadastrado com o nome especificado";
                }
            }

            $disciplina = $mapper_disciplina->buscaDisciplinaByID($id_disciplina);

            if ($disciplina instanceof Application_Model_Disciplina && $disciplina->getStatus() == Application_Model_Disciplina::status_ativo) {
                $mapper_cursos = new Application_Model_Mappers_Curso();
                $form_alteracao->populate($disciplina->parseArray(true));

                $form_alteracao->initializeCursos($mapper_cursos->buscaCursos(array('status' => Application_Model_Curso::status_ativo)), $disciplina->getCurso()->getIdCurso(true));
                $form_alteracao->initializeDisciplinas($mapper_disciplina->buscaDisciplinas(array('id_curso' => $disciplina->getCurso()->getIdCurso()), null, $disciplina->getIdDisciplina()));

                $this->view->pre_requisitos = $disciplina->getPreRequisitos();
                $this->view->form = $form_alteracao;

                return;
            }
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function excluirAction() {
        /* $id_disciplina = (int) base64_decode($this->getParam('disciplina'));

          if ($id_disciplina > 0) {
          $this->view->title = "Projeto Incluir - Excluir Disciplina";
          $form_exclusao = new Application_Form_FormDisciplina();
          $form_exclusao->limpaValidadores();

          $mapper_disciplina = new Application_Model_Mappers_Disciplina();
          $this->view->form = $form_exclusao;

          if ($this->getRequest()->isPost()) {
          $dados = $this->getRequest()->getPost();

          if (isset($dados['cancelar']))
          $this->_helper->redirector->goToRoute(array('controller' => 'disciplina', 'action' => 'index'), null, true);

          if ($form_exclusao->isValid($dados)) {
          if ($mapper_disciplina->excluirDisciplina((int) base64_decode($form_exclusao->getValue('id_disciplina')))) {
          $form_exclusao->reset();
          $this->view->mensagem = "Disciplina excluída com sucesso!";
          } else
          $this->view->mensagem = "A disciplina não foi excluída. Por favor, tente novamente ou contate o administrador do sistema,<br/>";
          }
          }
          $disciplina = $mapper_disciplina->buscaDisciplinaByID($id_disciplina);

          if ($disciplina instanceof Application_Model_Disciplina) {
          $mapper_cursos = new Application_Model_Mappers_Curso();
          $form_exclusao->populate($disciplina->parseArray(true));

          $form_exclusao->initializeCursos($mapper_cursos->buscaCursos(), $disciplina->getCurso()->getIdCurso(true));
          $form_exclusao->initializeDisciplinas($mapper_disciplina->buscaDisciplinas(array('id_curso' => $disciplina->getCurso()->getIdCurso()), null, $disciplina->getIdDisciplina()));

          $this->view->pre_requisitos = $disciplina->getPreRequisitos();
          $this->view->form = $form_exclusao;
          } else
          $this->view->not_found = true;
          return;
          }
          $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true); */
    }

    public function buscarDisciplinasAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->_request->isPost()) {
                $id_curso = (int) base64_decode($this->getRequest()->getParam('id_curso'));

                if ($id_curso > 0) {
                    $id_exclude = null;

                    if (!is_null($this->getRequest()->getParam('id_disciplina_exclude')))
                        $id_exclude = base64_decode($this->getRequest()->getParam('id_disciplina_exclude'));

                    $mapper_disciplina = new Application_Model_Mappers_Disciplina();
                    $disciplinas = $mapper_disciplina->buscaDisciplinas(array('id_curso' => $id_curso, 'status' => Application_Model_Disciplina::status_ativo), null, $id_exclude);

                    $array_disciplinas = array();

                    if (!empty($disciplinas)) {
                        $i = 0;
                        foreach ($disciplinas as $disciplina) {
                            $array_disciplinas[$i]['id_disciplina'] = $disciplina->getIdDisciplina(true);
                            $array_disciplinas[$i]['nome_disciplina'] = $disciplina->getNomeDisciplina();
                            $i++;
                        }
                    }
                    echo json_encode($array_disciplinas);
                    return;
                }
            }
            echo json_encode(null);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            echo json_encode(null);
        }
    }

    public function cancelarAction() {
        $id_disciplina = (int) base64_decode($this->getParam('disciplina'));

        if ($id_disciplina > 0) {
            $this->view->title = "Projeto Incluir - Cancelar Disciplina";

            $form_confirmacao = new Application_Form_FormConfirmacao();
            $mapper_disciplina = new Application_Model_Mappers_Disciplina();

            $this->view->form = $form_confirmacao;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'disciplina', 'action' => 'index'), null, true);

                if ($form_confirmacao->isValid($dados)) {
                    $mapper_turma = new Application_Model_Mappers_Turma();
                    $id_disciplina = (int) base64_decode($form_confirmacao->getValue('id'));

                    if ($mapper_disciplina->cancelarDisciplina($id_disciplina) && $mapper_turma->cancelarTurmasByDisciplinas(array($id_disciplina)))
                        $this->view->mensagem = "Disciplina cancelada com sucesso!";
                    else
                        $this->view->mensagem = "A disciplina não foi cancelada. Por favor, tente novamente ou contate o administrador do sistema,<br/>";
                }
            }

            $disciplina = $mapper_disciplina->buscaDisciplinaByID($id_disciplina);

            if ($disciplina instanceof Application_Model_Disciplina && $disciplina->getStatus() == Application_Model_Disciplina::status_ativo) {
                $form_confirmacao->populate(array('id' => $disciplina->getIdDisciplina(true)));
                $this->view->disciplina = $disciplina;
            }

            return;
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function ativarAction() {
        $this->view->title = "Projeto Incluir - Ativação de Disciplina";

        $id_disciplina = (int) base64_decode($this->getParam('disciplina'));

        if ($id_disciplina > 0) {
            $form_ativacao = new Application_Form_FormConfirmacao();
            $mapper_disciplina = new Application_Model_Mappers_Disciplina();

            $this->view->form = $form_ativacao;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();
                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'disciplina', 'action' => 'index'), null, true);

                if ($form_ativacao->isValid($dados)) {
                    if ($mapper_disciplina->ativarDisciplina((int) base64_decode($form_ativacao->getValue('id'))))
                        $this->view->mensagem = "A disciplina foi restaurada com sucesso.";
                    else
                        $this->view->mensagem = "A disciplina não foi restaurada, por favor consulte o administrador do sistema para mais informações.";
                }
            } 
            else {
                $disciplina = $mapper_disciplina->buscaDisciplinaByID($id_disciplina);

                if ($disciplina instanceof Application_Model_Disciplina && $disciplina->getStatus() == Application_Model_Disciplina::status_inativo) {
                    $form_ativacao->populate(array('id' => $disciplina->getIdDisciplina(true)));
                    $this->view->disciplina = $disciplina;
                }
            }
            return;
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

}
