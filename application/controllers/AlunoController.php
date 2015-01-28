<?php

class AlunoController extends Zend_Controller_Action {

    public function init() {
        $this->view->controller = 'aluno';
    }

    public function indexAction() {
        $periodo = new Application_Model_Mappers_Periodo();

        $this->view->title = "Projeto Incluir - Gerenciar Alunos";

        $form_consulta = new Application_Form_FormConsultaAluno();
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
                $mapper_aluno = new Application_Model_Mappers_Aluno();

                $paginator = $mapper_aluno->buscaAlunos($form_consulta->getValues(), true);
                $paginator->setItemCountPerPage(10);
                $paginator->setCurrentPageNumber($pagina);

                $this->view->resultado_busca = $paginator;
                $this->view->is_fim_periodo_atual = $periodo->verificaFimPeriodo();
            }
        }
    }

    public function cadastrarAction() {
        $this->view->title = "Projeto Incluir - Cadastrar Aluno";
        $periodo = new Application_Model_Mappers_Periodo();

        if (!$periodo->verificaFimPeriodo()) {
            $form_cadastro = new Application_Form_FormAluno();

            $mapper_curso = new Application_Model_Mappers_Curso();
            $mapper_alimentos = new Application_Model_Mappers_Alimento();
            $mapper_turma = new Application_Model_Mappers_Turma();

            $alimentos = $mapper_alimentos->buscaAlimentos();

            $form_cadastro->initializeAlimentos($alimentos);
            $form_cadastro->initializeCursos($mapper_curso->buscaCursos());

            $this->view->form = $form_cadastro;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'aluno', 'action' => 'index'), null, true);

                if ($form_cadastro->isValid($dados)) {
                    if ($this->validaDados($dados)) {
                        $aluno = new Application_Model_Aluno(null, $form_cadastro->getValue('nome_aluno'), $form_cadastro->getValue('cpf'), Application_Model_Aluno::$status_ativo, null, null, $form_cadastro->getValue('rg'), $form_cadastro->getValue('data_nascimento'), $form_cadastro->getValue('email'), $form_cadastro->getValue('escolaridade'), $form_cadastro->getValue('telefone'), $form_cadastro->getValue('celular'), $form_cadastro->getValue('endereco'), $form_cadastro->getValue('bairro'), $form_cadastro->getValue('numero'), $form_cadastro->getValue('complemento'), $form_cadastro->getValue('cep'), $form_cadastro->getValue('cidade'), $form_cadastro->getValue('estado'), $form_cadastro->getValue('data_registro'), $form_cadastro->getValue('is_cpf_responsavel'), $form_cadastro->getValue('nome_responsavel'));

                        foreach ($dados['turmas'] as $turma)
                            $aluno->addTurma(new Application_Model_Turma((int) base64_decode($turma)), $dados['liberacao'][$turma]);

                        foreach ($dados['pagamento_turmas'] as $turma => $pagamentos_turma) {
                            $obj_pagamento = new Application_Model_Pagamento(null, $dados['situacao_turmas'][$turma], $pagamentos_turma);

                            if (!empty($dados['alimentos'][$turma])) {
                                foreach ($dados['alimentos'][$turma] as $tipo_alimento => $quantidade)
                                    $obj_pagamento->addAlimento(new Application_Model_Alimento((int) base64_decode($tipo_alimento)), $quantidade);
                            }

                            $aluno->addPagamento(new Application_Model_Turma(base64_decode($turma)), $obj_pagamento);
                        }

                        $mapper_aluno = new Application_Model_Mappers_Aluno();
                        if ($mapper_aluno->addAluno($aluno)) {
                            $form_cadastro->reset();
                            $this->view->mensagem = "Aluno cadastrado com sucesso!";
                            return;
                        }
                    }
                    $this->view->mensagem = "O aluno não foi cadastrado.<br/>Por favor, verifique se há algum aluno cadastrado com o cpf especificado";
                }

                $form_cadastro->populate($dados);
                if (!empty($dados['turmas'])) {
                    $turmas = $mapper_turma->buscaTurmasByID($dados['turmas']);
                    $form_cadastro->initializeTurmasAlunos($turmas, $dados['turmas']);
                    $this->view->turmas = $turmas;
                }
                if (!empty($dados['liberacao']))
                    $this->view->liberacao = $dados['liberacao'];

                if (!empty($dados['pagamento_turmas']))
                    $this->view->pagamentos = $dados['pagamento_turmas'];

                if (!empty($dados['alimentos']))
                    $this->view->alimentos = $dados['alimentos'];

                $this->view->todos_alimentos = $alimentos;

                if (!empty($dados['cidade']) && !empty($dados['estado']))
                    $form_cadastro->setEstadoCidade($dados['cidade'], $dados['estado']);
            }
        }
    }

    public function alterarAction() {
        $this->view->title = "Projeto Incluir - Alterar Aluno";
        $periodo = new Application_Model_Mappers_Periodo();

        if (!$periodo->verificaFimPeriodo()) {
            $id_aluno = (int) base64_decode($this->getParam('aluno'));

            if ($id_aluno > 0) {
                $form_alteracao = new Application_Form_FormAluno();

                $mapper_curso = new Application_Model_Mappers_Curso();
                $mapper_alimentos = new Application_Model_Mappers_Alimento();
                $mapper_aluno = new Application_Model_Mappers_Aluno();

                $alimentos = $mapper_alimentos->buscaAlimentos();

                $form_alteracao->initializeAlimentos($alimentos);
                $form_alteracao->initializeCursos($mapper_curso->buscaCursos());

                $this->view->form = $form_alteracao;

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();

                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'aluno', 'action' => 'index'), null, true);

                    if ($form_alteracao->isValid($dados)) {
                        if ($this->validaDados($dados)) {
                            $aluno = new Application_Model_Aluno(base64_decode($form_alteracao->getValue('id_aluno')), $form_alteracao->getValue('nome_aluno'), $form_alteracao->getValue('cpf'), Application_Model_Aluno::$status_ativo, null, null, $form_alteracao->getValue('rg'), $form_alteracao->getValue('data_nascimento'), $form_alteracao->getValue('email'), $form_alteracao->getValue('escolaridade'), $form_alteracao->getValue('telefone'), $form_alteracao->getValue('celular'), $form_alteracao->getValue('endereco'), $form_alteracao->getValue('bairro'), $form_alteracao->getValue('numero'), $form_alteracao->getValue('complemento'), $form_alteracao->getValue('cep'), $form_alteracao->getValue('cidade'), $form_alteracao->getValue('estado'), $form_alteracao->getValue('data_registro'), $form_alteracao->getValue('is_cpf_responsavel'), $form_alteracao->getValue('nome_responsavel'));

                            foreach ($dados['turmas'] as $turma)
                                $aluno->addTurma(new Application_Model_Turma((int) base64_decode($turma)), $dados['liberacao'][$turma]);

                            foreach ($dados['pagamento_turmas'] as $turma => $pagamentos_turma) {
                                $obj_pagamento = new Application_Model_Pagamento(null, $dados['situacao_turmas'][$turma], $pagamentos_turma);

                                if (!empty($dados['alimentos'][$turma])) {
                                    foreach ($dados['alimentos'][$turma] as $tipo_alimento => $quantidade)
                                        $obj_pagamento->addAlimento(new Application_Model_Alimento((int) base64_decode($tipo_alimento)), $quantidade);
                                }
                                $aluno->addPagamento(new Application_Model_Turma(base64_decode($turma)), $obj_pagamento);
                            }

                            if ($mapper_aluno->alteraAluno($aluno))
                                $this->view->mensagem = "Aluno alterado com sucesso!";
                            else
                                $this->view->mensagem = "O aluno não foi alterado.<br/>Por favor, verifique se há algum aluno cadastrado com o cpf especificado";
                        } else
                            $this->view->mensagem = "O aluno não foi alterado.<br/>Por favor, verifique se as turmas foram inseridas";
                    }
                }
                $periodo = new Application_Model_Mappers_Periodo();
                $aluno = $mapper_aluno->buscaAlunosByID($id_aluno, $periodo->getIdPeriodo());

                if ($aluno instanceof Application_Model_Aluno) {
                    $form_alteracao->populate($aluno->parseArray(true));
                    $form_alteracao->seTurmasAlunos($aluno->getTurmas());

                    $this->view->turmas = $aluno->getTurmas();
                    $this->view->liberacao = $aluno->getLiberacaoTurmas();
                    $this->view->pagamentos = $aluno->getValoresPagamentos();
                    $this->view->alimentos = $aluno->getAlimentosPagamentos();
                    $this->view->todos_alimentos = $alimentos;
                    $form_alteracao->setEstadoCidade($aluno->getCidade(), $aluno->getEstado());

                    return;
                }
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        }
    }

    public function excluirAction() {
        $this->view->title = "Projeto Incluir - Excluir Aluno";
        $periodo = new Application_Model_Mappers_Periodo();

        if (!$periodo->verificaFimPeriodo()) {
            $id_aluno = (int) base64_decode($this->getParam('aluno'));

            if ($id_aluno > 0) {
                $form_exclusao = new Application_Form_FormAluno();

                $mapper_curso = new Application_Model_Mappers_Curso();
                $mapper_alimentos = new Application_Model_Mappers_Alimento();
                $mapper_aluno = new Application_Model_Mappers_Aluno();
                $alimentos = $mapper_alimentos->buscaAlimentos();

                $form_exclusao->initializeAlimentos($alimentos);
                $form_exclusao->initializeCursos($mapper_curso->buscaCursos());
                $form_exclusao->limpaValidadores();

                $this->view->form = $form_exclusao;

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();

                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'aluno', 'action' => 'index'), null, true);

                    if ($form_exclusao->isValid($dados)) {
                        if ($mapper_aluno->deletaAluno((int) base64_decode($form_exclusao->getValue('id_aluno'))))
                            $this->view->mensagem = "Aluno excluído com sucesso!";
                        else
                            $this->view->mensagem = "O aluno não foi excluido. Por favor, consulte o administrador do sistema.";

                        $this->view->form = null;
                    }
                }
                else {
                    $aluno = $mapper_aluno->buscaAlunosByID($id_aluno);

                    if ($aluno instanceof Application_Model_Aluno) {
                        $form_exclusao->populate($aluno->parseArray(true));
                        $form_exclusao->seTurmasAlunos($aluno->getTurmas());

                        $this->view->turmas = $aluno->getTurmas();
                        $this->view->liberacao = $aluno->getLiberacaoTurmas();
                        $this->view->pagamentos = $aluno->getValoresPagamentos();
                        $this->view->alimentos = $aluno->getAlimentosPagamentos();
                        $this->view->todos_alimentos = $alimentos;
                        $form_exclusao->setEstadoCidade($aluno->getCidade(), $aluno->getEstado());
                    } else
                        $this->view->form = null;
                }
                return;
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        }
    }

    private function validaDados($dados) {
        if (!empty($dados['turmas']) && !empty($dados['pagamento_turmas']) && !empty($dados['liberacao']) && !empty($dados['situacao_turmas'])) {
            if (is_array($dados['pagamento_turmas']) && is_array($dados['pagamento_turmas']) && is_array($dados['liberacao']) && is_array($dados['situacao_turmas']))
                return true;
        }
        return false;
    }

    /**
     * Busca alunos da turma passada e suas informações relativas a frequência.
     * Usado no lançamento de frequência
     */
    public function buscaAlunosTurmaFaltasAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->getRequest()->isPost()) {
                $id_turma = (int) base64_decode($this->getRequest()->getParam('id_turma'));

                if (!empty($id_turma)) {
                    $mapper_aluno = new Application_Model_Mappers_Aluno();
                    $alunos = $mapper_aluno->getAlunosFaltas($id_turma);

                    if (!empty($alunos)) {
                        $mapper_turma = new Application_Model_Mappers_Turma();
                        $turma = $mapper_turma->buscaTurmaByID($id_turma);
                        
                        $i = 0;
                        $array_alunos = array('turma' => array(
                                'nome_turma' => $turma->getCompleteNomeTurma(),
                                'data_inicio' => $turma->getDataInicio(true),
                                'data_termino' => $turma->getDataFim(true)
                        ));

                        $mapper_calendario = new Application_Model_Mappers_DatasAtividade();
                        $datas_atividades = $mapper_calendario->getDatasByPeriodo($turma->getPeriodo());
                        
                        $total_aulas = ($datas_atividades instanceof Application_Model_DatasAtividade) ? $datas_atividades->getQuantidadeAulas() : 0;

                        foreach ($alunos as $aluno) {
                            if ($aluno instanceof Application_Model_Aluno) {
                                $array_alunos[$i]['id_aluno'] = $aluno->getIdAluno();
                                $array_alunos[$i]['nome_aluno'] = $aluno->getNomeAluno();
                                $array_alunos[$i]['faltas'] = $aluno->getFaltas(true);
                                $array_alunos[$i]['media_frequencia'] = $aluno->getPorcentagemFaltas($id_turma, $total_aulas, true);
                                $i++;
                            }
                        }
                        echo json_encode($array_alunos);
                        return;
                    }
                }
            }
            echo json_encode(null);
        } catch (Exception $ex) {
            echo json_encode(null);
        }
    }

    /**
     * Busca alunos da turma passada e suas informações relativas as notas.
     * Usado no lançamento de notas
     */
    public function buscaAlunosTurmaNotaAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->getRequest()->isPost()) {
                $id_turma = (int) base64_decode($this->getRequest()->getParam('id_turma'));

                if (!empty($id_turma)) {
                    $mapper_aluno = new Application_Model_Mappers_Aluno();
                    $alunos = $mapper_aluno->getAlunosNotas($id_turma);

                    if (!empty($alunos)) {
                        $array_alunos = array();
                        $i = 0;

                        foreach ($alunos as $aluno) {
                            if ($aluno instanceof Application_Model_Aluno) {
                                $array_alunos[$i]['id_aluno'] = $aluno->getIdAluno();
                                $array_alunos[$i]['nome_aluno'] = $aluno->getNomeAluno();
                                $array_alunos[$i]['notas'] = $aluno->getNotas(true);
                                $i++;
                            }
                        }
                        echo json_encode($array_alunos);
                        return;
                    }
                }
            }
            echo json_encode(null);
        } catch (Exception $ex) {
            echo json_encode(null);
        }
    }

    /**
     * Retorna os alunos da turma indicada, com nome e id
     */
    public function buscaAlunosTurmaAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->getRequest()->isPost()) {
                $id_turma = (int) base64_decode($this->getRequest()->getParam('id_turma'));

                if (!empty($id_turma)) {
                    $mapper_aluno = new Application_Model_Mappers_Aluno();
                    $alunos = $mapper_aluno->getAlunosByTurma($id_turma);

                    if (!empty($alunos)) {
                        $i = 0;
                        $array_alunos = array();

                        foreach ($alunos as $aluno) {
                            if ($aluno instanceof Application_Model_Aluno) {
                                $array_alunos[$i]['id_aluno'] = $aluno->getIdAluno();
                                $array_alunos[$i]['nome_aluno'] = $aluno->getNomeAluno();
                                $i++;
                            }
                        }
                        echo json_encode($array_alunos);
                        return;
                    }
                }
            }
            echo json_encode(null);
        } catch (Exception $ex) {
            echo json_encode(null);
        }
    }

    public function visualizarAction() {
        $id_aluno = (int) base64_decode($this->getParam('aluno'));

        if ($id_aluno > 0) {
            $this->view->title = "Projeto Incluir - Visualizar Aluno";

            if ($this->getRequest()->isPost())
                $this->_helper->redirector->goToRoute(array('controller' => 'aluno', 'action' => 'index'), null, true);

            $mapper_aluno = new Application_Model_Mappers_Aluno();
            $aluno = $mapper_aluno->buscaAlunosByID($id_aluno);

            if ($aluno instanceof Application_Model_Aluno) {
                $mapper_calendario = new Application_Model_Mappers_DatasAtividade();
                $calendarios = $mapper_calendario->getCalendarios();
                
                $this->view->calendarios = $calendarios;
                $this->view->aluno = $aluno;
                return;
            }
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    /**
     * Retorna os alunos com os nomes indicados. 
     * Utilizado para ajudar a evitar o cadastro de alunos repetidos.
     * Esses alunos são retornados a medida em que o nome é digitado no campo
     */
    public function verificaAlunoAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $nome_aluno = $this->getRequest()->getParam('term');

            if (!empty($nome_aluno)) {
                $filter_string = new Aplicacao_Filtros_StringFilter();
                $mapper_aluno = new Application_Model_Mappers_Aluno();

                echo json_encode($mapper_aluno->verificaAlunoNome($filter_string->filter($nome_aluno)));
                return;
            }

            echo json_encode(null);
        } catch (Exception $ex) {
            echo json_encode(null);
        }
    }

    public function desligarAlunoAction() {
        $this->view->title = "Projeto Incluir - Desligamento de Aluno";
        $periodo = new Application_Model_Mappers_Periodo();

        if (!$periodo->verificaFimPeriodo()) {
            $id_aluno = (int) base64_decode($this->getParam('aluno'));

            if ($id_aluno > 0) {
                $form_desligamento = new Application_Form_FormDesligamentoAluno();
                $mapper_aluno = new Application_Model_Mappers_Aluno();

                $this->view->form = $form_desligamento;

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();
                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'aluno', 'action' => 'index'), null, true);

                    if ($form_desligamento->isValid($dados)) {
                        $aluno = new Application_Model_Aluno((int) base64_decode($form_desligamento->getValue('id_aluno')), null, null, Application_Model_Aluno::$status_desligado, $form_desligamento->getValue('data_desligamento'), $form_desligamento->getValue(
                                        'motivo_desligamento'));

                        if ($mapper_aluno->desligarAluno($aluno))
                            $this->view->mensagem = "O aluno foi desligado com sucesso.";
                        else
                            $this->view->mensagem = "O aluno não foi desligado. Consulte o administrador do sistema para mais informações.";
                    } else
                        $form_desligamento->populate($dados);
                } else {
                    $aluno = $mapper_aluno->buscaAlunosByID($id_aluno);

                    if ($aluno instanceof Application_Model_Aluno) {
                        $form_desligamento->populate(array('id_aluno' => $aluno->getIdAluno(true)));
                        $this->view->aluno = $aluno;
                    }
                }
                return;
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        }
    }

    public function ativarAlunoAction() {
        $periodo = new Application_Model_Mappers_Periodo();
        $this->view->title = "Projeto Incluir - Ativação de Aluno";
        
        if (!$periodo->verificaFimPeriodo()) {
            $id_aluno = (int) base64_decode($this->getParam('aluno'));

            if ($id_aluno > 0) {

                $form_ativacao = new Application_Form_FormConfirmacao();
                $mapper_aluno = new Application_Model_Mappers_Aluno();

                $this->view->form = $form_ativacao;

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();
                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute(array('controller' => 'aluno', 'action' => 'index'), null, true);

                    if ($form_ativacao->isValid($dados)) {
                        if ($mapper_aluno->ativarAluno((int) base64_decode($form_ativacao->getValue('id'))))
                            $this->view->mensagem = "O aluno foi restaurado com sucesso.";
                        else
                            $this->view->mensagem = "O aluno não foi restaurado, por favor consulte o administrador do sistema para mais informações.";
                    }
                } else {
                    $aluno = $mapper_aluno->buscaAlunosByID($id_aluno);

                    if ($aluno instanceof Application_Model_Aluno) {
                        $form_ativacao->populate(array('id' => $aluno->getIdAluno(true)));
                        $this->view->aluno = $aluno;
                    }
                }
                return;
            }
            $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
        }
    }
}
