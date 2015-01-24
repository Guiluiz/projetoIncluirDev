<?php

class Application_Model_Periodo {

    private $id_periodo;
    private $identificacao_periodo;
    private $data_inicial;
    private $data_final;
    private $valor_liberacao;
    private $frequencia_min_aprovacao;
    private $total_pts_periodo;
    private $min_pts_aprovacao;
    private $quantidade_alimentos;
    private $db_periodo;

    public function __construct() {
        $this->db_periodo = new Application_Model_DbTable_Periodo();
        $this->iniPeriodo();
    }

    private function iniPeriodo() {
        try {
            $select = $this->db_periodo->select();
            $select->where('is_atual = ?', true);

            $periodo = $this->db_periodo->fetchRow($select);

            if (!empty($periodo)) {
                $this->id_periodo = $periodo->id_periodo;
                $this->identificacao_periodo = $periodo->nome_periodo;
                $this->data_inicial = $this->parseDate($periodo->data_inicio);
                $this->data_final = $this->parseDate($periodo->data_termino);
                $this->valor_liberacao = $periodo->valor_liberacao_periodo;
                $this->frequencia_min_aprovacao = $periodo->freq_min_aprov;
                $this->total_pts_periodo = $periodo->total_pts_periodo;
                $this->min_pts_aprovacao = $periodo->min_pts_aprov;
                $this->quantidade_alimentos = $periodo->quantidade_alimentos;
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
    }

    private function parseDate($date) {
        if (strpos($date, '-'))
            return new DateTime($date);
        return DateTime::createFromFormat('d/m/Y', $date);
    }

    public function gerenciaPeriodo($dados) {
        try {
            if (!empty($dados['data_inicio']) && !empty($dados['data_fim'])) {
                $dados['id_periodo'] = $this->id_periodo;
                $dados['data_inicio'] = $this->parseDate($dados['data_inicio']);
                $dados['data_fim'] = $this->parseDate($dados['data_fim']);
                $dados['valor_liberacao'] = (float) str_replace(',', '.', $dados['valor_liberacao']);
                
                if ($dados['data_inicio'] instanceof DateTime && $dados['data_fim'] instanceof DateTime && $dados['data_inicio'] < $dados['data_fim'] && $dados['valor_liberacao'] > 0.0) {
                    if ($this->verificaFimPeriodo())
                        $dados['id_periodo'] = $this->db_periodo->insert(array('is_atual' => true, 'nome_periodo' => $dados['nome_periodo'], 'data_inicio' => $dados['data_inicio']->format('Y-m-d'), 'data_termino' => $dados['data_fim']->format('Y-m-d'), 'valor_liberacao_periodo' => $dados['valor_liberacao'], 'freq_min_aprov' => $dados['freq_min_aprov'], 'total_pts_periodo' => $dados['total_pts_periodo'], 'min_pts_aprov' => $dados['min_pts_aprov'], 'quantidade_alimentos' => $dados['quantidade_alimentos']));
                    else
                        $this->db_periodo->update(array('nome_periodo' => $dados['nome_periodo'], 'data_inicio' => $dados['data_inicio']->format('Y-m-d'), 'data_termino' => $dados['data_fim']->format('Y-m-d'), 'valor_liberacao_periodo' => $dados['valor_liberacao'], 'freq_min_aprov' => $dados['freq_min_aprov'], 'total_pts_periodo' => $dados['total_pts_periodo'], 'min_pts_aprov' => $dados['min_pts_aprov'], 'quantidade_alimentos' => $dados['quantidade_alimentos']), $this->db_periodo->getAdapter()->quoteInto('is_atual = ?', true));

                    $this->setPeriodo($dados['id_periodo'], $dados['nome_periodo'], $dados['data_inicio'], $dados['data_fim'], $dados['valor_liberacao'], $dados['freq_min_aprov'], $dados['total_pts_periodo'], $dados['min_pts_aprov'], $dados['quantidade_alimentos']);
                    return true;
                }
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getDataInicio() {
        return $this->data_inicial;
    }

    public function getDataTermino() {
        return $this->data_final;
    }

    public function verificaFimPeriodo() {
        if ($this->isValid()) {
            $data_atual = new DateTime();
            $this->data_final->setTime(23, 59);

            if ($data_atual > $this->data_final) {
             //   $this->finalizaPeriodoReserva();
                return true;
            }

            return false;
        }
        return true;
    }

    private function isValid() {
        if (!empty($this->id_periodo) && $this->data_inicial instanceof DateTime && $this->data_final instanceof DateTime)
            return true;
        return false;
    }

    private function finalizaPeriodoReserva() {
        try {
            $db_periodo = new Application_Model_DbTable_Periodo();
            $where = $db_periodo->getAdapter()->quoteInto('id_periodo = ?', $this->id_periodo);

            $db_periodo->update(array('is_atual' => false), $where);
            $this->setPeriodo();

            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    private function setPeriodo($id_periodo = null, $nome_periodo = null, $data_inicio = null, $data_termino = null, $valor = null, $min_freq_aprov = null, $total_pts = null, $min_pts_aprov = null, $quantidade_alimentos = null) {
        $this->data_inicio = $data_inicio;
        $this->data_termino = $data_termino;
        $this->id_periodo = $id_periodo;
        $this->identificacao_periodo = $nome_periodo;
        $this->valor_liberacao = $valor;
        $this->frequencia_min_aprovacao = $min_freq_aprov;
        $this->min_pts_aprovacao = $min_pts_aprov;
        $this->total_pts_periodo = $total_pts;
        $this->quantidade_alimentos = $quantidade_alimentos;
    }

    public function getIdPeriodo($isView = null) {
        if (!$this->verificaFimPeriodo()) {
            if ($isView)
                return base64_encode($this->id_periodo);
            return $this->id_periodo;
        }
        return null;
    }

    public function getValorLiberacao($isView = null) {
        if (!$this->verificaFimPeriodo()) {
            if (!empty($isView))
                return number_format($this->valor_liberacao, 2, ',');
            return $this->valor_liberacao;
        }
        return null;
    }

    public function getQuantidadeAlimentos() {
        if (!$this->verificaFimPeriodo())
            return $this->quantidade_alimentos;
        return null;
    }
    
    public function getTotalPontosPeriodo(){
        return $this->total_pts_periodo;
    }

    public function parseArray() {
        if ($this->isValid())
            return array(
                'id_periodo' => $this->id_periodo,
                'nome_periodo' => $this->identificacao_periodo,
                'data_inicio' => $this->data_inicial->format('d/m/Y'),
                'data_fim' => $this->data_final->format('d/m/Y'),
                'valor_liberacao' => number_format($this->valor_liberacao, 2, ',', ''),
                'freq_min_aprov' => $this->frequencia_min_aprovacao,
                'total_pts_periodo' => $this->total_pts_periodo,
                'min_pts_aprov' => $this->min_pts_aprovacao,
                'quantidade_alimentos' => $this->quantidade_alimentos
            );
        return array(
            'id_periodo' => '',
            'nome_periodo' => '',
            'data_inicio' => '',
            'data_fim' => '',
            'valor_liberacao' => '',
            'freq_min_aprov' => '',
            'total_pts_periodo' => '',
            'min_pts_aprov' => '',
            'quantidade_alimentos' => ''
        );
    }

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

}
