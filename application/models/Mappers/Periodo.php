<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Application_Model_Mappers_Periodo {

    private $db_periodo;
    
    public function __construct() {
        $this->db_periodo = new Application_Model_DbTable_Periodo();
    }

    public function getPeriodoAtual() {
        try {
            $select = $this->db_periodo->select();
            $select->where('is_atual = ?', true);

            $periodo = $this->db_periodo->fetchRow($select);

            if (!empty($periodo))
                return new Application_Model_Periodo(
                        $periodo->id_periodo, $periodo->is_atual, $periodo->nome_periodo, $periodo->data_inicio, $periodo->data_termino, $periodo->valor_liberacao_periodo, $periodo->freq_min_aprov, $periodo->total_pts_periodo, $periodo->min_pts_aprov, $periodo->quantidade_alimentos
                );
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Faz as alterações do período de acordo com as solicitações do usuário
     * @param APplication_Model_Periodo $periodo
     * @return boolean
     */
    public function gerenciaPeriodo($periodo) {
        try {
            if ($periodo instanceof Application_Model_Periodo && $periodo->isValid()) {
                $data_inicio = $periodo->getDataInicio();
                $data_termino = $periodo->getDataTermino();

                if ($this->verificaFimPeriodo() && $this->periodoIsValid($data_inicio->format('Y-m-d'), $data_termino->format('Y-m-d'))) {
                    $this->db_periodo->insert($periodo->parseArray());
                    return true;
                } 
                
                elseif ($this->periodoIsValid($data_inicio->format('Y-m-d'), $data_termino->format('Y-m-d'), $periodo->getIdPeriodo())) {
                    $this->db_periodo->update($periodo->parseArray(), $this->db_periodo->getAdapter()->quoteInto('is_atual = ?', true));
                    
                    $calendario = new Application_Model_DatasAtividade();
                    $calendario->removeDatasForaPeriodoAtual($data_inicio, $data_termino, $periodo->getIdPeriodo());
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
     * As datas indicadas para o novo período, ou para alteração do período atual
     * não podem interferir nas datas dos outros períodos
     */
    public function periodoIsValid($ini, $termino, $exclude = null) {
        try {
            $select = $this->db_periodo->select();
            $select->where($this->db_periodo->getAdapter()->quoteInto('(data_inicio <= ? ', $ini) .
                    $this->db_periodo->getAdapter()->quoteInto('AND data_termino >= ?) OR ', $termino) .
                    $this->db_periodo->getAdapter()->quoteInto('(data_inicio <= ? ', $ini) .
                    $this->db_periodo->getAdapter()->quoteInto('AND data_termino > ?) OR ', $ini) .
                    $this->db_periodo->getAdapter()->quoteInto('(data_inicio < ? ', $termino) .
                    $this->db_periodo->getAdapter()->quoteInto('AND data_termino >= ?) OR ', $termino) .
                    $this->db_periodo->getAdapter()->quoteInto('(data_inicio >= ? ', $ini) .
                    $this->db_periodo->getAdapter()->quoteInto('AND data_termino <= ?)', $termino)
            );

            if (!empty($exclude))
                $select->where('id_periodo <> ?', (int) $exclude);

            $periodos = $this->db_periodo->fetchAll($select)->count();

            if ($periodos > 0)
                return false;

            return true;
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    /**
     * Altera o status do período atual no banco de dados para finalizado
     * @return boolean
     */
    private function finalizaPeriodoReserva() {
        try {
            $this->db_periodo = new Application_Model_DbTable_Periodo();
            $where = $this->db_periodo->getAdapter()->quoteInto('id_periodo = ?', $this->id_periodo);

            $this->db_periodo->update(array('is_atual' => false), $where);

            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Retorna um array com os períodos já cadastrados
     * @return Application_Model_Periodo[]
     */
    public function getPeriodos() {
        try {
            $this->db_periodo = new Application_Model_DbTable_Periodo();
            $periodos = $this->db_periodo->fetchAll();

            if (!empty($periodos)) {
                $array_aux = array();

                foreach ($periodos as $periodo)
                    $array_aux[base64_encode($periodo->id_periodo)] = $periodo->nome_periodo;

                return $array_aux;
            }
            return null;
        } catch (Zend_Exception $ex) {
            echo $ex->getMessage();
            return null;
        }
    }

    /**
     * Verifica se o período já pode ser finalizado e o faz, ou indica se já foi ou não finalizado
     * @return boolean
     */
    public function verificaFimPeriodo() {
        $periodo_atual = $this->getPeriodoAtual();

        if ($periodo_atual instanceof Application_Model_Periodo) {
            $data_atual = new DateTime();
            $data_final = $periodo_atual->getDataTermino();

            $data_final->setTime(23, 59);

            if ($data_atual > $data_final) {
                $this->finalizaPeriodoReserva();
                return true;
            }
            return false;
        }
        return true;
    }

}
