<?php

class Application_Model_Mappers_DatasAtividade {

    private $db_datas_atividades;

    public function getDatasByPeriodo($periodo) {
        try {
            if ($periodo instanceof Application_Model_Periodo) {
                $this->db_datas_atividades = new Application_Model_DbTable_DatasAtividade();

                $datas = $this->db_datas_atividades->fetchAll($this->db_datas_atividades->select()
                                ->where('id_periodo = ?', $periodo->getIdPeriodo()));

                if (!empty($datas)) {
                    $obj_data = new Application_Model_DatasAtividade($periodo);

                    foreach ($datas as $data)
                        $obj_data->addData($data->data_funcionamento);
                }
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Inclui as novas datas e remove as datas que não serão mais utilizadas
     * @param DateTime[] $dados
     * @return boolean
     */
    public function gerenciaDatasPeriodoAtual($dados, $periodo_atual) {
        try {
            $this->db_datas_atividades = new Application_Model_DbTable_DatasAtividade();

            if ($periodo_atual instanceof Application_Model_Periodo && $periodo_atual->isPeriodoAtual()) {
                $calendario = $this->getDatasByPeriodo($periodo_atual);

                if ($calendario instanceof Application_Model_DatasAtividade) {
                    $datas_cadastradas = $calendario->getDatas();
                    $aux_array = array();

                    foreach ($dados as $data) {
                        if ($data instanceof DateTime) {
                            $aux_data = $data->format('d/m/Y');
                            $aux_array[$aux_data] = $data;

                            if (!isset($datas_cadastradas[$aux_data]) && ($data >= $periodo->getDataInicio() && $data <= $periodo->getDataTermino())) {
                                $this->db_datas_atividades->insert(array('data_funcionamento' => $data->format('Y-m-d'), 'id_periodo' => $periodo_atual->getIdPeriodo()));
                                $this->addData($data);
                            }
                        }
                    }

                    foreach ($datas_cadastradas as $key => $data) {
                        if (isset($aux_array[$key]))
                            unset($datas_cadastradas[$key]);
                    }

                    if (!empty($datas_cadastradas)) {
                        $where = "( ";

                        foreach ($datas_cadastradas as $data)
                            $where .= $this->db_datas_atividades->getAdapter()->quoteInto('data_funcionamento = ?', $data->format('Y-m-d')) . " OR ";

                        $this->db_datas_atividades->delete(substr($where, 0, -4) . ")");
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
     * Quando o período é atualizado, as datas fora desse período devem ser retiradas.
     * Automaticamente, as atividades, notas e frequências lançadas são retiradas
     * @param DateTime $data_ini
     * @param DateTime $data_fim
     * @param int $id_periodo
     * 
     */
    public function removeDatasForaPeriodoAtual($data_ini, $data_fim, $id_periodo) {
        try {
            if ($data_ini instanceof DateTime && $data_fim instanceof DateTime) {
                $this->db_datas_atividades->delete(
                        $this->db_datas_atividades->getAdapter()->quoteInto('(data_funcionamento < ? OR ', $data_ini->format('Y-m-d')) .
                        $this->db_datas_atividades->getAdapter()->quoteInto('data_funcionamento > ?) AND (', $data_fim->format('Y-m-d')) .
                        $this->db_datas_atividades->getAdapter()->quoteInto('id_periodo = ?)', (int) $id_periodo)
                );
                return true;
            }
            return false;
        } catch (Zend_Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

}
