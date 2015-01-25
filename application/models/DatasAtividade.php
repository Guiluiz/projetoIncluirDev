<?php

class Application_Model_DatasAtividade {

    private $datas;
    private $db_datas_atividades;

    public function __construct() {
        $this->datas = array();
        $this->iniDatas();
    }

    /**
     * Inicializa os atributos com as datas do período atual.
     */
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

    /**
     * Inclui as novas datas e remove as datas que não serão mais utilizadas
     * @param DateTime[] $dados
     * @return boolean
     */
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
                        $this->addData($data);
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

    /**
     * Inclui uma nova data no array de datas.
     * @param DateTime $data
     */
    private function addData($data) {
        if ($data instanceof DateTime && !isset($this->datas[$data->format('d/m/Y')]))
            $this->datas[$data->format('d/m/Y')] = $data;
    }

    /**
     * Retorna um array contendo as datas, de acordo com o formato indicado
     * @param boolean $isView
     * @return string[]
     */
    public function parseArray($isView = null) {
        $aux = array();
        $format = (!empty($isView)) ? 'd/m/Y' : 'Y-m-d';

        foreach ($this->datas as $data) {
            $date_format = $data->format($format);
            $aux[$date_format] = $date_format;
        }
        return $aux;
    }

    /**
     * Retorna o array de datas
     * @return DateTime[]
     */
    public function getDatas() {
        return $this->datas;
    }

    public function getDatasByPeriodo() {
        
    }

    /**
     * Retorna a quantidade de aulas.
     * @param boolena $ate_atual Indica se serão contadas as datas até o momento atual
     * @return int
     */
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

    /**
     * Converte uma string em dateTime
     * @param DateTime|string $data
     * @return null|\DateTime
     */
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

    /**
     * Quando o período é atualizado, as datas fora desse período devem ser retiradas.
     * Automaticamente, as atividades, notas e frequências lançadas são retiradas
     * @param DateTime $data_ini
     * @param DateTime $data_fim
     * @param int $periodo
     * 
     */
    public function removeDatasForaPeriodoAtual($data_ini, $data_fim, $periodo) {
        try {
            if ($data_ini instanceof DateTime && $data_fim instanceof DateTime) {
                $this->db_datas_atividades->delete(
                        $this->db_datas_atividades->getAdapter()->quoteInto('(data_funcionamento < ? OR ', $data_ini->format('Y-m-d')) .
                        $this->db_datas_atividades->getAdapter()->quoteInto('data_funcionamento > ?) AND (', $data_fim->format('Y-m-d')) .
                        $this->db_datas_atividades->getAdapter()->quoteInto('id_periodo = ?)', (int)$periodo)
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
