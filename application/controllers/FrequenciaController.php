<?php

class FrequenciaController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // action body
    }

    public function frequenciaAlunoAction() {
        try {
            $periodo = new Application_Model_Mappers_Periodo();

            $this->view->title = "Projeto Incluir - Frequência de Alunos";

            if (!$periodo->verificaFimPeriodo()) {
                $form_frequencia = new Application_Form_FormFrequenciaAluno();
                $usuario = Zend_Auth::getInstance()->getIdentity();

                $calendario_academico = new Application_Model_Mappers_DatasAtividade();
                $calendario_atual = $calendario_academico->getDatasByPeriodo($periodo->getPeriodoAtual());

                $mapper_curso = new Application_Model_Mappers_Curso();
                $form_frequencia->initializeCursos($mapper_curso->buscaCursos(array('status' => Application_Model_Curso::status_ativo)));

                $this->view->form = $form_frequencia;
                $this->view->datas_atividade = json_encode($calendario_atual->parseArray(true));

                if ($this->getRequest()->isPost()) {
                    $dados = $this->getRequest()->getPost();

                    if (isset($dados['cancelar']))
                        $this->_helper->redirector->goToRoute($usuario->getUserIndex(), null, true);

                    $faltas = null;
                    $mapper_turma = new Application_Model_Mappers_Turma();

                    if (isset($dados['turma'])) {
                        $faltas = $form_frequencia->getFaltas($dados, $mapper_turma->getQuantidadeAlunos((int) base64_decode($dados['turma']), false));
                    }

                    if (is_array($faltas) && $form_frequencia->isValid($dados)) {
                        $mapper_aluno = new Application_Model_Mappers_Aluno();
                        $mapper_frequencia = new Application_Model_Mappers_Frequencia();

                        if ($mapper_frequencia->lancamentoFrequenciaAlunos($faltas, $mapper_turma->buscaTurmaByID((int) base64_decode($dados['turma'])), $mapper_aluno->getTurmaAlunosID((int) base64_decode($form_frequencia->getValue('turma'))), DateTime::createFromFormat('d/m/Y', $form_frequencia->getValue('data'))))
                            $this->view->mensagem = "Frequência lançada/alterada com sucesso!";
                        else
                            $this->view->mensagem = "Houve problemas para efetuar o lançamento";

                        $form_frequencia->reset();
                    } else
                        $this->view->mensagem = "Houve problemas para efetuar o lançamento";
                }
            } else
                $this->view->inativo = true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            $this->view->mensagem = "Houve problemas para efetuar o lançamento";
        }
    }

    public function frequenciaVoluntarioAction() {
        $periodo = new Application_Model_Mappers_Periodo();
        $this->view->title = "Projeto Incluir - Frequência de Voluntários";

        if (!$periodo->verificaFimPeriodo()) {
            $usuario = Zend_Auth::getInstance()->getIdentity();

            $calendario_academico = new Application_Model_Mappers_DatasAtividade();
            $datas_atividade = $calendario_academico->getDatasByPeriodo($periodo->getPeriodoAtual());
            $form_frequencia = new Application_Form_FormFrequenciaVoluntario();

            $form_frequencia->initializeSetores();

            $this->view->datas_atividade = json_encode($datas_atividade->parseArray(true));
            $this->view->form = $form_frequencia;

            if ($this->getRequest()->isPost()) {
                $dados = $this->getRequest()->getPost();

                if (isset($dados['cancelar']))
                    $this->_helper->redirector->goToRoute($usuario->getUserIndex(), null, true);

                $mapper_voluntario = new Application_Model_Mappers_Voluntario();
                $frequencias = $form_frequencia->getFrequencia($dados, $mapper_voluntario->getCountVoluntarios());

                if (is_array($frequencias) && $form_frequencia->isValid($dados) && Application_Model_EscalaFrequencia::verificaFrequencias($frequencias)) {
                    $mappper_frequencia = new Application_Model_Mappers_Frequencia();

                    if ($mappper_frequencia->lancamentoFrequenciaVoluntarios($frequencias, DateTime::createFromFormat('d/m/Y', $form_frequencia->getValue('data'))))
                        $this->view->mensagem = "Frequência lançada/alterada com sucesso!";
                    else
                        $this->view->mensagem = "Houve problemas para efetuar o lançamento";

                    $form_frequencia->reset();
                } else
                    $this->view->mensagem = "Houve problemas para efetuar o lançamento";
            }
        } else
            $this->view->inativo = true;
    }

}
