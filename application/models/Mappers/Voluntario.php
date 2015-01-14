<?php

/**
 * Classe para controle de voluntários no banco de dados
 * @author Projeto Incluir
 */
class Application_Model_Mappers_Voluntario {

    private $db_voluntario;

    public function addVoluntario($voluntario) {
        try {
            if ($voluntario instanceof Application_Model_Voluntario) {
                $validacao = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'voluntario',
                    'field' => 'cpf'
                ));

                if ($validacao->isValid($voluntario->getCpf())) {
                    $this->db_voluntario = new Application_Model_DbTable_Voluntario();

                    $id_voluntario = $this->db_voluntario->insert($voluntario->parseArray());

                    if ($voluntario instanceof Application_Model_Professor) {
                        if ($voluntario->hasDisciplinasMinistradas()) {
                            $db_disciplinas_ministradas = new Application_Model_DbTable_VoluntarioDisciplinas();

                            foreach ($voluntario->getDisciplinasMinistradas() as $disciplina)
                                $db_disciplinas_ministradas->insert(array('id_voluntario' => $id_voluntario, 'id_disciplina' => $disciplina->getIdDisciplina()));
                        }
                    }
                    return true;
                }
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function alterarVoluntario($voluntario) {
        try {
            if ($voluntario instanceof Application_Model_Voluntario) {
                $validacao = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'voluntario',
                    'field' => 'cpf',
                    'exclude' => array(
                        'field' => 'id_voluntario',
                        'value' => $voluntario->getIdVoluntario()
                    )
                ));

                if ($validacao->isValid($voluntario->getCpf())) {
                    $this->db_voluntario = new Application_Model_DbTable_Voluntario();
                    $this->db_voluntario->update($voluntario->parseArray(), $this->db_voluntario->getAdapter()->quoteInto('id_voluntario = ?', $voluntario->getIdVoluntario()));

                    if ($voluntario instanceof Application_Model_Professor) {
                        $db_disciplinas_ministradas = new Application_Model_DbTable_VoluntarioDisciplinas();
                        $db_disciplinas_ministradas->delete($db_disciplinas_ministradas->getAdapter()->quoteInto('id_voluntario = ?', $voluntario->getIdVoluntario()));

                        if ($voluntario->hasDisciplinasMinistradas()) {
                            foreach ($voluntario->getDisciplinasMinistradas() as $disciplina)
                                $db_disciplinas_ministradas->insert(array('id_voluntario' => $voluntario->getIdVoluntario(), 'id_disciplina' => $disciplina->getIdDisciplina()));

                            //remover turmas de disciplinas que o professor não pode dar aula
                        }
                    }
                    return true;
                }
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e;
            return false;
        }
    }

