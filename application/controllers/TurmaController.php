<?php

class TurmaController extends Zend_Controller_Action {

    public function init() {
        $this->view->controller = "turma";
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Gerenciar Turmas";

        $form_consulta = new Application_Form_FormConsultaTurma();
        $mapper_disciplina = new Application_Model_Mappers_Disciplina();

        $periodo = new Application_Model_Mappers_Periodo();

        $form_consulta->initializeDisciplinas($mapper_disciplina->buscaDisciplinas());
        $this->view->form = $form_consulta;
        $this->view->inativo = ($periodo->verificaFimPeriodo()) ? true : null;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();
            $pagina = 1;
        } else {
            $dados = $this->getRequest()->getParams();
            $pagina = $this->_getParam('pagina');
        }

        if ($form_consulta->isValid($dados)) {
            if ($this->getRequest()->isPost() || !empty($pagina)) {
                $mapper_turma = new Application_Model_Mappers_Turma();

                $paginator = $mapper_turma->buscaTurmas($form_consulta->getValues(), true);
                $paginator->setItemCountPerPage(10);
                $paginator->setCurrentPageNumber($pagina);

                $this->view->resultado_busca = $paginator;

                if (!$periodo->verificaFimPeriodo()) {
                    $periodo_atual = $periodo->getPeriodoAtual();
                    $this->view->periodo_atual = $periodo_atual->getIdPeriodo();
                }
            }
        }
    }

    public function cadastrarAction() {
        $periodo = new Application_Model_Mappers_Periodo();
        $this->view->title = "Projeto Incluir - Cadastrar Turma";

        if (!$periodo->verificaFimPeriodo()) {
            $form_cadastro = new Application_Form_FormTurma();
            $mapper_cursos = new Application_Model_Mappers_Curso();

            $form_cadastro->initializeCursos($mapper_cursos->buscaCursos());
            $this->view->form = $form_cadastro;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'turma', 'action' => 'index'), null, true);

                if ($form_cadastro->isValid($dados)) {
                    $mapper_turma = new Application_Model_Mappers_Turma();
                    $periodo_atual = $periodo->getPeriodoAtual();

                    $turma = new Application_Model_Turma(null, $form_cadastro->getValue('nome_turma'), $form_cadastro->getValue('data_inicio'), $form_cadastro->getValue('data_fim'), $form_cadastro->getValue('horario_inicio'), $form_cadastro->getValue('horario_fim'), new Application_Model_Disciplina(base64_decode($form_cadastro->getValue('disciplina'))), Application_Model_Turma::$status_iniciada, null, $periodo_atual->getIdPeriodo());

                    if (!empty($dados['professores'])) {
                        foreach ($dados['professores']as $professor)
                            $turma->addProfessor(new Application_Model_Professor(base64_decode($professor)));
                    }

                    if ($mapper_turma->addTurma($turma)) {
                        $form_cadastro->reset();
                        $this->view->mensagem = "Turma cadastrada com sucesso!";
                        return;
                    }

                    $this->view->mensagem = "A turma não foi cadastrada.<br/>Por favor, verifique se há alguma turma cadastrada com o nome especificado";
                }

                $mapper_voluntarios = new Application_Model_Mappers_Voluntario();
                $mapper_disciplinas = new Application_Model_Mappers_Disciplina();

                $form_cadastro->populate($dados);

                if (!empty($dados['professores']))
                    $this->view->professores = $mapper_voluntarios->getProfessoresByIDs($dados['professores']);

                if (!empty($dados['curso']) && !empty($dados['disciplina'])) {
                    $form_cadastro->initializeDisciplinas($mapper_disciplinas->buscaDisciplinas(array('id_curso' => (int) base64_decode($dados['curso']))), $dados['disciplina']);
                    $form_cadastro->initializeProfessores($mapper_voluntarios->getProfessoresByDisciplina((int) base64_decode($dados['disciplina'])));
                }
            }
        } else
            $this->view->inativo = true;
    }

    public function alterarAction() {
        $periodo = new Application_Model_Mappers_Periodo();
        $this->view->title = "Projeto Incluir - Alterar Turma";

        if (!$periodo->verificaFimPeriodo()) {
            $id_turma = (int) base64_decode($this->getParam('turma'));

            if ($id_turma > 0) {
                $form_alteracao = new Application_Form_FormTurma();
                $mapper_turma = new Application_Model_Mappers_Turma();
                $periodo_atual = new Application_Model_Mappers_Periodo();

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();

                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'turma', 'action' => 'index'), null, true);

                    if ($form_alteracao->isValid($dados)) {
                        $turma = new Application_Model_Turma(base64_decode($form_alteracao->getValue('id_turma')), $form_alteracao->getValue('nome_turma'), $form_alteracao->getValue('data_inicio'), $form_alteracao->getValue('data_fim'), $form_alteracao->getValue('horario_inicio'), $form_alteracao->getValue('horario_fim'), new Application_Model_Disciplina(base64_decode($form_alteracao->getValue('disciplina'))), Application_Model_Turma::$status_iniciada, null, $periodo->getIdPeriodo());

                        if (!empty($dados['professores'])) {
                            foreach ($dados['professores']as $professor)
                                $turma->addProfessor(new Application_Model_Professor(base64_decode($professor)));
                        }

                        if ($mapper_turma->alterarTurma($turma))
                            $this->view->mensagem = "Turma alterada com sucesso!";
                        else
                            $this->view->mensagem = "A turma não foi alterada.<br/>Por favor, verifique se há alguma turma cadastrada com o nome especificado";
                    }
                }

                $turma = $mapper_turma->buscaTurmaByID($id_turma, $periodo_atual->getIdPeriodo(), true);

                if ($turma instanceof Application_Model_Turma) {
                    $mapper_cursos = new Application_Model_Mappers_Curso();
                    $mapper_voluntarios = new Application_Model_Mappers_Voluntario();
                    $mapper_disciplinas = new Application_Model_Mappers_Disciplina();

                    $form_alteracao->populate($turma->parseArray(true));

                    if ($turma->hasProfessores())
                        $this->view->professores = $turma->getProfessores();

                    $form_alteracao->initializeCursos($mapper_cursos->buscaCursos(), $turma->getDisciplina()->getCurso()->getIdCurso(true));
                    $form_alteracao->initializeDisciplinas($mapper_disciplinas->buscaDisciplinas(array('id_curso' => $turma->getDisciplina()->getCurso()->getIdCurso())), $turma->getDisciplina()->getIdDisciplina(true));
                    $form_alteracao->initializeProfessores($mapper_voluntarios->getProfessoresByDisciplina($turma->getDisciplina()->getIdDisciplina()));
                    $this->view->form = $form_alteracao;

                    return;
                }
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        } else
            $this->view->inativo = true;
    }

    public function excluirAction() {
        $periodo = new Application_Model_Mappers_Periodo();
        $this->view->title = "Projeto Incluir - Excluir Turma";

        if (!$periodo->verificaFimPeriodo()) {
            $id_turma = (int) base64_decode($this->getParam('turma'));

            if ($id_turma > 0) {
                $periodo = new Application_Model_Mappers_Periodo();

                $form_exclusao = new Application_Form_FormTurma();
                $mapper_turma = new Application_Model_Mappers_Turma();

                $form_exclusao->limpaValidadores();

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();

                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'turma', 'action' => 'index'), null, true);

                    if ($form_exclusao->isValid($dados)) {
                        if ($mapper_turma->excluirTurma((int) base64_decode($form_exclusao->getValue('id_turma'))))
                            $this->view->mensagem = "Turma excluída com sucesso!";
                        else
                            $this->view->mensagem = "A turma não foi excluida.<br/>Por favor, tente novamente ou procure o administrador do sistema";
                    }
                }

                else {
                    $turma = $mapper_turma->buscaTurmaByID($id_turma, $periodo->getIdPeriodo(), true);

                    if ($turma instanceof Application_Model_Turma) {
                        $mapper_cursos = new Application_Model_Mappers_Curso();
                        $mapper_voluntarios = new Application_Model_Mappers_Voluntario();
                        $mapper_disciplinas = new Application_Model_Mappers_Disciplina();

                        $form_exclusao->populate($turma->parseArray(true));

                        if ($turma->hasProfessores())
                            $this->view->professores = $turma->getProfessores();

                        $form_exclusao->initializeCursos($mapper_cursos->buscaCursos(), $turma->getDisciplina()->getCurso()->getIdCurso(true));
                        $form_exclusao->initializeDisciplinas($mapper_disciplinas->buscaDisciplinas(array('id_curso' => $turma->getDisciplina()->getCurso()->getIdCurso())), $turma->getDisciplina()->getIdDisciplina(true));
                        $form_exclusao->initializeProfessores($mapper_voluntarios->getProfessoresByDisciplina($turma->getDisciplina()->getIdDisciplina()));
                        $this->view->form = $form_exclusao;
                    }
                }
                return;
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        } else
            $this->view->inativo = true;
    }

    /**
     * Action que retorna as turmas da disciplina indicada
     */
    public function buscarTurmasAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->_request->isPost()) {
                $periodo = new Application_Model_Mappers_Periodo();

                $id_disciplina = $this->getRequest()->getParam('id_disciplina');
                $id_periodo = (strlen($this->getRequest()->getParam('id_periodo')) > 0) ? (int) base64_decode($this->getRequest()->getParam('id_periodo')) : $periodo->getIdPeriodo();

                $mapper_turma = new Application_Model_Mappers_Turma();

                if (!empty($id_disciplina))
                    $turmas = $mapper_turma->buscaTurmas(array('disciplina' => $id_disciplina, 'periodo' => $id_periodo, 'status' => Application_Model_Turma::$status_iniciada), null);
                else
                    $turmas = $mapper_turma->buscaTurmas(array('periodo' => $id_periodo), null);

                $array_turmas = array();

                if (!empty($turmas)) {
                    $i = 0;
                    foreach ($turmas as $turma) {
                        $array_turmas[$i]['id_turma'] = $turma->getIdTurma(true);
                        $array_turmas[$i]['nome_turma'] = $turma->getNomeTurma();
                        $i++;
                    }
                }
                echo json_encode($array_turmas);
                return;
            }
            echo json_encode(null);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            echo json_encode(null);
        }
    }

    /**
     * Action que verifica se o aluno indicado foi aprovado na turma da disciplina pré requisito
     * indicada em períodos anteriores.
     */
    public function verificarLiberacaoTurmaAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->_request->isPost()) {
                $id_turma = $this->getRequest()->getParam('id_turma');
                $id_disciplina = (int) base64_decode($this->getRequest()->getParam('id_disciplina'));
                $id_aluno = $this->getRequest()->getParam('id_aluno');

                $mapper_disciplina = new Application_Model_Mappers_Disciplina();
                $pre_requisitos = $mapper_disciplina->getPreRequisitos($id_disciplina);

//                if (empty($id_aluno)) {
                $array_pre_requisitos = array();

                if (!empty($pre_requisitos)) {
                    $i = 0;
                    foreach ($pre_requisitos as $pre_requisito) {
                        $array_pre_requisitos[$i]['id_pre_requisito'] = $pre_requisito->getIdDisciplina(true);
                        $array_pre_requisitos[$i]['nome_pre_requisito'] = $pre_requisito->getNomeDisciplina();
                        $i++;
                    }
                }
                echo json_encode($array_pre_requisitos);
                return;
                //              }
                /*            else{
                  $mapper_aluno = new Application_Model_Mappers_Aluno();
                  $array_pre_requisitos = array();

                  if(!empty($pre_requisitos))
                  var_dump($mapper_aluno->isAprovado($id_aluno, $pre_requisitos));
                  }
                 */
            }
            echo json_encode(null);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            echo json_encode(null);
        }
    }

    public function cancelarAction() {
        $periodo = new Application_Model_Mappers_Periodo();

        if (!$periodo->verificaFimPeriodo()) {

            $id_turma = (int) base64_decode($this->getParam('turma'));

            if ($id_turma > 0) {
                $this->view->title = "Projeto Incluir - Cancelar Turma";

                $form_cancelamento = new Application_Form_FormConfirmacao();
                $mapper_turma = new Application_Model_Mappers_Turma();
                $periodo = new Application_Model_Mappers_Periodo();

                $this->view->form = $form_cancelamento;

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();

                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'turma', 'action' => 'index'), null, true);

                    if ($form_cancelamento->isValid($dados)) {
                        if ($mapper_turma->cancelarTurma((int) base64_decode($form_cancelamento->getValue('id'))))
                            $this->view->mensagem = "Turma cancelada com sucesso!";
                        else
                            $this->view->mensagem = "A turma não foi cancelada.<br/>Por favor, tente novamente ou procure o administrador do sistema";
                    }
                }

                $turma = $mapper_turma->buscaTurmaByID($id_turma, $periodo->getIdPeriodo(), true);

                if ($turma instanceof Application_Model_Turma) {
                    $form_cancelamento->populate(array('id' => $turma->getIdTurma(true)));
                    $this->view->turma = $turma;
                }
                return;
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        }
    }

    public function visualizarAction() {
        $id_turma = (int) base64_decode($this->getParam('turma'));

        if ($id_turma > 0) {
            $this->view->title = "Projeto Incluir - Visualizar Turma";

            $mapper_turma = new Application_Model_Mappers_Turma();
            $turma = $mapper_turma->buscaTurmaByID($id_turma);

            if ($turma instanceof Application_Model_Turma) {
                $mapper_aluno = new Application_Model_Mappers_Aluno();
                $datas_calendario_letivo = new Application_Model_DatasAtividade();

                $this->view->total_aulas = $datas_calendario_letivo->getQuantidadeAulas();
                $this->view->turma = $turma;
                $this->view->alunos = $mapper_aluno->getAlunosByDisciplina(array('id_turma' => $this->getParam('turma')), true, true);
                $this->view->id_turma = $id_turma;
                return;
            }
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    /**
     * Action que retorna a quantidade de alunos da turma especificada. Utilizado no cadastro de alunos
     * para informar quantos alunos existem na turma indicada.
     */
    public function buscarQuantidadeTurmaAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->_request->isPost()) {
                $id_turma = (int) base64_decode($this->getRequest()->getParam('id_turma'));

                if (!empty($id_turma)) {
                    $mapper_turma = new Application_Model_Mappers_Turma();
                    echo json_encode($mapper_turma->getQuantidadeAlunos($id_turma));
                    return;
                }
            }
            echo json_encode(null);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            echo json_encode(null);
        }
    }

    /**
     * Decodifica parâmetros passados pela url. Utilizado quando há paginação de
     * resultados e filtros passados pela url.
     * 
     * @param array $dados
     */
    private function decodeParams(&$dados) {
        foreach ($dados as &$data)
            $data = urldecode(urldecode($data));
    }

    public function quantidadeAlunosTurmaAction() {
        $this->view->title = "Projeto Incluir - Quantidade de Alunos de Turmas Ativas";

        $form_quantidade_periodo = new Application_Form_FormQuantidadeAlunosTurma();
        $periodo = new Application_Model_Mappers_Periodo();
        $mapper_turma = new Application_Model_Mappers_Turma();

        $form_quantidade_periodo->initializePeriodo($periodo->getPeriodos(), $periodo->getIdPeriodo(true));

        $this->view->form = $form_quantidade_periodo;
        $this->view->alunos_turma = $mapper_turma->getQuantidadeAlunos();
    }

}
