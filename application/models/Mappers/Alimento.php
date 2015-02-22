<?php

class Application_Model_Mappers_Alimento {

    private $db_alimento;

    /**
     * Inclui um alimento no BD
     * @param Application_Model_Alimento $alimento
     * @return boolean
     */
    public function addAlimento($alimento) {
        try {
            if ($alimento instanceof Application_Model_Alimento) {
                $validacao = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'alimento',
                    'field' => 'nome_alimento'
                ));

                if ($validacao->isValid($alimento->getNomeAlimento())) {
                    $this->db_alimento = new Application_Model_DbTable_Alimento();
                    $this->db_alimento->insert($alimento->parseArray());
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
     * Altera um alimento no BD
     * @param Application_Model_Alimento $alimento
     * @return boolean
     */
    public function alterarAlimento($alimento) {
        try {
            if ($alimento instanceof Application_Model_Alimento) {
                $validacao = new Zend_Validate_Db_NoRecordExists(array(
                    'table' => 'alimento',
                    'field' => 'nome_alimento',
                    'exclude' => array(
                        'field' => 'id_alimento',
                        'value' => $alimento->getIdAlimento()
                    )
                ));
                if ($validacao->isValid($alimento->getNomeAlimento())) {
                    $this->db_alimento = new Application_Model_DbTable_Alimento();
                    $this->db_alimento->update($alimento->parseArray(), $this->db_alimento->getAdapter()->quoteInto('id_alimento = ?', $alimento->getIdAlimento()));
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
     * Exclui um alimento do BD
     * @param int $id_alimento
     * @return boolean
     */
    public function excluirAlimento($id_alimento) {
        try {
            $this->db_alimento = new Application_Model_DbTable_Alimento();
            $this->db_alimento->delete($this->db_alimento->getAdapter()->quoteInto('id_alimento = ?', (int) $id_alimento));
            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Busca os alimentos de acordo com os filtros especificados
     * @param array $filtros_busca
     * @param boolean $paginator
     * @return \Application_Model_Alimento|null|\Zend_Paginator
     */
    public function buscaAlimentos($filtros_busca = null, $paginator = null) {
        try {
            $this->db_alimento = new Application_Model_DbTable_Alimento();
            $select = $this->db_alimento->select()->order('nome_alimento ASC');

            if (!empty($filtros_busca['nome_alimento']))
                $select->where('nome_alimento LIKE ?', '%' . $filtros_busca['nome_alimento'] . '%');

            if (empty($paginator)) {
                $alimentos = $this->db_alimento->fetchAll($select);

                if (!empty($alimentos)) {
                    $array_alimentos = array();
                    foreach ($alimentos as $alimento) {
                        $array_alimentos[base64_encode($alimento->id_alimento)] = new Application_Model_Alimento(
                                $alimento->id_alimento, $alimento->nome_alimento
                        );
                    }
                    return $array_alimentos;
                }
                return null;
            }
            return new Zend_Paginator(new Application_Model_Paginator_Alimento($select->order('nome_alimento')));
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function buscaAlimentoByID($id_alimento) {
        try {
            $this->db_alimento = new Application_Model_DbTable_Alimento();
            $select = $this->db_alimento->select()
                    ->where('id_alimento = ?', (int) $id_alimento);

            $alimento = $this->db_alimento->fetchRow($select);

            if (!empty($alimento)) {
                return new Application_Model_Alimento($alimento->id_alimento, $alimento->nome_alimento);
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

}

?>
