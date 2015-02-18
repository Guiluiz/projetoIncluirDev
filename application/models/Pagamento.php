<?php

class Application_Model_Pagamento {

    public static $pagamento_liberado = 1;
    public static $pagamento_pendente = 0;
    
    public static $pagamento_normal = 1;
    public static $pagamento_isento_parcial = 2;
    public static $pagamento_isento_total = 3;
    public static $pagamento_pendente_total = 4;
    public static $pagamento_pendente_parcial = 5;
    
    public static $isencao_pendencia_alimento = 1;
    public static $isencao_pendencia_pagamento = 2;
    public static $isencao_pendencia_alimento_pagamento = 3;
    
    public static $strings_status_pagamento = array(0 => 'Pendente', 1 => 'Liberado');
    public static $index_alimento = 1;
    public static $index_quantidade_alimento = 2;
    
    private $id_pagamento;
    private $situacao;
    private $valor;
    private $alimentos;

    // private $turma;

    public function __construct($id_pagamento, $situacao, $valor = null, $alimento = null, $quantidade = null) {//$turma, $situacao = null, $valor = null, $alimento = null, $quantidade = null) {
        $this->id_pagamento = ((!empty($id_pagamento)) ? (int) $id_pagamento : null);
        $this->situacao = $this->parseSituacao($situacao);
        $this->valor = $valor;
        $this->alimentos = array();
        $this->addAlimento($alimento, $quantidade);
        //$this->turma = $turma;
    }

    public function getIdPagamento($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_pagamento);
        return $this->id_pagamento;
    }

    private function isValidSituacao($situacao) {
        if ($situacao == Application_Model_Pagamento::$pagamento_liberado || $situacao == Application_Model_Pagamento::$pagamento_pendente)
            return true;
        return false;
    }

    private function parseSituacao($situacao) {
        if (is_numeric($situacao))
            return (int) $situacao;

        if (is_string($situacao)) {
            foreach (Application_Model_Pagamento::$strings_status_pagamento as $key => $val) {
                if ($situacao == $val)
                    return $key;
            }
        }
        return null;
    }

    public function addAlimento($alimento, $quantidade) {
        $quantidade = (int) $quantidade;
        if ($alimento instanceof Application_Model_Alimento && $quantidade > 0) {
            if (!isset($this->alimentos[$alimento->getIdAlimento()])) {
                $this->alimentos[$alimento->getIdAlimento()][Application_Model_Pagamento::$index_alimento] = $alimento;
                $this->alimentos[$alimento->getIdAlimento()][Application_Model_Pagamento::$index_quantidade_alimento] = $quantidade;
            }
        }
    }

    public function getAlimentos() {
        return $this->alimentos;
    }

    public function hasAlimentos() {
        if (count($this->alimentos) > 0)
            return true;
        return false;
    }

    public function parseArray($isView = null) {
        return array(
            'id_pagamento' => $this->getIdPagamento($isView),
            'situacao' => $this->situacao,
            'valor_pago' => $this->valor
        );
    }

    public function setIdPagamento($id_pagamento) {
        $this->id_pagamento = ((!empty($id_pagamento)) ? (int) $id_pagamento : null);
    }

    public function getAlimentosPagamento() {
        $aux = array();

        if ($this->hasAlimentos()) {
            foreach ($this->alimentos as $alimento)
                $aux[$alimento[Application_Model_Pagamento::$index_alimento]->getIdAlimento(true)] = $alimento[Application_Model_Pagamento::$index_quantidade_alimento];
        }
        return $aux;
    }

    public function getValorPagamento($isView = null) {
        if (!empty($isView))
            return number_format((float) $this->valor, 2, ',', '');
        return $this->valor;
    }

    public function getSituacao($isView = true) {
        if ($isView)
            return Application_Model_Pagamento::$strings_status_pagamento[$this->situacao];
        return $this->situacao;
    }

}

?>
