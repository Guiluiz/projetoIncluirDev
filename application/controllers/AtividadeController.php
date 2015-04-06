<?php

class AtividadeController extends Zend_Controller_Action {

    public function init() {
        $calendario = new Application_Model_Mappers_DatasAtividade();
        $periodo = new Application_Model_Mappers_Periodo();

        $datas_atividade = $calendario->getDatasByPeriodo($periodo->getPeriodoAtual());
        $this->view->datas_atividade = json_encode($datas_atividade->parseArray(true));
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Gerenciar Atividades";

        $periodo = new Application_Model_Mappers_Periodo();
        $form_consulta = new Application_Form_FormConsultaAtividade();
        $mapper_turma = new Application_Model_Mappers_Turma();

        $form_consulta->initializeTurmas($mapper_turma->buscaTurmasSimples());
        $this->view->form = $form_consulta;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();
            $pagina = 1;
        } else {
            $dados = $this->getRequest()->getParams();
            $pagina = $this->_getParam('pagina');
        }

        if ($periodo->verificaFimPeriodo())
            $this->view->inativo = true;

        if ($form_consulta->isValid($dados)) {
            if ($this->getRequest()->isPost() || !empty($pagina)) {
                $mapper_atividades = new Application_Model_Mappers_Atividade();
                $paginator = $mapper_atividades->buscaAtividadesTurma((int) base64_decode($form_consulta->getValue('turma')), true);

                $paginator->setItemCountPerPage(10);
                $paginator->setCurrentPageNumber($pagina);
                $this->view->resultado_busca = $paginator;
            }
        } else
            $form_consulta->populate($dados);
    }

    public function cadastrarAction() {
        $this->view->title = "Projeto Incluir - Cadastrar Atividade";

        $periodo = new Application_Model_Mappers_Periodo();

        if (!$periodo->verificaFimPeriodo()) {
            $form_atividade = new Application_Form_FormAtividade();
            $mapper_curso = new Application_Model_Mappers_Curso();

            $form_atividade->initializeCursos($mapper_curso->buscaCursos());
            $this->view->form = $form_atividade;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'atividade', 'action' => 'index'), null, true);

                if ($form_atividade->isValid($dados)) {
                    $mapper_atividade = new Application_Model_Mappers_Atividade();

                    if ($mapper_atividade->addAtividade(new Application_Model_Atividade(null, new Application_Model_Turma((int) base64_decode($form_atividade->getValue('turma'))), $form_atividade->getValue('nome'), $form_atividade->getValue('valor_total'), $form_atividade->getValue('descricao'), $form_atividade->getValue('data_funcionamento')))) {
                        $form_atividade->reset();
                        $this->view->mensagem = 'Atividade cadastrada com sucesso.';
                    } else
                        $this->view->mensagem = 'A atividade não foi cadastrada. Verique se soma do total de pontos não está passando do valor estipulado para o semestre.';
                } else
                    $form_atividade->populate($dados);
            }
        }
    }

    public function alterarAction() {
        $this->view->title = "Projeto Incluir - Alterar Atividade";
        $periodo = new Application_Model_Mappers_Periodo();

        if (!$periodo->verificaFimPeriodo()) {
            $id_atividade = (int) base64_decode($this->getParam('atividade'));

            if ($id_atividade > 0) {
                $mapper_atividade = new Application_Model_Mappers_Atividade();
                $form_atividade = new Application_Form_FormAtividade();

                $this->view->form = $form_atividade;

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();

                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'atividade', 'action' => 'index'), null, true);

                    if ($form_atividade->isValid($dados)) {
                        $mapper_atividade = new Application_Model_Mappers_Atividade();

                        if ($mapper_atividade->alterarAtividade(new Application_Model_Atividade((int) base64_decode($form_atividade->getValue('id_atividade')), new Application_Model_Turma((int) base64_decode($form_atividade->getValue('turma'))), $form_atividade->getValue('nome'), $form_atividade->getValue('valor_total'), $form_atividade->getValue('descricao'), $form_atividade->getValue('data_funcionamento')))) {
                            $form_atividade->reset();
                            $this->view->mensagem = 'Atividade alterada com sucesso.';
                        } else
                            $this->view->mensagem = 'A atividade não foi alterada. Verique se soma do total de pontos não está passando do valor estipulado para o semestre.';
                    } else
                        $form_atividade->populate($dados);
                }

                $atividade = $mapper_atividade->buscaAtividadeByID($id_atividade);

                if ($atividade instanceof Application_Model_Atividade && $atividade->getTurma()->getPeriodo()->isPeriodoAtual()) {
                    $mapper_curso = new Application_Model_Mappers_Curso();
                    $mapper_disciplina = new Application_Model_Mappers_Disciplina();
                    $mapper_turma = new Application_Model_Mappers_Turma();

                    $form_atividade->populate($atividade->parseArray(true));
                    $form_atividade->initializeCursos($mapper_curso->buscaCursos(), $atividade->getTurma()->getDisciplina()->getCurso()->getIdCurso(true));
                    $form_atividade->initializeDisciplinas($mapper_disciplina->buscaDisciplinas(array('id_curso' => $atividade->getTurma()->getDisciplina()->getCurso()->getIdCurso()), null), $atividade->getTurma()->getDisciplina()->getIdDisciplina(true));
                    $form_atividade->initializeTurmas($mapper_turma->buscaTurmas(array('disciplina' => $atividade->getTurma()->getDisciplina()->getIdDisciplina(true)), null), $atividade->getTurma()->getIdTurma(true));

                    return;
                }
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        }
    }

    public function excluirAction() {
        $periodo = new Application_Model_Mappers_Periodo();
        $this->view->title = "Projeto Incluir - Excluir Atividade";

        if (!$periodo->verificaFimPeriodo()) {
            $id_atividade = (int) base64_decode($this->getParam('atividade'));

            if ($id_atividade > 0) {

                $mapper_atividade = new Application_Model_Mappers_Atividade();
                $form_atividade = new Application_Form_FormAtividade();
                $form_atividade->limpaValidadores();

                $this->view->form = $form_atividade;

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();

                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'atividade', 'action' => 'index'), null, true);

                    if ($form_atividade->isValid($dados)) {
                        $mapper_atividade = new Application_Model_Mappers_Atividade();

                        if ($mapper_atividade->excluirAtividade(new Application_Model_Atividade((int) base64_decode($form_atividade->getValue('id_atividade'))))) {
                            $form_atividade->reset();
                            $this->view->mensagem = 'Atividade excluída com sucesso.';
                        } else
                            $this->view->mensagem = 'A atividade não foi excluída. Consulte o administrador do sistema.';
                    } else
                        $form_atividade->populate($dados);
                }

                $atividade = $mapper_atividade->buscaAtividadeByID($id_atividade);

                if ($atividade instanceof Application_Model_Atividade && $atividade->getTurma()->getPeriodo()->isPeriodoAtual()) {
                    $mapper_curso = new Application_Model_Mappers_Curso();
                    $mapper_disciplina = new Application_Model_Mappers_Disciplina();
                    $mapper_turma = new Application_Model_Mappers_Turma();

                    $form_atividade->populate($atividade->parseArray(true));
                    $form_atividade->initializeCursos($mapper_curso->buscaCursos(), $atividade->getTurma()->getDisciplina()->getCurso()->getIdCurso(true));
                    $form_atividade->initializeDisciplinas($mapper_disciplina->buscaDisciplinas(array('id_curso' => $atividade->getTurma()->getDisciplina()->getCurso()->getIdCurso()), null), $atividade->getTurma()->getDisciplina()->getIdDisciplina(true));
                    $form_atividade->initializeTurmas(
                            $mapper_turma->buscaTurmas(array(
                                'disciplina' => $atividade->getTurma()->getDisciplina()->getIdDisciplina(true)), null), $atividade->getTurma()->getIdTurma(true));
                } else
                    $this->view->not_found = true;

                return;
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        }
    }

    public function buscaAtividadesTurmaAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->_request->isPost()) {
                $id_turma = (int) base64_decode($this->getParam('id_turma'));
                $data_limite = $this->getParam('data');

                $mapper_atividade = new Application_Model_Mappers_Atividade();
                $atividades = $mapper_atividade->buscaAtividadesTurma($id_turma, null, $data_limite);

                $array_atividades = array();
                if (!empty($atividades)) {
                    $i = 0;

                    foreach ($atividades as $atividade) {
                        $array_atividades[$i]['id'] = $atividade->getIdAtividade(true);
                        $array_atividades[$i]['nome'] = $atividade->getNomeAtividade();
                        $array_atividades[$i]['valor'] = $atividade->getValor();
                        $array_atividades[$i]['data'] = $atividade->getDataAtividade(true);
                        $i++;
                    }
                }
                echo json_encode($array_atividades);
                return;
            }
            echo json_encode(null);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            echo json_encode(null);
        }
    }

}
