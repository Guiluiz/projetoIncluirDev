<?php

/**
 * Classe para gerenciar os cursos do projeto no banco de dados
 * @author Pablo Augusto
 */
class Application_Model_Mappers_Curso {

    /**
     * @var Application_Model_DbTable_Curso 
     */
    private $db_curso;

    /**
     * Adiciona um novo curso no BD
     * @param Application_Model_Curso $curso
     * @return boolean
     */
    public function addCurso($curso) {
        try {
            if ($curso instanceof Application_Model_Curso) {
                $validacao = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'curso',
                    'field' => 'nome_curso'
                ));

                if ($validacao->isValid($curso->getNomeCurso())) {
                    $this->db_curso = new Application_Model_DbTable_Curso();
                    $this->db_curso->insert($curso->parseArray());
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
     * Altera um curso no BD
     * @param Application_Model_Curso $curso
     * @return boolean
     */
    public function alterarCurso($curso) {
        try {
            if ($curso instanceof Application_Model_Curso) {
                $validacao = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'curso',
                    'field' => 'nome_curso',
                    'exclude' => array(
                        'field' => 'id_curso',
                        'value' => $curso->getIdCurso()
                    )
                ));
                if ($validacao->isValid($curso->getNomeCurso())) {
                    $this->db_curso = new Application_Model_DbTable_Curso();
                    $this->db_curso->update($curso->parseArray(), $this->db_curso->getAdapter()->quoteInto('id_curso = ?', $curso->getIdCurso()));
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
     * Exclui um curso do BD
     * @param int $id_curso
     * @return boolean
     */
    public function excluirCurso($id_curso) {
        try {
            $this->db_curso = new Application_Model_DbTable_Curso();
            $this->db_curso->delete($this->db_curso->getAdapter()->quoteInto('id_curso = ?', (int) $id_curso));
            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }
    
    /**
     * Altera o status do curso especificado para cancelado
     * @param int $id_curso
     * @return boolean
     */
    public function cancelarCurso($id_curso) {
        try {
            $this->db_curso = new Application_Model_DbTable_Curso();
            $this->db_curso->update(array('status' => Application_Model_Curso::status_inativo), $this->db_curso->getAdapter()->quoteInto('id_curso = ?', (int) $id_curso));
            return true;
        } catch (Zend_Exception $e) {
            return false;
        }
    }
    
    /**
     * Altera o status do curso especificado para ativo
     * @param int $id_curso
     * @return boolean
     */
    public function ativarCurso($id_curso) {
        try {
            $this->db_curso = new Application_Model_DbTable_Curso();
            $this->db_curso->update(array('status' => Application_Model_Curso::status_ativo), $this->db_curso->getAdapter()->quoteInto('id_curso = ?', (int) $id_curso));
            return true;
        } catch (Zend_Exception $e) {
            return false;
        }
    }

    /**
     * Busca os cursos de acordo com os filtros especificados
     * @param array $filtros_busca
     * @param boolean $paginator
     * @return \Application_Model_Curso|null|\Zend_Paginator
     */
    public function buscaCursos($filtros_busca = null, $paginator = null) {
        try {
            $this->db_curso = new Application_Model_DbTable_Curso();
            $select = $this->db_curso->select()
                    ->from('curso', array('id_curso', 'nome_curso', 'status'))
                    ->order('nome_curso ASC');

            if (!empty($filtros_busca['nome_curso']))
                $select->where('nome_curso LIKE ?', '%' . $filtros_busca['nome_curso'] . '%');
            
            if (!empty($filtros_busca['status']))
                $select->where('status = ?', $filtros_busca['status']);

            if (empty($paginator)) {
                $cursos = $this->db_curso->fetchAll($select);
                
                if (!empty($cursos)) {
                    $array_cursos = array();
                    
                    foreach ($cursos as $curso) 
                        $array_cursos[] = new Application_Model_Curso($curso->id_curso, $curso->nome_curso);
                    
                    return $array_cursos;
                }
                return null;
            }
            return new Zend_Paginator(new Application_Model_Paginator_Curso($select->order('nome_curso')));
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Busca o curso com o ID especificado
     * @param int $id_curso
     * @return \Application_Model_Curso|null
     */
    public function buscaCursoByID($id_curso) {
        try {
            $this->db_curso = new Application_Model_DbTable_Curso();
            $select = $this->db_curso->select()
                    ->where('id_curso = ?', (int) $id_curso);

            $curso = $this->db_curso->fetchRow($select);

            if (!empty($curso)) 
                return new Application_Model_Curso($curso->id_curso, $curso->nome_curso, $curso->descricao_curso, $curso->status);
            
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

}
