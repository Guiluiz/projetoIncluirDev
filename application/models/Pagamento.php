<?php

/**
 * Classe para representação do pagamento da turma do aluno.
 * @author Projeto Incluir
 */
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

    /**
     *
     * @var int 
     */
    private $id_pagamento;

    /**
     *
     * @var string 
     */
    private $situacao;

    /**
     *
     * @var float 
     */
    private $valor;

    /**
     *
     * @var Application_Model_Alimento 
     */
    private $alimentos;

    /**
     *
     * @var int 
     */
    private $condicao;

    /**
     *
     * @var int 
     */
    private $tipo_isencao_pendencia;

    /**
     *
     * @var int 
     */
    private $num_recibo;

    public function __construct($id_pagamento, $situacao, $valor = null, $alimento = null, $quantidade = null, $condicao = null, $isencao_pendencia = null, $recibo = null) {//$turma, $situacao = null, $valor = null, $alimento = null, $quantidade = null) {
        $this->id_pagamento = ((!empty($id_pagamento)) ? (int) $id_pagamento : null);
        $this->situacao = $this->parseSituacao($situacao);
        $this->valor = $valor;
        $this->alimentos = array();
        $this->addAlimento($alimento, $quantidade);
        $this->condicao = $condicao;
        $this->tipo_isencao_pendencia = $isencao_pendencia;
        $this->num_recibo = $recibo;
    }

    public function getIdPagamento($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_pagamento);
        return $this->id_pagamento;
    }

    public function getCondicaoPagamento() {
        return $this->condicao;
    }

    public function getCondicaoMatriculaToString() {
        $nomes = array(Application_Model_Pagamento::$pagamento_normal => 'Normal',
            Application_Model_Pagamento::$pagamento_isento_parcial => 'Isento Parcial',
            Application_Model_Pagamento::$pagamento_isento_total => 'Isento Total',
            Application_Model_Pagamento::$pagamento_pendente_parcial => 'Pendente Parcial',
            Application_Model_Pagamento::$pagamento_pendente_total => 'Pendente Total');

        $nomes_isencao_pendencia = array(Application_Model_Pagamento::$isencao_pendencia_alimento => 'Alimento',
            Application_Model_Pagamento::$isencao_pendencia_alimento_pagamento => 'Alimento e Pagamento',
            Application_Model_Pagamento::$isencao_pendencia_pagamento => 'Pagamento');

        if ($this->condicao == Application_Model_Pagamento::$pagamento_isento_parcial || $this->condicao == Application_Model_Pagamento::$pagamento_pendente_parcial)
            return $nomes[$this->condicao] . ' | ' . $nomes_isencao_pendencia[$this->tipo_isencao_pendencia];

        return $nomes[$this->condicao];
    }

    public function getTipoIsencaoPendencia() {
        return $this->tipo_isencao_pendencia;
    }

    /**
     * Converte a string da situação em valor inteiro correspondente.
     * @param int|string $situacao
     * @return int|null
     */
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

    /**
     * Inclui um alimento e a quantidade dele no pagamento.
     * @param Application_Model_Alimento $alimento
     * @param int $quantidade
     */
    public function addAlimento($alimento, $quantidade) {
        $quantidade = (int) $quantidade;
        if ($alimento instanceof Application_Model_Alimento && $quantidade > 0) {
            if (!isset($this->alimentos[$alimento->getIdAlimento()])) {
                $this->alimentos[$alimento->getIdAlimento()][Application_Model_Pagamento::$index_alimento] = $alimento;
                $this->alimentos[$alimento->getIdAlimento()][Application_Model_Pagamento::$index_quantidade_alimento] = $quantidade;
            }
        }
    }

    /**
     * Retorna o array de alimentos e suas quantidades do pagamento
     * @return array
     */
    public function getAlimentos() {
        return $this->alimentos;
    }

    public function hasAlimentos() {
        if (count($this->alimentos) > 0)
            return true;
        return false;
    }

    /**
     * Retorna um array com as informações do pagamento
     * @param boolean $isView
     * @return array
     */
    public function parseArray($isView = null) {
        return array(
            'id_pagamento' => $this->getIdPagamento($isView),
            'situacao' => $this->situacao,
            'condicao' => $this->condicao,
            'tipo_isencao_pendencia' => $this->tipo_isencao_pendencia,
            'valor_pago' => $this->valor,
            'num_recibo' => $this->num_recibo
        );
    }

    public function setIdPagamento($id_pagamento) {
        $this->id_pagamento = ((!empty($id_pagamento)) ? (int) $id_pagamento : null);
    }

    /**
     * Retorna as quantidades de alimentos junto com os id's dos alimentos correspondentes.
     * Utilizado na construção da tabela de alimentos na alteração de aluno.
     * @return array
     */
    public function getAlimentosPagamento() {
        $aux = array();

        if ($this->hasAlimentos()) {
            foreach ($this->alimentos as $alimento)
                $aux[$alimento[Application_Model_Pagamento::$index_alimento]->getIdAlimento(true)] = $alimento[Application_Model_Pagamento::$index_quantidade_alimento];
        }
        return $aux;
    }

    /**
     * Retorna o valor do pagamento
     * @param boolean $isView Indica o formato do retorno
     * @return string|float
     */
    public function getValorPagamento($isView = null) {
        if (!empty($isView))
            return number_format((float) $this->valor, 2, ',', '');
        return $this->valor;
    }

    /**
     * Retorna a situação do pagamento. 
     * @param boolean $isView 
     * @return int|string
     */
    public function getSituacao($isView = true) {
        if ($isView)
            return Application_Model_Pagamento::$strings_status_pagamento[$this->situacao];
        return $this->situacao;
    }

    public function getRecibo() {
        return $this->num_recibo;
    }

    public function getReciboToString() {
        if (!empty($this->num_recibo))
            return $this->num_recibo;
        return '-';
    }

}

?>