    public function buscaVoluntarios($filtros_busca = null, $paginator = null) {
        try {
            $this->db_voluntario = new Application_Model_DbTable_Voluntario();
            $select = $this->db_voluntario->select()
                    ->from('voluntario', array('id_voluntario', 'nome', 'cpf', 'status'));

            if (!empty($filtros_busca['nome']))
                $select->where('nome LIKE ?', '%' . $filtros_busca['nome'] . '%');

            if (!empty($filtros_busca['cpf']))
                $select->where('cpf = ?', $filtros_busca['cpf']);

            if (empty($paginator)) {
                $voluntarios = $this->db_voluntario->fetchAll($select);
                if (!empty($voluntarios)) {
                    $array_voluntarios = array();

                    foreach ($voluntarios as $voluntario)
                        $array_voluntarios[] = new Application_Model_Voluntario($voluntario->id_voluntario, $voluntario->nome, $voluntario->cpf, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, $voluntario->status);

                    return $array_voluntarios;
                }
                return null;
            }
            return new Zend_Paginator(new Application_Model_Paginator_Voluntario($select->order('nome')));
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function excluirVoluntario($id_voluntario) {
        try {
            $this->db_voluntario = new Application_Model_DbTable_Voluntario();
            $this->db_voluntario->delete($this->db_voluntario->getAdapter()->quoteInto('id_voluntario = ?', (int) $id_voluntario));
            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function buscaVoluntarioByID($id_voluntario, $is_ativo = null) {
        try {
            $this->db_voluntario = new Application_Model_DbTable_Voluntario();
            $select = $this->db_voluntario->select()
                    ->setIntegrityCheck(false)
                    ->from('voluntario')
                    ->joinLeft('voluntario_disciplinas', 'voluntario.id_voluntario = voluntario_disciplinas.id_voluntario', array('id_disciplina'))
                    ->joinLeft('disciplina', 'voluntario_disciplinas.id_disciplina = disciplina.id_disciplina', array('nome_disciplina', 'id_curso'))
                    ->joinLeft('curso', 'disciplina.id_curso = curso.id_curso', array('nome_curso'))
                    ->where('voluntario.id_voluntario = ?', (int) $id_voluntario);

            if (!empty($is_ativo))
                $select->where('voluntario.status = ?', $is_ativo);

            $inf_voluntario = $this->db_voluntario->fetchAll($select);

            if (!empty($inf_voluntario)) {
                $tipos_atividades = array(Application_Model_Voluntario::$atividade_informatica => 'funcao_informatica', Application_Model_Voluntario::$atividade_marketing => 'funcao_marketing', Application_Model_Voluntario::$atividade_rh => 'funcao_rh', Application_Model_Voluntario::$atividade_secretaria => 'funcao_secretaria');
                $array_voluntario = array();

                foreach ($inf_voluntario as $inf) {
                    if (empty($array_voluntario[$inf->id_voluntario])) {
                        if (!empty($inf->id_disciplina))
                            $array_voluntario[$inf->id_voluntario] = new Application_Model_Professor($inf->id_voluntario, $inf->nome, $inf->cpf, $inf->rg, $inf->data_nascimento, $inf->email, $inf->formacao, $inf->profissao, $inf->telefone_fixo, $inf->telefone_celular, $inf->endereco, $inf->bairro, $inf->cidade, $inf->estado, $inf->numero, $inf->complemento, $inf->cep, $inf->carga_horaria, $inf->data_inicio, $inf->data_desligamento, $inf->motivo_desligamento, $inf->status, $inf->conhecimento, Application_Model_Voluntario::getUnserializeData($inf->disponibilidade), new Application_Model_Disciplina($inf->id_disciplina, $inf->nome_disciplina, null, new Application_Model_Curso($inf->id_curso, $inf->nome_curso)), $inf->funcao_informatica, Application_Model_Voluntario::$atividade_informatica);
                        else
                            $array_voluntario[$inf->id_voluntario] = new Application_Model_Voluntario($inf->id_voluntario, $inf->nome, $inf->cpf, $inf->rg, $inf->data_nascimento, $inf->email, $inf->formacao, $inf->profissao, $inf->telefone_fixo, $inf->telefone_celular, $inf->endereco, $inf->bairro, $inf->cidade, $inf->estado, $inf->numero, $inf->complemento, $inf->cep, $inf->carga_horaria, $inf->data_inicio, $inf->data_desligamento, $inf->motivo_desligamento, $inf->status, $inf->conhecimento, Application_Model_Voluntario::getUnserializeData($inf->disponibilidade), $inf->funcao_informatica, Application_Model_Voluntario::$atividade_informatica);

                        foreach ($tipos_atividades as $key => $tipo)
                            $array_voluntario[$inf->id_voluntario]->addFuncao($inf->$tipo, $key);
                    }
                    else if ($array_voluntario[$inf->id_voluntario] instanceof Application_Model_Professor)
                        $array_voluntario[$inf->id_voluntario]->addDisciplinasMinistradas(new Application_Model_Disciplina($inf->id_disciplina, $inf->nome_disciplina, null, new Application_Model_Curso($inf->id_curso, $inf->nome_curso)));
                }
                return $array_voluntario[$inf->id_voluntario];
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function getProfessoresByDisciplina($id_disciplina) {
        try {
            $this->db_voluntario = new Application_Model_DbTable_Voluntario();

            $select = $this->db_voluntario->select()
                    ->setIntegrityCheck(false)
                    ->from('voluntario', array('id_voluntario', 'nome'))
                    ->joinInner('voluntario_disciplinas', 'voluntario.id_voluntario = voluntario_disciplinas.id_voluntario', array('id_disciplina'))
                    ->where('voluntario.status = ?', Application_Model_Voluntario::$status_ativo);

            if (!empty($id_disciplina))
                $select->where('voluntario_disciplinas.id_disciplina = ?', (int) $id_disciplina);

            $inf_professor = $this->db_voluntario->fetchAll($select->group('voluntario.id_voluntario'));

            if (!empty($inf_professor)) {
                $array_professores = array();

                foreach ($inf_professor as $inf) {
                    if (empty($array_professores[$inf->id_voluntario]))
                        $array_professores[$inf->id_voluntario] = new Application_Model_Professor($inf->id_voluntario, $inf->nome);
                }
                return $array_professores;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function getProfessoresByIDs($array_ids) {
        try {
            if (!empty($array_ids) && is_array($array_ids)) {
                $this->db_voluntario = new Application_Model_DbTable_Voluntario();
                $select = $this->db_voluntario->select()
                        ->setIntegrityCheck(false)
                        ->from('voluntario', array('nome'))
                        ->joinInner('voluntario_disciplinas', 'voluntario.id_voluntario = voluntario_disciplinas.id_voluntario', array('id_voluntario'));

                $where = "( ";

                foreach ($array_ids as $id)
                    $where .= $this->db_voluntario->getAdapter()->quoteInto('voluntario_disciplinas.id_voluntario = ?', (int) base64_decode($id)) . " OR ";

                $where = substr($where, 0, -4) . ")";
                $inf_professor = $this->db_voluntario->fetchAll($select->where($where));

                if (!empty($inf_professor)) {
                    $array_professores = array();

                    foreach ($inf_professor as $inf) {
                        if (empty($array_professores[$inf->id_voluntario]))
                            $array_professores[$inf->id_voluntario] = new Application_Model_Professor($inf->id_voluntario, $inf->nome);
                    }
                    return $array_professores;
                }
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function desligarVoluntario($voluntario) {
        try {
            if ($voluntario instanceof Application_Model_Voluntario) {
                $this->db_voluntario = new Application_Model_DbTable_Voluntario();
                $this->db_voluntario->update($voluntario->parseArrayDesligamento(), $this->db_voluntario->getAdapter()->quoteInto('id_voluntario = ?', $voluntario->getIdVoluntario()));

                $mapper_turma = new Application_Model_Mappers_Turma();
                $id_turmas = $mapper_turma->getTurmasProfessorPeriodoAtual($voluntario->getIdVoluntario());

                if (!empty($id_turmas)) {
                    $db_turmas_ministradas = new Application_Model_DbTable_VoluntarioTurmas();
                    $where = "( ";

                    foreach ($id_turmas as $id_turma)
                        $where .= $this->db_reserva->getAdapter()->quoteInto('id_turma = ?', $id_turma) . " OR ";

                    $where .= substr($where, 0, -4) . ")";
                    $db_turmas_ministradas->delete($where);
                }

                return true;
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e;
            return false;
        }
    }

    public function verificaVoluntarioNome($nome) {
        try {
            $this->db_voluntario = new Application_Model_DbTable_Voluntario();
            $select = $this->db_voluntario
                    ->select()
                    ->from('voluntario', array('id_voluntario', 'nome', 'cpf'))
                    ->where('removeInvalidsCharacters(nome) LIKE ?', '%' . $nome . '%');

            $voluntarios = $this->db_voluntario->fetchAll($select);

            if (!empty($voluntarios)) {
                $url_helper = new Zend_View_Helper_Url();
                $array_voluntarios = array();

                foreach ($voluntarios as $voluntario) {
                    $array_voluntarios[$voluntario->id_voluntario]['label'] = $voluntario->nome . ' | ' . $voluntario->cpf;
                    $array_voluntarios[$voluntario->id_voluntario]['value'] = '';
                    $array_voluntarios[$voluntario->id_voluntario]['url'] = $url_helper->url(array('controller' => 'voluntario', 'action' => 'alterar', 'voluntario' => base64_encode($voluntario->id_voluntario)));
                }
                return $array_voluntarios;
            }
            return null;
        } catch (Zend_Exception $ex) {
            echo $ex->getMessage();
            return null;
        }
    }

    public function ativarVoluntario($id_voluntario) {
        try {
            $this->db_voluntario = new Application_Model_DbTable_Voluntario();
            $this->db_voluntario->update(Application_Model_Voluntario::parseArrayAtivacao(), $this->db_voluntario->getAdapter()->quoteInto('id_voluntario = ?', (int) $id_voluntario));
            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getAllVoluntarios() {
        try {
            $this->db_voluntario = new Application_Model_DbTable_Voluntario();
            $select = $this->db_voluntario->select()
                    ->setIntegrityCheck(false)
                    ->from('voluntario')
                    ->joinLeft('voluntario_disciplinas', 'voluntario.id_voluntario = voluntario_disciplinas.id_voluntario', array('id_disciplina'))
                    ->joinLeft('disciplina', 'voluntario_disciplinas.id_disciplina = disciplina.id_disciplina', array('nome_disciplina', 'id_curso'))
                    ->joinLeft('curso', 'disciplina.id_curso = curso.id_curso', array('nome_curso'))
                    ->joinLeft('escala_frequencia_voluntario', 'escala_frequencia_voluntario.id_voluntario = voluntario.id_voluntario', array('id_frequencia', 'hora_entrada', 'hora_saida', 'data_funcionamento', 'is_presente'))
                    ->where('voluntario.status = ?', Application_Model_Voluntario::$status_ativo)
                    ->order('nome ASC');

            $inf_voluntario = $this->db_voluntario->fetchAll($select);

            if (!empty($inf_voluntario)) {
                $tipos_atividades = array(Application_Model_Voluntario::$atividade_informatica => 'funcao_informatica', Application_Model_Voluntario::$atividade_marketing => 'funcao_marketing', Application_Model_Voluntario::$atividade_rh => 'funcao_rh', Application_Model_Voluntario::$atividade_secretaria => 'funcao_secretaria');

                $array_voluntario = array();
                $array_frequencia = array();

                foreach ($inf_voluntario as $inf) {
                    if (empty($array_voluntario[$inf->id_voluntario])) {
                        if (!empty($inf->id_disciplina))
                            $array_voluntario[$inf->id_voluntario] = new Application_Model_Professor($inf->id_voluntario, $inf->nome, $inf->cpf, $inf->rg, $inf->data_nascimento, $inf->email, $inf->formacao, $inf->profissao, $inf->telefone_fixo, $inf->telefone_celular, $inf->endereco, $inf->bairro, $inf->cidade, $inf->estado, $inf->numero, $inf->complemento, $inf->cep, $inf->carga_horaria, $inf->data_inicio, $inf->data_desligamento, $inf->motivo_desligamento, $inf->status, $inf->conhecimento, Application_Model_Voluntario::getUnserializeData($inf->disponibilidade), new Application_Model_Disciplina($inf->id_disciplina, $inf->nome_disciplina, null, new Application_Model_Curso($inf->id_curso, $inf->nome_curso)), $inf->funcao_informatica, Application_Model_Voluntario::$atividade_informatica);
                        else
                            $array_voluntario[$inf->id_voluntario] = new Application_Model_Voluntario($inf->id_voluntario, $inf->nome, $inf->cpf, $inf->rg, $inf->data_nascimento, $inf->email, $inf->formacao, $inf->profissao, $inf->telefone_fixo, $inf->telefone_celular, $inf->endereco, $inf->bairro, $inf->cidade, $inf->estado, $inf->numero, $inf->complemento, $inf->cep, $inf->carga_horaria, $inf->data_inicio, $inf->data_desligamento, $inf->motivo_desligamento, $inf->status, $inf->conhecimento, Application_Model_Voluntario::getUnserializeData($inf->disponibilidade), $inf->funcao_informatica, Application_Model_Voluntario::$atividade_informatica);

                        foreach ($tipos_atividades as $key => $tipo)
                            $array_voluntario[$inf->id_voluntario]->addFuncao($inf->$tipo, $key);
                    }
                    else if ($array_voluntario[$inf->id_voluntario] instanceof Application_Model_Professor)
                        $array_voluntario[$inf->id_voluntario]->addDisciplinasMinistradas(new Application_Model_Disciplina($inf->id_disciplina, $inf->nome_disciplina, null, new Application_Model_Curso($inf->id_curso, $inf->nome_curso)));

                    if (!isset($array_frequencia[$inf->id_voluntario][$inf->id_frequencia]))
                        $array_frequencia[$inf->id_voluntario][$inf->id_frequencia] = new Application_Model_EscalaFrequencia($inf->id_frequencia, $inf->is_presente, $inf->data_funcionamento, $inf->hora_entrada, $inf->hora_saida);
                }

                //var_dump($array_frequencia);

                if (!empty($array_frequencia)) {
                    foreach ($array_frequencia as $id_voluntario => $frequencia)
                        $array_voluntario[$id_voluntario]->addFrequencia($frequencia);
                }

                return $array_voluntario;
            }
            return null;
        } catch (Zend_Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function getCountVoluntarios($setor = null) {
        try {
            $this->db_voluntario = new Application_Model_DbTable_Voluntario();
            $select = $this->db_voluntario->select();

            if (!empty($setor))
                $select->where('');

            return $this->db_voluntario->fetchAll($select)->count();
        } catch (Zend_Exception $ex) {
            echo $ex->getMessage();
            return null;
        }
    }

}
