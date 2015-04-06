<?php

class VoluntarioController extends Zend_Controller_Action {

    public function init() {
        $this->view->controller = "voluntario";
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Gerenciamento de Voluntários";
        $form_consulta = new Application_Form_FormConsultaVoluntario();
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
                $mapper_voluntario = new Application_Model_Mappers_Voluntario();

                $paginator = $mapper_voluntario->buscaVoluntarios($form_consulta->getValues(), true);
                $paginator->setItemCountPerPage(10);
                $paginator->setCurrentPageNumber($pagina);

                $this->view->resultado_busca = $paginator;
            }
        }
    }

    public function cadastrarAction() {
        $this->view->title = "Projeto Incluir - Cadastro de Voluntário";

        $form_cadastro = new Application_Form_FormVoluntario();
        $this->view->form = $form_cadastro;

        // campo de curso não precisa ser populado
        $mapper_cursos = new Application_Model_Mappers_Curso();
        $form_cadastro->initializeCursos($mapper_cursos->buscaCursos(array('status' => Application_Model_Curso::status_ativo)));

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();
            if (isset($dados['cancelar']))
                $this->_helper->redirector->goToRoute(array('controller' => 'voluntario', 'action' => 'index'), null, true);

            $form_cadastro->controleValidacao($dados);

            if ($form_cadastro->isValid($dados)) {
                if (!empty($dados['disciplinas'])) {
                    $voluntario = new Application_Model_Professor(null, $form_cadastro->getValue('nome'), $form_cadastro->getValue('cpf'), $form_cadastro->getValue('rg'), $form_cadastro->getValue('data_nascimento'), $form_cadastro->getValue('email'), $form_cadastro->getValue('formacao'), $form_cadastro->getValue('profissao'), $form_cadastro->getValue('telefone_fixo'), $form_cadastro->getValue('telefone_celular'), $form_cadastro->getValue('endereco'), $form_cadastro->getValue('bairro'), $form_cadastro->getValue('cidade'), $form_cadastro->getValue('estado'), $form_cadastro->getValue('numero'), $form_cadastro->getValue('complemento'), $form_cadastro->getValue('cep'), $form_cadastro->getValue('carga_horaria'), $form_cadastro->getValue('data_inicio'), null, null, Application_Model_Voluntario::$status_ativo, $form_cadastro->getValue('conhecimento'), $form_cadastro->getValue('disponibilidade'));

                    foreach ($dados['disciplinas'] as $disciplina)
                        $voluntario->addDisciplinasMinistradas(new Application_Model_Disciplina(base64_decode($disciplina)));
                } else
                    $voluntario = new Application_Model_Voluntario(null, $form_cadastro->getValue('nome'), $form_cadastro->getValue('cpf'), $form_cadastro->getValue('rg'), $form_cadastro->getValue('data_nascimento'), $form_cadastro->getValue('email'), $form_cadastro->getValue('formacao'), $form_cadastro->getValue('profissao'), $form_cadastro->getValue('telefone_fixo'), $form_cadastro->getValue('telefone_celular'), $form_cadastro->getValue('endereco'), $form_cadastro->getValue('bairro'), $form_cadastro->getValue('cidade'), $form_cadastro->getValue('estado'), $form_cadastro->getValue('numero'), $form_cadastro->getValue('complemento'), $form_cadastro->getValue('cep'), $form_cadastro->getValue('carga_horaria'), $form_cadastro->getValue('data_inicio'), null, null, Application_Model_Voluntario::$status_ativo, $form_cadastro->getValue('conhecimento'), $form_cadastro->getValue('disponibilidade'));

                $tipos_atividades = array(Application_Model_Voluntario::$atividade_informatica => 'funcao_informatica', Application_Model_Voluntario::$atividade_marketing => 'funcao_marketing', Application_Model_Voluntario::$atividade_rh => 'funcao_rh', Application_Model_Voluntario::$atividade_secretaria => 'funcao_secretaria');

                foreach ($tipos_atividades as $key => $tipo_atividade) {
                    if (in_array($key, $form_cadastro->getValue('atividades')))
                        $voluntario->addFuncao($form_cadastro->getValue($tipo_atividade), $key);
                }

                $mapper_voluntario = new Application_Model_Mappers_Voluntario();

                if ($mapper_voluntario->addVoluntario($voluntario)) {
                    $form_cadastro->reset();
                    $this->view->mensagem = "Voluntário cadastrado com sucesso!";
                    return;
                } else
                    $this->view->mensagem = "O voluntário não foi cadastrado.<br/>Por favor, verifique se há alguma voluntário cadastrado com o nome ou cpf especificado";
            }

            $mapper_disciplina = new Application_Model_Mappers_Disciplina();
            $form_cadastro->populate($dados);
            $form_cadastro->setEstadoCidade($dados['cidade'], $dados['estado']);

            if (!empty($dados['disciplinas']))
                $this->view->disciplinas = $mapper_disciplina->buscaDisciplinasByID($dados['disciplinas']);
        }
    }

    public function alterarAction() {
        $id_voluntario = (int) base64_decode($this->getParam('voluntario'));

        if ($id_voluntario > 0) {
            $this->view->title = "Projeto Incluir - Alterar Voluntário";

            $form_alteracao = new Application_Form_FormVoluntario();
            $mapper_voluntario = new Application_Model_Mappers_Voluntario();

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();
                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'voluntario', 'action' => 'index'), null, true);

                $form_alteracao->controleValidacao($dados);

                if ($form_alteracao->isValid($dados)) {
                    if (!empty($dados['disciplinas'])) {
                        $voluntario = new Application_Model_Professor(base64_decode($form_alteracao->getValue('id_voluntario')), $form_alteracao->getValue('nome'), $form_alteracao->getValue('cpf'), $form_alteracao->getValue('rg'), $form_alteracao->getValue('data_nascimento'), $form_alteracao->getValue('email'), $form_alteracao->getValue('formacao'), $form_alteracao->getValue('profissao'), $form_alteracao->getValue('telefone_fixo'), $form_alteracao->getValue('telefone_celular'), $form_alteracao->getValue('endereco'), $form_alteracao->getValue('bairro'), $form_alteracao->getValue('cidade'), $form_alteracao->getValue('estado'), $form_alteracao->getValue('numero'), $form_alteracao->getValue('complemento'), $form_alteracao->getValue('cep'), $form_alteracao->getValue('carga_horaria'), $form_alteracao->getValue('data_inicio'), null, null, Application_Model_Voluntario::$status_ativo, $form_alteracao->getValue('conhecimento'), $form_alteracao->getValue('disponibilidade'));

                        foreach ($dados['disciplinas'] as $disciplina)
                            $voluntario->addDisciplinasMinistradas(new Application_Model_Disciplina(base64_decode($disciplina)));
                    } 
                    else
                        $voluntario = new Application_Model_Voluntario(base64_decode($form_alteracao->getValue('id_voluntario')), $form_alteracao->getValue('nome'), $form_alteracao->getValue('cpf'), $form_alteracao->getValue('rg'), $form_alteracao->getValue('data_nascimento'), $form_alteracao->getValue('email'), $form_alteracao->getValue('formacao'), $form_alteracao->getValue('profissao'), $form_alteracao->getValue('telefone_fixo'), $form_alteracao->getValue('telefone_celular'), $form_alteracao->getValue('endereco'), $form_alteracao->getValue('bairro'), $form_alteracao->getValue('cidade'), $form_alteracao->getValue('estado'), $form_alteracao->getValue('numero'), $form_alteracao->getValue('complemento'), $form_alteracao->getValue('cep'), $form_alteracao->getValue('carga_horaria'), $form_alteracao->getValue('data_inicio'), null, null, Application_Model_Voluntario::$status_ativo, $form_alteracao->getValue('conhecimento'), $form_alteracao->getValue('disponibilidade'));

                    $tipos_atividades = array(Application_Model_Voluntario::$atividade_informatica => 'funcao_informatica', Application_Model_Voluntario::$atividade_marketing => 'funcao_marketing', Application_Model_Voluntario::$atividade_rh => 'funcao_rh', Application_Model_Voluntario::$atividade_secretaria => 'funcao_secretaria');

                    foreach ($tipos_atividades as $key => $tipo_atividade) {
                        if (in_array($key, $form_alteracao->getValue('atividades')))
                            $voluntario->addFuncao($form_alteracao->getValue($tipo_atividade), $key);
                    }

                    if ($mapper_voluntario->alterarVoluntario($voluntario))
                        $this->view->mensagem = "Voluntário alterado com sucesso!";
                    else
                        $this->view->mensagem = "O voluntário não foi alterado.<br/>Por favor, verifique se há alguma voluntário cadastrado com o nome ou cpf especificado";
                }
            }
            $voluntario = $mapper_voluntario->buscaVoluntarioByID($id_voluntario);

            if ($voluntario instanceof Application_Model_Voluntario) {
                $mapper_cursos = new Application_Model_Mappers_Curso();

                $form_alteracao->populate($voluntario->parseArray(true));
                $form_alteracao->setEstadoCidade($voluntario->getCidade(), $voluntario->getEstado());
                $form_alteracao->initializeCursos($mapper_cursos->buscaCursos(array('status' => Application_Model_Curso::status_ativo)));

                if ($voluntario instanceof Application_Model_Professor)
                    $this->view->disciplinas = $voluntario->getDisciplinasMinistradas();

                $this->view->form = $form_alteracao;
                return;
            }
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function desligarAction() {
        $id_voluntario = (int) base64_decode($this->getParam('voluntario'));

        if ($id_voluntario > 0) {
            $this->view->title = "Projeto Incluir - Desligamento de Voluntário";

            $form_desligamento = new Application_Form_FormDesligamentoVoluntario();
            $mapper_voluntario = new Application_Model_Mappers_Voluntario();

            $this->view->form = $form_desligamento;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();
                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'voluntario', 'action' => 'index'), null, true);

                if ($form_desligamento->isValid($dados)) {
                    $voluntario = new Application_Model_Voluntario(base64_decode($form_desligamento->getValue('id_voluntario')), null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, $form_desligamento->getValue('data_desligamento'), $form_desligamento->getValue('motivo_desligamento'), Application_Model_Voluntario::$status_desligado, null, null);

                    if ($mapper_voluntario->desligarVoluntario($voluntario))
                        $this->view->mensagem = "O voluntário foi desligado com sucesso.";
                    else
                        $this->view->mensagem = "O voluntário não foi desligado. Consulte o administrador do sistema para mais informações.";
                } else
                    $form_desligamento->populate($dados);
            }
            else {
                $voluntario = $mapper_voluntario->buscaVoluntarioByID($id_voluntario, true);

                if ($voluntario instanceof Application_Model_Voluntario) {
                    $form_desligamento->populate(array('id_voluntario' => $voluntario->getIdVoluntario(true)));
                    $this->view->voluntario = $voluntario;
                }
            }
            return;
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function ativarAction() {
        $id_voluntario = (int) base64_decode($this->getParam('voluntario'));

        if ($id_voluntario > 0) {
            $this->view->title = "Projeto Incluir - Ativação de Voluntário";

            $form_ativacao = new Application_Form_FormConfirmacao();
            $mapper_voluntario = new Application_Model_Mappers_Voluntario();

            $this->view->form = $form_ativacao;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();
                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'voluntario', 'action' => 'index'), null, true);

                if ($form_ativacao->isValid($dados)) {
                    if ($mapper_voluntario->ativarVoluntario((int) base64_decode($form_ativacao->getValue('id'))))
                        $this->view->mensagem = "O voluntário foi restaurado com sucesso.";
                    else
                        $this->view->mensagem = "O voluntário não foi restaurado, por favor consulte o administrador do sistema para mais informações.";
                }
            }
            else {
                $voluntario = $mapper_voluntario->buscaVoluntarioByID($id_voluntario);

                if ($voluntario instanceof Application_Model_Voluntario) {
                    $form_ativacao->populate(array('id' => $voluntario->getIdVoluntario(true)));
                    $this->view->voluntario = $voluntario;
                }
            }
            return;
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function excluirAction() {
        $id_voluntario = (int) base64_decode($this->getParam('voluntario'));

        if ($id_voluntario > 0) {
            $this->view->title = "Projeto Incluir - Excluir Voluntario";

            $form_exclusao = new Application_Form_FormVoluntario();
            $mapper_voluntario = new Application_Model_Mappers_Voluntario();

            $form_exclusao->limpaValidadores();

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();
                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute(array('controller' => 'disciplina', 'action' => 'index'), null, true);

                if ($form_exclusao->isValid($dados)) {

                    if ($mapper_voluntario->excluirVoluntario((int) base64_decode($form_exclusao->getValue('id_voluntario')))) {
                        $form_exclusao->reset();
                        $this->view->mensagem = "Voluntário excluído com sucesso!";
                    } else
                        $this->view->mensagem = "O voluntário não foi excluído.<br/>Por favor, tente novamente ou contate o administrador do sistema.";
                }
            }
            else {
                $voluntario = $mapper_voluntario->buscaVoluntarioByID($id_voluntario);

                if ($voluntario instanceof Application_Model_Voluntario) {
                    $mapper_cursos = new Application_Model_Mappers_Curso();

                    $form_exclusao->populate($voluntario->parseArray(true));
                    $form_exclusao->setEstadoCidade($voluntario->getCidade(), $voluntario->getEstado());
                    $form_exclusao->initializeCursos($mapper_cursos->buscaCursos());

                    if ($voluntario instanceof Application_Model_Professor)
                        $this->view->disciplinas = $voluntario->getDisciplinasMinistradas();

                    $this->view->form = $form_exclusao;
                }
            }
            return;
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function buscarProfessoresAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->_request->isPost()) {
                $id_disciplina = (int) base64_decode($this->getRequest()->getParam('id_disciplina'));

                if ($id_disciplina > 0) {
                    $mapper_voluntario = new Application_Model_Mappers_Voluntario();
                    $professores = $mapper_voluntario->getProfessoresByDisciplina($id_disciplina);

                    $array_professores = array();

                    if (!empty($professores)) {
                        $i = 0;
                        foreach ($professores as $professor) {
                            $array_professores[$i]['id_professor'] = $professor->getIdProfessor(true);
                            $array_professores[$i]['nome_professor'] = $professor->getNomeVoluntario();
                            $i++;
                        }
                    }
                    echo json_encode($array_professores);
                    return;
                }
            }
            echo json_encode(null);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            echo json_encode(null);
        }
    }

    public function visualizarAction() {
        $id_voluntario = (int) base64_decode($this->getParam('voluntario'));

        if ($id_voluntario > 0) {
            $this->view->title = "Projeto Incluir - Visualizar Voluntário";

            $mapper_voluntarios = new Application_Model_Mappers_Voluntario();

            if ($this->getRequest()->isPost())
                $this->_helper->redirector->goToRoute(array('controller' => 'voluntario', 'action' => 'index'), null, true);

            $voluntario = $mapper_voluntarios->buscaVoluntarioByID($id_voluntario);

            if ($voluntario instanceof Application_Model_Voluntario) {
                $this->view->voluntario = $voluntario;
                return;
            }
        }
        $this->_helper->redirector->goToRoute(array('controller' => 'error', 'action' => 'error'), null, true);
    }

    public function verificaVoluntarioAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $nome_voluntario = $this->getRequest()->getParam('term');

            if (!empty($nome_voluntario)) {
                $filter_string = new Aplicacao_Filtros_StringFilter();
                $mapper_voluntario = new Application_Model_Mappers_Voluntario();

                echo json_encode($mapper_voluntario->verificaVoluntarioNome($filter_string->filter($nome_voluntario)));
                return;
            }
            echo json_encode(null);
        } catch (Exception $ex) {
            echo json_encode(null);
        }
    }

    public function buscaVoluntariosSetorAction() {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            if ($this->getRequest()->isPost()) {
                //$aux = base64_decode($this->getParam('setor'));
                //$setor = ($aux == 'all') ? '' : (int) $aux;

                $mapper_voluntario = new Application_Model_Mappers_Voluntario();
                $voluntarios = $mapper_voluntario->getAllVoluntarios();
                $array_voluntarios = array();

                if (!empty($voluntarios)) {
                    $i = 0;
                    foreach ($voluntarios as $voluntario) {
                        $array_voluntarios[$i]['id_voluntario'] = $voluntario->getIdVoluntario(true);
                        $array_voluntarios[$i]['nome_voluntario'] = $voluntario->getNomeVoluntario();
                        $array_voluntarios[$i]['frequencia'] = $voluntario->getFrequencias(true);
                        $array_voluntarios[$i]['total_horas'] = $voluntario->getTotalHoras();
                        $i++;
                    }
                }
                echo json_encode($array_voluntarios);
                return;
            }
            echo json_encode(null);
        } catch (Exception $ex) {
            echo json_encode(null);
        }
    }

}
