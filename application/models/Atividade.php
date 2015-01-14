<?php

class Application_Model_Atividade {

    private $id_atividade;
    private $data_atividade;
    private $nome_atividade;
    private $desc_atividade;
    private $valor;

    /**
     *
     * @var Application_Model_Turma
     */
    private $turma;

    public function __construct($id_atividade, $turma = null, $nome_atividade = null, $valor = null, $desc_atividade = null, $data_atividade = null) {
        $this->id_atividade = $id_atividade;
        $this->turma = $turma;
        $this->data_atividade = $this->parseDate($data_atividade);
        $this->nome_atividade = $nome_atividade;
        $this->desc_atividade = $desc_atividade;
        $this->valor = (float)$valor;
    }

    public function getIdAtividade($isView = null) {
        if ($isView)
            return base64_encode($this->id_atividade);
        return $this->id_atividade;
    }

    public function getNomeAtividade() {
        return $this->nome_atividade;
    }

    public function getDataAtividade($isView = null) {
        if ($this->data_atividade instanceof DateTime) {
            if ($isView)
                return $this->data_atividade->format('d/m/Y');
            return $this->data_atividade->format('Y-m-d');
        }
        return null;
    }

    public function getDescricaoAtividade() {
        return $this->desc_atividade;
    }

    public function parseArray($isView = null) {
        return array(
            'id_atividade' => $this->getIdAtividade($isView),
            'nome' => $this->nome_atividade,
            'data_funcionamento' => $this->getDataAtividade($isView),
            'descricao' => $this->desc_atividade,
            'valor_total' => $this->valor,
        );
    }

    public function getValor($isView = false) {
        if ($isView) {
            return number_format($this->valor, 2, ',', '.');
        }
        return $this->valor;
    }

    public function getTurma() {
        return $this->turma;
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
