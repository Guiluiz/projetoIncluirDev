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
    private $is_semestre_atual;

    public function __construct($id_periodo, $is_atual = false, $nome_periodo = null, $data_inicio = null, $data_termino = null, $valor = null, $min_freq_aprov = null, $total_pts = null, $min_pts_aprov = null, $quantidade_alimentos = null) {
        $this->data_inicial = $this->parseDate($data_inicio);
        $this->data_final = $this->parseDate($data_termino);
        $this->id_periodo = (int) $id_periodo;
        $this->identificacao_periodo = $nome_periodo;
        $this->valor_liberacao = (float) str_replace(',', '.', $valor);
        $this->frequencia_min_aprovacao = $min_freq_aprov;
        $this->min_pts_aprovacao = $min_pts_aprov;
        $this->total_pts_periodo = $total_pts;
        $this->quantidade_alimentos = $quantidade_alimentos;
        $this->is_semestre_atual = (bool)$is_atual;
    }

    /**
     * Verifica se o período indicado é o atual
     * @return boolean
     */
    public function isPeriodoAtual() {
        return $this->is_semestre_atual;
    }

    public function getIdPeriodo($isView = null) {
        if ($isView)
            return base64_encode($this->id_periodo);
        return $this->id_periodo;
    }

    public function getValorLiberacao($isView = null) {
        if (!empty($isView))
            return number_format($this->valor_liberacao, 2, ',');
        return $this->valor_liberacao;
    }

    public function getQuantidadeAlimentos() {
        return $this->quantidade_alimentos;
    }

    public function getFrequenciaLiberacao() {
        return $this->frequencia_min_aprovacao;
    }

    public function getTotalPontosPeriodo() {
        return $this->total_pts_periodo;
    }

    public function getDataInicio($getObject = true, $isView = null) {
        if ($this->data_inicial instanceof DateTime) {
            if ($getObject)
                return $this->data_inicial;

            elseif ($isView)
                return $this->data_inicial->format('d/m/Y');
            else
                return $this->data_inicial->format('Y-m-d');
        }
        return null;
    }

    public function getDataTermino($getObject = true, $isView = null) {
        if ($this->data_final instanceof DateTime) {
            if ($getObject)
                return $this->data_final;

            elseif ($isView)
                return $this->data_final->format('d/m/Y');
            else
                return $this->data_final->format('Y-m-d');
        }
        return null;
    }

    public function getNomePeriodo() {
        return $this->identificacao_periodo;
    }

    public function parseArray($isView = false) {
        return array(
            'id_periodo' => $this->getIdPeriodo($isView),
            'nome_periodo' => $this->identificacao_periodo,
            'data_inicio' => $this->getDataInicio(false, $isView),
            'data_termino' => $this->getDataTermino(false, $isView),
            'valor_liberacao_periodo' => number_format($this->valor_liberacao, 2, ',', ''),
            'freq_min_aprov' => $this->frequencia_min_aprovacao,
            'total_pts_periodo' => $this->total_pts_periodo,
            'min_pts_aprov' => $this->min_pts_aprovacao,
            'quantidade_alimentos' => $this->quantidade_alimentos,
            'is_atual' => $this->is_semestre_atual
        );
    }

    /**
     * Auxiliar para converter string em data
     * @param string $date
     * @return DateTime
     */
    private function parseDate($date) {
        if (!empty($date)) {
            if (strpos($date, '-'))
                return new DateTime($date);
            return DateTime::createFromFormat('d/m/Y', $date);
        }
        return null;
    }

    public function isValid() {
        if ($this->data_inicial instanceof DateTime && $this->data_final instanceof DateTime) {
            if ($this->data_inicial < $this->data_final && $this->valor_liberacao > 0.0)
                return true;
        }
        return false;
    }

    public function getMinPtsAprovacao() {
        return $this->min_pts_aprovacao;
    }

}
