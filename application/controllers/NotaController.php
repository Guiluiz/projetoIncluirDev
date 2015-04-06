<?php

class NotaController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function lancamentoNotasAlunosAction() {
        try {
            $periodo = new Application_Model_Mappers_Periodo();
            $this->view->title = "Projeto Incluir - Lançamento de Notas";
                
            if (!$periodo->verificaFimPeriodo()) {
                $form_lancamento_nota = new Application_Form_FormNotaAluno();

                $mapper_curso = new Application_Model_Mappers_Curso();
                $form_lancamento_nota->initializeCursos($mapper_curso->buscaCursos(array('status' => Application_Model_Curso::status_ativo)));

                $this->view->form = $form_lancamento_nota;

                if ($this->getRequest()->isPost()) {
                    $mapper_turma = new Application_Model_Mappers_Turma();
                    $mapper_atividade = new Application_Model_Mappers_Atividade();

                    $dados = $this->getRequest()->getPost();
                    $notas = null;
                     
                    if (isset($dados['atividade']))
                        $notas = $form_lancamento_nota->getNotas($dados, $mapper_turma->getQuantidadeAlunos((int) base64_decode($dados['turma']), false), $mapper_atividade->buscaAtividadeByID((int) base64_decode($dados['atividade'])));

                    if (is_array($notas) && $form_lancamento_nota->isValid($dados)) {
                        $mapper_alunos = new Application_Model_Mappers_Aluno();
                        $mapper_notas = new Application_Model_Mappers_Nota();
                        $mapper_atividades = new Application_Model_Mappers_Atividade();

                        $turma = (int) base64_decode($form_lancamento_nota->getValue('turma'));

                        if ($mapper_notas->lancamentoNotaAlunos((int) base64_decode($form_lancamento_nota->getValue('atividade')), $notas, $mapper_atividades->getTurmaAtividadesID($turma), $mapper_alunos->getTurmaAlunosID($turma)))
                            $this->view->mensagem = "Nota lançada/alterada com sucesso!";
                        else
                            $this->view->mensagem = "Houve problemas para efetuar o lançamento";

                        $form_lancamento_nota->reset();
                    }
                    else
                        $this->view->mensagem = "Houve problemas para efetuar o lançamento";
                }
            } else
                $this->view->inativo = true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            $this->view->mensagem = "Houve problemas para efetuar o lançamento";
        }
    }

}
