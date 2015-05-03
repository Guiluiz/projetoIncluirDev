<?php

/**
 * Classe para gerenciar as disciplinas do projeto no banco de dados
 * @author Pablo Augusto
 */
class Application_Model_Mappers_Disciplina {

    /**
     * @var Application_Model_DbTable_Disciplina 
     */
    private $db_disciplina;

    /**
     * Adiciona uma nova disciplina no BD
     * @param Application_Model_Disciplina $disciplina
     * @return boolean
     */
    public function addDisciplina($disciplina) {
        try {
            if ($disciplina instanceof Application_Model_Disciplina) {
                $validacao = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'disciplina',
                    'field' => 'nome_disciplina'
                ));

                if ($validacao->isValid($disciplina->getNomeDisciplina())) {
                    $this->db_disciplina = new Application_Model_DbTable_Disciplina();
                    $id_disciplina = $this->db_disciplina->insert($disciplina->parseArray());

                    if ($disciplina->hasPreRequisitos()) {
                        $db_pre_requisitos = new Application_Model_DbTable_DisciplinaPreRequisitos();

                        foreach ($disciplina->getPreRequisitos() as $pre_requisito)
                            $db_pre_requisitos->insert(array('id_disciplina' => $id_disciplina, 'id_disciplina_pre_requisito' => $pre_requisito->getIdDisciplina()));
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
     * Altera um disciplina no BD
     * @param Application_Model_Disciplina $disciplina
     * @return boolean
     */
    public function alterarDisciplina($disciplina) {
        try {
            if ($disciplina instanceof Application_Model_Disciplina) {
                $validacao = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'disciplina',
                    'field' => 'nome_disciplina',
                    'exclude' => array(
                        'field' => 'id_disciplina',
                        'value' => $disciplina->getIdDisciplina()
                    )
                ));

                if ($validacao->isValid($disciplina->getNomeDisciplina())) {
                    $this->db_disciplina = new Application_Model_DbTable_Disciplina();
                    $this->db_disciplina->update($disciplina->parseArray(), $this->db_disciplina->getAdapter()->quoteInto('id_disciplina = ?', $disciplina->getIdDisciplina()));

                    $db_disciplina_pre_requisitos = new Application_Model_DbTable_DisciplinaPreRequisitos();
                    $db_disciplina_pre_requisitos->delete($db_disciplina_pre_requisitos->getAdapter()->quoteInto('id_disciplina = ?', $disciplina->getIdDisciplina()));

                    if ($disciplina->hasPreRequisitos()) {
                        foreach ($disciplina->getPreRequisitos() as $pre_requisito)
                            $db_disciplina_pre_requisitos->insert(array('id_disciplina' => $disciplina->getIdDisciplina(), 'id_disciplina_pre_requisito' => $pre_requisito->getIdDisciplina()));
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
     * Exclui um disciplina do BD
     * @param int $id_disciplina
     * @return boolean
     */
    public function excluirDisciplina($id_disciplina) {
        try {
            $this->db_disciplina = new Application_Model_DbTable_Disciplina();
            $this->db_disciplina->delete($this->db_disciplina->getAdapter()->quoteInto('id_disciplina = ?', (int) $id_disciplina));
            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Altera o status das disciplinas do curso especificado para cancelado
     * @param int $id_curso
     * @return boolean
     */
    public function cancelarDisciplinaByCurso($id_curso) {
        try {
            $this->db_disciplina = new Application_Model_DbTable_Disciplina();
            $this->db_disciplina->update(array('status' => Application_Model_Disciplina::status_inativo), $this->db_disciplina->getAdapter()->quoteInto('id_curso = ?', (int) $id_curso));
            return true;
        } catch (Zend_Exception $e) {
            throw $e;
        }
    }

    /**
     * Altera o status da disciplina especificada para cancelado
     * @param int $id_disciplina
     * @return boolean
     */
    public function cancelarDisciplina($id_disciplina) {
        try {
            $this->db_disciplina = new Application_Model_DbTable_Disciplina();
            $this->db_disciplina->update(array('status' => Application_Model_Disciplina::status_inativo), $this->db_disciplina->getAdapter()->quoteInto('id_disciplina = ?', (int) $id_disciplina));
            return true;
        } catch (Zend_Exception $e) {
            return false;
        }
    }
    
    /**
     * Altera o status da disciplina especificada para ativo
     * @param int $id_disciplina
     * @return boolean
     */
    public function ativarDisciplina($id_disciplina) {
        try {
            $this->db_disciplina = new Application_Model_DbTable_Disciplina();
            $this->db_disciplina->update(array('status' => Application_Model_Disciplina::status_ativo), $this->db_disciplina->getAdapter()->quoteInto('id_disciplina = ?', (int) $id_disciplina));
            return true;
        } catch (Zend_Exception $e) {
            return false;
        }
    }

    
    /**
     * Busca os disciplinas de acordo com os filtros especificados
     * @param array $filtros_busca
     * @param boolean $paginator
     * @return \Application_Model_Disciplina|null|\Zend_Paginator
     */
    public function buscaDisciplinas($filtros_busca = null, $paginator = null, $exclude = null) {
        try {
            $this->db_disciplina = new Application_Model_DbTable_Disciplina();
            $select = $this->db_disciplina->select()
                    ->setIntegrityCheck(false)
                    ->from('disciplina', array('id_disciplina', 'nome_disciplina', 'status'))
                    ->joinInner('curso', 'curso.id_curso = disciplina.id_curso', array('nome_curso'))
                    ->order('disciplina.nome_disciplina ASC');

            if (!empty($exclude))
                $select->where('disciplina.id_disciplina <> ?', $exclude);

            if (!empty($filtros_busca['nome_disciplina']))
                $select->where('disciplina.nome_disciplina LIKE ?', '%' . $filtros_busca['nome_disciplina'] . '%');

            if (!empty($filtros_busca['id_curso']))
                $select->where('disciplina.id_curso = ?', (int) $filtros_busca['id_curso']);

            if (!empty($filtros_busca['status']))
                $select->where('disciplina.status = ?', (int) $filtros_busca['status']);

            if (empty($paginator)) {
                $disciplinas = $this->db_disciplina->fetchAll($select->order('curso.nome_curso'));

                if (!empty($disciplinas)) {
                    $array_disciplinas = array();

                    foreach ($disciplinas as $disciplina)
                        $array_disciplinas[] = new Application_Model_Disciplina($disciplina->id_disciplina, $disciplina->nome_disciplina, null, new Application_Model_Curso(null, $disciplina->nome_curso), $disciplina->status);

                    return $array_disciplinas;
                }
                return null;
            }
            return new Zend_Paginator(new Application_Model_Paginator_Disciplina($select->order('nome_disciplina')));
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Busca o disciplina com o ID especificado
     * @param int $id_disciplina
     * @return \Application_Model_Disciplina|null
     */
    public function buscaDisciplinaByID($id_disciplina) {
        try {
            $this->db_disciplina = new Application_Model_DbTable_Disciplina();
            $select = $this->db_disciplina->select()
                    ->where('id_disciplina = ?', (int) $id_disciplina);

            $disciplina = $this->db_disciplina->fetchRow($select);

            if (!empty($disciplina))
                return new Application_Model_Disciplina($disciplina->id_disciplina, $disciplina->nome_disciplina, $disciplina->ementa_disciplina, new Application_Model_Curso($disciplina->id_curso), $this->getPreRequisitos($disciplina->id_disciplina), $disciplina->status);

            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Busca os pré requisitos ativos da disciplina selecionada
     * @param int $id_disciplina
     * @return null|\Application_Model_Disciplina[]
     */
    public function getPreRequisitos($id_disciplina) {
        try {
            if (!$this->db_disciplina instanceof Application_Model_DbTable_Disciplina)
                $this->db_disciplina = new Application_Model_DbTable_Disciplina();

            $select = $this->db_disciplina->select()
                    ->setIntegrityCheck(false)
                    ->from('disciplina')
                    ->joinInner('disciplina_pre_requisitos', 'disciplina_pre_requisitos.id_disciplina_pre_requisito = disciplina.id_disciplina')
                    ->where('disciplina_pre_requisitos.id_disciplina = ?', (int) $id_disciplina)
                    ->where('disciplina.status = ?', Application_Model_Disciplina::status_ativo);

            $disciplinas = $this->db_disciplina->fetchAll($select);

            if (!empty($disciplinas)) {
                $array_disciplinas = array();

                foreach ($disciplinas as $disciplina)
                    $array_disciplinas[] = new Application_Model_Disciplina($disciplina->id_disciplina_pre_requisito, $disciplina->nome_disciplina, null, new Application_Model_Curso($disciplina->id_curso), $disciplina->status);

                return $array_disciplinas;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Busca as disciplinas com o id's indicados. Utilizado para exibir as disciplinas do professor
     * @param int[] $array_ids
     * @return null|\Application_Model_Disciplina[]
     */
    public function buscaDisciplinasByID($array_ids) {
        try {
            if (!empty($array_ids) && is_array($array_ids)) {
                $this->db_disciplina = new Application_Model_DbTable_Disciplina();
                $select = $this->db_disciplina->select()
                        ->setIntegrityCheck(false)
                        ->from('disciplina', array('id_disciplina', 'nome_disciplina', 'id_curso'))
                        ->joinInner('curso', 'curso.id_curso = disciplina.id_curso', array('nome_curso'));

                $where = "( ";

                foreach ($array_ids as $id)
                    $where .= $this->db_disciplina->getAdapter()->quoteInto('id_disciplina = ?', (int) base64_decode($id)) . " OR ";

                $where = substr($where, 0, -4) . ")";
                $disciplinas = $this->db_disciplina->fetchAll($select->where($where));

                if (!empty($disciplinas)) {
                    $array_disciplinas = array();

                    foreach ($disciplinas as $disciplina)
                        $array_disciplinas[] = new Application_Model_Disciplina($disciplina->id_disciplina, $disciplina->nome_disciplina, null, new Application_Model_Curso($disciplina->id_curso, $disciplina->nome_curso, $disciplina->status));

                    return $array_disciplinas;
                }
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Busca as disciplinas do curso especificado
     * @param int $id_curso
     * @return \Application_Model_Disciplina[]|null
     */
    public function buscaDisciplinasByCurso($id_curso) {
        try {
            $this->db_disciplina = new Application_Model_DbTable_Disciplina();
            $select = $this->db_disciplina->select()
                    ->from('disciplina', array('id_disciplina'))
                    ->where('id_curso = ?', (int) $id_curso);

            $disciplinas = $this->db_disciplina->fetchAll($select);
            $array_disciplinas = array();

            if (!empty($disciplinas)) {
                foreach ($disciplinas as $disciplina)
                    $array_disciplinas[] = $disciplina->id_disciplina;
            }
            
            return $array_disciplinas;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

}
