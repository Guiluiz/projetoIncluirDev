<?php

class Application_Model_DatasAtividade {

    private $datas;
    private $db_datas_atividades;

    public function __construct() {
        $this->datas = array();
        $this->iniDatas();
    }

    private function iniDatas() {
        try {
            $this->db_datas_atividades = new Application_Model_DbTable_DatasAtividade();
            $periodo = new Application_Model_Periodo();
            $datas = $this->db_datas_atividades->fetchAll($this->db_datas_atividades->select()
                            ->where('id_periodo = ?', $periodo->getIdPeriodo()));

            if (!empty($datas)) {
                foreach ($datas as $data)
                    $this->addData($this->parseDate($data->data_funcionamento));
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
    }

    public function gerenciaDatas($dados) {
        try {
            $this->db_datas_atividades = new Application_Model_DbTable_DatasAtividade();
            $periodo = new Application_Model_Periodo();
            $aux_array = array();

            foreach ($dados as $data) {
                if ($data instanceof DateTime) {
                    $aux_data = $data->format('d/m/Y');
                    $aux_array[$aux_data] = $data;

                    if (!isset($this->datas[$aux_data]) && ($data >= $periodo->getDataInicio() && $data <= $periodo->getDataTermino())) {
                        $this->db_datas_atividades->insert(array('data_funcionamento' => $data->format('Y-m-d'), 'id_periodo' => $periodo->getIdPeriodo()));
                        $this->datas[$aux_data] = $data;
                    }
                }
            }

            foreach ($this->datas as $key => $data) {
                if (isset($aux_array[$key]))
                    unset($this->datas[$key]);
            }

            if (!empty($this->datas)) {
                $where = "( ";

                foreach ($this->datas as $data)
                    $where .= $this->db_datas_atividades->getAdapter()->quoteInto('data_funcionamento = ?', $data->format('Y-m-d')) . " OR ";

                $this->db_datas_atividades->delete(substr($where, 0, -4) . ")");
            }
            unset($this->datas);
            $this->datas = $aux_array;

            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    private function addData($data) {
        if ($data instanceof DateTime && !isset($this->datas[$data->format('d/m/Y')]))
            $this->datas[$data->format('d/m/Y')] = $data;
    }

    public function parseArray($isView = null) {
        $aux = array();
        $format = (!empty($isView)) ? 'd/m/Y' : 'Y-m-d';

        foreach ($this->datas as $data) {
            $date_format = $data->format($format);
            $aux[$date_format] = $date_format;
        }
        return $aux;
    }

    public function getDatas() {
        return $this->datas;
    }

    public function getDatasByPeriodo() {
        
    }

    public function getQuantidadeAulas($ate_atual = true) {
        if ($ate_atual) {
            $count = 0;
            $data_atual = new DateTime();

            foreach ($this->datas as $data) {
                if ($data_atual > $data)
                    $count++;
            }
            return $count;
        }

        return count($this->datas);
    }

    private function parseDate($data) {
        if (!$data instanceof DateTime) {
            if (!empty($data)) {
                if (strpos($data, '-') === false)
                    return DateTime::createFromFormat('d/m/Y', $data);
                return new DateTime($data);
            }
            return null;
        }
        return $data;
    }

}
