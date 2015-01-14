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
                    ->from('disciplina', array('id_disciplina', 'nome_disciplina'))
                    ->joinInner('curso', 'curso.id_curso = disciplina.id_curso', array('nome_curso'));

            if (!empty($exclude))
                $select->where('disciplina.id_disciplina <> ?', $exclude);

            if (!empty($filtros_busca['nome_disciplina']))
                $select->where('disciplina.nome_disciplina LIKE ?', '%' . $filtros_busca['nome_disciplina'] . '%');

            if (!empty($filtros_busca['id_curso']))
                $select->where('disciplina.id_curso = ?', (int) $filtros_busca['id_curso']);

            if (empty($paginator)) {
                $disciplinas = $this->db_disciplina->fetchAll($select->order('curso.nome_curso'));
                if (!empty($disciplinas)) {
                    $array_disciplinas = array();

                    foreach ($disciplinas as $disciplina)
                        $array_disciplinas[] = new Application_Model_Disciplina($disciplina->id_disciplina, $disciplina->nome_disciplina, null, new Application_Model_Curso(null, $disciplina->nome_curso));

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
                return new Application_Model_Disciplina($disciplina->id_disciplina, $disciplina->nome_disciplina, $disciplina->ementa_disciplina, new Application_Model_Curso($disciplina->id_curso), $this->getPreRequisitos($disciplina->id_disciplina));

            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function getPreRequisitos($id_disciplina) {
        try {
            if (!$this->db_disciplina instanceof Application_Model_DbTable_Disciplina)
                $this->db_disciplina = new Application_Model_DbTable_Disciplina();

            $select = $this->db_disciplina->select()
                    ->setIntegrityCheck(false)
                    ->from('disciplina')
                    ->joinInner('disciplina_pre_requisitos', 'disciplina_pre_requisitos.id_disciplina_pre_requisito = disciplina.id_disciplina')
                    ->where('disciplina_pre_requisitos.id_disciplina = ?', (int) $id_disciplina);

            $disciplinas = $this->db_disciplina->fetchAll($select);

            if (!empty($disciplinas)) {
                $array_disciplinas = array();

                foreach ($disciplinas as $disciplina)
                    $array_disciplinas[] = new Application_Model_Disciplina($disciplina->id_disciplina_pre_requisito, $disciplina->nome_disciplina, null, new Application_Model_Curso($disciplina->id_curso));

                return $array_disciplinas;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

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
                        $array_disciplinas[] = new Application_Model_Disciplina($disciplina->id_disciplina, $disciplina->nome_disciplina, null, new Application_Model_Curso($disciplina->id_curso, $disciplina->nome_curso));

                    return $array_disciplinas;
                }
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

}
