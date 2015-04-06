<?php

/**
 * Classe para controle das turmas no banco de dados
 * @author Projeto Incluir
 */
class Application_Model_Mappers_Turma {

    private $db_turma;

    /**
     * Inclui a turma especificada no banco de dados
     * @param Application_Model_Turma $turma
     * @return boolean
     */
    public function addTurma($turma) {
        try {
            if ($turma instanceof Application_Model_Turma) {
                if ($this->isValid($turma)) {
                    if (!$this->db_turma instanceof Application_Model_DbTable_Turma)
                        $this->db_turma = new Application_Model_DbTable_Turma();

                    $id_turma = $this->db_turma->insert($turma->parseArray());
                    $db_turma_professores = new Application_Model_DbTable_VoluntarioTurmas();

                    if ($turma->hasProfessores()) {
                        foreach ($turma->getProfessores() as $professor)
                            $db_turma_professores->insert(array('id_turma' => $id_turma, 'id_voluntario' => $professor->getIdVoluntario()));
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

    /**
     * Altera a turma especificada no banco de dados
     * @param Application_Model_Turma $turma
     * @return boolean
     */
    public function alterarTurma($turma) {
        try {
            if ($turma instanceof Application_Model_Turma) {
                if ($this->isValid($turma)) {
                    if (!$this->db_turma instanceof Application_Model_DbTable_Turma)
                        $this->db_turma = new Application_Model_DbTable_Turma();

                    $this->db_turma->update($turma->parseArray(), $this->db_turma->getAdapter()->quoteInto('id_turma = ?', $turma->getIdTurma()));

                    $db_turma_professores = new Application_Model_DbTable_VoluntarioTurmas();
                    $db_turma_professores->delete($db_turma_professores->getAdapter()->quoteInto('id_turma = ?', $turma->getIdTurma()));

                    if ($turma->hasProfessores()) {
                        foreach ($turma->getProfessores() as $professor)
                            $db_turma_professores->insert(array('id_turma' => $turma->getIdTurma(), 'id_voluntario' => $professor->getIdVoluntario()));
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

    /**
     * Exclui a turma com o id especificado do banco de dados
     * @param int $id_turma
     * @return boolean
     */
    public function excluirTurma($id_turma) {
        try {
            $this->db_turma = new Application_Model_DbTable_Turma();
            $this->db_turma->delete($this->db_turma->getAdapter()->quoteInto('id_turma = ?', (int) $id_turma));
            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Altera o status das turmas da disciplina especificada para cancelado
     * @param int $ids_disciplinas
     * @return boolean
     */
    public function cancelarTurmasByDisciplinas($ids_disciplinas) {
        try {
            if (!empty($ids_disciplinas)) {
                $this->db_turma = new Application_Model_DbTable_Turma();

                $where = "( ";

                foreach ($ids_disciplinas as $id)
                    $where .= $this->db_turma->getAdapter()->quoteInto('id_disciplina = ?', (int) $id) . " OR ";

                $where = substr($where, 0, -4) . ")";
                $this->db_turma->update(array('status' => Application_Model_Turma::$status_cancelada), $where);
            }
            return true;
        } catch (Zend_Exception $e) {
            return false;
        }
    }

    /**
     * Altera o status da turma especificada para cancelado
     * @param int $id_turma
     * @return boolean
     */
    public function cancelarTurma($id_turma) {
        try {
            $this->db_turma = new Application_Model_DbTable_Turma();
            $this->db_turma->update(array('status' => Application_Model_Turma::$status_cancelada), $this->db_turma->getAdapter()->quoteInto('id_turma = ?', (int) $id_turma));
            return true;
        } catch (Zend_Exception $e) {
            return false;
        }
    }

    /**
     * Retorna a quantidade de alunos da(s) turma(s) e outras informações de acordo com o parâmetro
     * @param int|null $id_turma Indica a turma que terá a quantidade retornada, caso seja nulo as quantidades de todas as turmas são buscadas
     * @param boolean $complete Indica se informações sobre a disciplina serao retornadas (utilizada para exibição de quantidades para usuário, verificação de quantidade de lançamentos de notas e frequencia, etc)
     * @return array|int (retorna int quando é apenas uma turma e o $complete é false)
     */
    public function getQuantidadeAlunos($id_turma = null, $complete = true) {
        try {
            $this->db_turma = new Application_Model_DbTable_Turma();
            if ($complete) {
                $select = $this->db_turma->select()
                        ->setIntegrityCheck(false)
                        ->from('turma', array('nome_turma', 'id_periodo'))
                        ->joinInner('disciplina', 'turma.id_disciplina = disciplina.id_disciplina', array('nome_disciplina'))
                        ->joinleft('turma_alunos', 'turma.id_turma = turma_alunos.id_turma', array('count(aluno.id_aluno)'))
                        ->joinLeft('aluno', 'turma_alunos.id_aluno = aluno.id_aluno AND ' . $this->db_turma->getDefaultAdapter()->quoteInto('aluno.status = ?', Application_Model_Aluno::$status_ativo), array())
                        ->order(array('disciplina.nome_disciplina ASC', 'turma.nome_turma ASC'))
                        ->group('turma.id_turma');

                if (!empty($id_turma))
                    $select->where('turma.id_turma = ?', (int) $id_turma);

                return $this->db_turma->fetchAll($select)->toArray();
            }

            else {
                $select = $this->db_turma->select()
                        ->setIntegrityCheck(false)
                        ->joinleft('turma_alunos', 'turma.id_turma = turma_alunos.id_turma', array('id_aluno'))
                        ->joinLeft('aluno', 'turma_alunos.id_aluno = aluno.id_aluno', array())
                        ->where('aluno.status = ?', Application_Model_Aluno::$status_ativo);

                if (!empty($id_turma)) {
                    $select->where('turma_alunos.id_turma = ?', (int) $id_turma);
                    return $this->db_turma->fetchAll($select)->count();
                } else
                    return $this->db_turma->fetchAll($select)->toArray();
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna a quantidade de alunos de todas as turmas
     * Utilizado para finalizar o período
     * @return null
     */
    public function getQuantidadeAlunosByPeriodo($id_periodo) {
        try {
            $this->db_turma = new Application_Model_DbTable_Turma();
            $select = $this->db_turma->select()
                    ->setIntegrityCheck(false)
                    ->from('turma', array('id_turma'))
                    ->joinleft('turma_alunos', 'turma.id_turma = turma_alunos.id_turma', array('count(aluno.id_aluno) as quantidade'))
                    ->joinLeft('aluno', 'turma_alunos.id_aluno = aluno.id_aluno AND ' . $this->db_turma->getDefaultAdapter()->quoteInto('aluno.status = ?', Application_Model_Aluno::$status_ativo), array())
                    ->group('turma.id_turma');

            if (!empty($id_periodo))
                $select->where('turma.id_periodo = ?', (int) $id_periodo);

            $quantidades = $this->db_turma->fetchAll($select);

            if (!empty($quantidades)) {
                $array_quantidades = array();

                foreach ($quantidades as $quantidade)
                    $array_quantidades[$quantidade->id_turma] = $quantidade->quantidade;

                return $array_quantidades;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Busca as turmas de acordo com os filtros indicados
     * @param type $filtros_busca
     * @param type $paginator
     * @return \Zend_Paginator|\Application_Model_Turma|null
     */
    public function buscaTurmas($filtros_busca = null, $paginator = null) {
        try {
            $this->db_turma = new Application_Model_DbTable_Turma();
            $select = $this->db_turma->select()
                    ->setIntegrityCheck(false)
                    ->from('turma', array('id_turma', 'data_inicio', 'data_fim', 'horario_inicio', 'horario_fim', 'nome_turma', 'id_disciplina', 'status', 'id_periodo'))
                    ->joinInner('disciplina', 'disciplina.id_disciplina = turma.id_disciplina', array('nome_disciplina'))
                    ->order('turma.nome_turma ASC');

            if (!empty($filtros_busca['nome_turma']))
                $select->where('turma.nome_turma LIKE ?', '%' . $filtros_busca['nome_turma'] . '%');

            if (!empty($filtros_busca['disciplina']))
                $select->where('turma.id_disciplina = ?', (int) base64_decode($filtros_busca['disciplina']));

            if (!empty($filtros_busca['status']))
                $select->where('turma.status = ?', $filtros_busca['status']);

            if (!empty($filtros_busca['periodo']))
                $select->where('turma.id_periodo = ?', (int) $filtros_busca['periodo']);

            if (empty($paginator)) {
                $turmas = $this->db_turma->fetchAll($select);
                if (!empty($turmas)) {
                    $array_turmas = array();

                    foreach ($turmas as $turma)
                        $array_turmas[$turma->id_turma] = new Application_Model_Turma($turma->id_turma, $turma->nome_turma, $turma->data_inicio, $turma->data_fim, $turma->horario_inicio, $turma->horario_fim, new Application_Model_Disciplina($turma->id_disciplina, $turma->nome_disciplina), $turma->status, null, new Application_Model_Periodo($turma->id_periodo));

                    return $array_turmas;
                }
                return null;
            }
            return new Zend_Paginator(new Application_Model_Paginator_Turma($select->order('nome_turma')));
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna a turma de acordo com o id especificado. Os campos de período e ativa,
     * são necessários para evitar a alteração de turmas canceladas ou de períodos passados.
     * @param int $id_turma
     * @param int|null $periodo 
     * @param int|null $ativa
     * @return \Application_Model_Turma|null
     */
    public function buscaTurmaByID($id_turma, $periodo = null, $ativa = null) {
        try {
            $this->db_turma = new Application_Model_DbTable_Turma();
            $select = $this->db_turma->select()
                    ->setIntegrityCheck(false)
                    ->from('turma')
                    ->joinInner('disciplina', 'disciplina.id_disciplina = turma.id_disciplina', array('nome_disciplina', 'id_curso'))
                    ->joinLeft('voluntario_turmas', 'turma.id_turma = voluntario_turmas.id_turma', array('id_voluntario'))
                    ->joinLeft('voluntario', 'voluntario_turmas.id_voluntario = voluntario.id_voluntario', array('nome'))
                    ->where('turma.id_turma = ?', (int) $id_turma);

            if (!empty($ativa))
                $select->where('turma.status = ?', Application_Model_Turma::$status_iniciada);

            if (!empty($periodo))
                $select->where('turma.id_periodo = ?', (int) $periodo);

            $turma = $this->db_turma->fetchAll($select);

            if (!empty($turma)) {
                $array_turmas = array();
                $id_turma = 0;

                foreach ($turma as $inf_turma) {
                    if (empty($array_turmas[$inf_turma->id_turma]))
                        $array_turmas[$inf_turma->id_turma] = new Application_Model_Turma($inf_turma->id_turma, $inf_turma->nome_turma, $inf_turma->data_inicio, $inf_turma->data_fim, $inf_turma->horario_inicio, $inf_turma->horario_fim, new Application_Model_Disciplina($inf_turma->id_disciplina, $inf_turma->nome_disciplina, null, new Application_Model_Curso($inf_turma->id_curso)), $inf_turma->status, ((!empty($inf_turma->id_voluntario)) ? new Application_Model_Professor($inf_turma->id_voluntario, $inf_turma->nome) : null), new Application_Model_Periodo($inf_turma->id_periodo), $inf_turma->sala);
                    else {
                        if (!empty($inf_turma->id_voluntario))
                            $array_turmas[$inf_turma->id_turma]->addProfessor(new Application_Model_Professor($inf_turma->id_voluntario, $inf_turma->nome));
                    }
                    $id_turma = $inf_turma->id_turma;
                }

                if (!empty($array_turmas))
                    return $array_turmas[$id_turma];
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna as turmas com os id's indicados por parâmetro. 
     * Utilizado para popular as turmas selecionadas para o aluno no cadastro (quando há erro no cadastro, as tabelas com as turmas escolhidas devem ser inseridas)
     * @param array $array_ids
     * @return \Application_Model_Turma|null
     */
    public function buscaTurmasByID($array_ids) {
        try {
            if (!empty($array_ids) && is_array($array_ids)) {
                $this->db_turma = new Application_Model_DbTable_Turma();
                $select = $this->db_turma->select()
                        ->setIntegrityCheck(false)
                        ->from('turma', array('id_turma', 'nome_turma', 'id_disciplina', 'id_periodo', 'horario_inicio', 'horario_fim'))
                        ->joinInner('disciplina', 'turma.id_disciplina = disciplina.id_disciplina', array('nome_disciplina', 'id_curso'))
                        ->joinInner('curso', 'disciplina.id_curso = curso.id_curso', array('nome_curso'));

                $where = "( ";

                foreach ($array_ids as $id)
                    $where .= $this->db_turma->getAdapter()->quoteInto('turma.id_turma = ?', (int) base64_decode($id)) . " OR ";

                $where = substr($where, 0, -4) . ")";
                $turmas = $this->db_turma->fetchAll($select->where($where));

                if (!empty($turmas)) {
                    $array_turmas = array();

                    foreach ($turmas as $turma)
                        $array_turmas[$turma->id_turma] = new Application_Model_Turma($turma->id_turma, $turma->nome_turma, null, null, $turma->horario_inicio, $turma->horario_fim, new Application_Model_Disciplina($turma->id_disciplina, $turma->nome_disciplina, null, new Application_Model_Curso($turma->id_curso, $turma->nome_curso)), null, new Application_Model_Periodo($turma->id_periodo));

                    return $array_turmas;
                }
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Verifica se há turmas do semestre atual e da mesma disciplina com o nome indicado.
     * @param Application_Model_Turma $turma
     * @return boolean
     */
    private function isValid($turma) {
        try {
            if ($turma instanceof Application_Model_Turma) {
                if (!$this->db_turma instanceof Application_Model_DbTable_Turma)
                    $this->db_turma = new Application_Model_DbTable_Turma();

                $select = $this->db_turma->select()
                        ->setIntegrityCheck(false)
                        ->from('turma', array('id_turma', 'nome_turma', 'id_disciplina'))
                        ->where($this->db_turma->getAdapter()->quoteInto('(turma.nome_turma = ? AND ', $turma->getNomeTurma()) .
                        $this->db_turma->getAdapter()->quoteInto('turma.id_disciplina = ? AND ', $turma->getDisciplina()->getIdDisciplina()) .
                        $this->db_turma->getAdapter()->quoteInto('turma.id_periodo = ?)', $turma->getPeriodo()->getIdPeriodo())
                );

                if (!is_null($turma->getIdTurma()))
                    $select->where('turma.id_turma <> ?', $turma->getIdTurma());

                if (count($this->db_turma->fetchAll($select)->toArray()) > 0)
                    return false;
                return true;
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return true;
        }
    }

    /**
     * Busca as turmas do período indicado por parâmetro
     * @param type $periodo 
     * @return \Application_Model_Turma|null
     */
    public function buscaTurmasSimples($periodo = null) {
        try {
            $this->db_turma = new Application_Model_DbTable_Turma();
            $select = $this->db_turma->select()
                    ->setIntegrityCheck(false)
                    ->from('turma', array('id_turma', 'nome_turma', 'id_disciplina', 'id_periodo'))
                    ->joinInner('disciplina', 'disciplina.id_disciplina = turma.id_disciplina', array('nome_disciplina'))
                    ->order(array('disciplina.nome_disciplina ASC', 'turma.nome_turma ASC'));

            if (!empty($periodo))
                $select->where('turma.id_periodo = ?', $periodo);

            $turmas = $this->db_turma->fetchAll($select);

            if (!empty($turmas)) {
                $array_turmas = array();

                foreach ($turmas as $turma)
                    $array_turmas[$turma->id_turma] = new Application_Model_Turma($turma->id_turma, $turma->nome_turma, null, null, null, null, new Application_Model_Disciplina($turma->id_disciplina, $turma->nome_disciplina), null, new Application_Model_Periodo($turma->id_periodo));

                return $array_turmas;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retornas as turmas do período atual do professor com o id passado por parâmetro
     * @param type $id_voluntario
     * @return null
     */
    public function getTurmasProfessorPeriodoAtual($id_voluntario) {
        try {
            $id_voluntario = (int) $id_voluntario;

            if ($id_voluntario > 0) {
                $this->db_turma = new Application_Model_DbTable_Turma();
                $select = $this->db_turma->select()
                        ->from('turma', array('id_turma'))
                        ->innerJoin('periodo', 'periodo.id_periodo = turma.id_periodo', array())
                        ->innerJoin('voluntario_turmas', 'turma.id_turma = voluntario_turmas.id_turma', array())
                        ->where('periodo.is_atual = ?', true)
                        ->where('voluntario_turmas.id_voluntario = ?', $id_voluntario);

                return $this->db_turma->fetchAll($select)->toArray();
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Busca as turmas de uma disciplina de acordo com os horários especificados. Usado para redistribuir alunos 
     * nas turmas
     * @param int $id_disciplina
     * @param string $horario_inicio
     * @param string $horario_termino
     * @return \Application_Model_Turma|null
     */
    public function getTurmasByDisciplinaHorario($id_disciplina, $horario_inicio, $horario_termino) {
        try {
            $this->db_turma = new Application_Model_DbTable_Turma();
            $select = $this->db_turma
                    ->select()
                    ->from('turma', array('id_turma', 'nome_turma'))
                    ->joinInner('periodo', 'periodo.id_periodo = turma.id_periodo', array())
                    ->where('periodo.is_atual = ?', true)
                    ->where('turma.id_disciplina = ?', $id_disciplina)
                    ->where('turma.horario_inicio = ?', $horario_inicio)
                    ->where('turma.horario_fim = ?', $horario_termino)
                    ->order('turma.nome_turma');

            $turmas = $this->db_turma->fetchAll($select);

            if (!empty($turmas)) {
                $array_turmas = array();

                foreach ($turmas as $turma)
                    $array_turmas[] = new Application_Model_Turma($turma->id_turma, $turma->nome_turma);

                return $array_turmas;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna as turmas das disciplinas indicadas por parâmetro
     * @param array $array_ids_disciplinas Array de id's de disciplinas
     * @return \Application_Model_Turma|null
     */
    public function getTurmasByDisciplinas($array_ids_disciplinas, $inclui_turmas_periodo_atual = false) {
        try {
            if (!empty($array_ids_disciplinas) && is_array($array_ids_disciplinas)) {
                $this->db_turma = new Application_Model_DbTable_Turma();
                $select = $this->db_turma->select()
                        ->setIntegrityCheck(false)
                        ->from('turma', array('id_turma', 'nome_turma', 'id_disciplina', 'id_periodo'))
                        ->joinInner('periodo', 'turma.id_periodo = periodo.id_periodo', array('nome_periodo'))
                        ->joinInner('disciplina', 'turma.id_disciplina = disciplina.id_disciplina', array('nome_disciplina', 'id_curso'))
                        ->joinInner('curso', 'disciplina.id_curso = curso.id_curso', array('nome_curso'));

                if (!$inclui_turmas_periodo_atual)
                    $select->where('periodo.is_atual = ?', false);

                $where = "( ";

                foreach ($array_ids_disciplinas as $id)
                    $where .= $this->db_turma->getAdapter()->quoteInto('turma.id_disciplina = ?', (int) $id) . " OR ";

                $where = substr($where, 0, -4) . ")";
                $turmas = $this->db_turma->fetchAll($select->where($where));

                if (!empty($turmas)) {
                    $array_turmas = array();

                    foreach ($turmas as $turma)
                        $array_turmas[$turma->id_turma] = new Application_Model_Turma($turma->id_turma, $turma->nome_turma, null, null, null, null, new Application_Model_Disciplina($turma->id_disciplina, $turma->nome_disciplina, null, new Application_Model_Curso($turma->id_curso, $turma->nome_curso)), null, new Application_Model_Periodo($turma->id_periodo));

                    return $array_turmas;
                }
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

}
