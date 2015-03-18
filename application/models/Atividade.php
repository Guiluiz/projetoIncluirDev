<?php

/**
 * Classe que representa uma atividade
 * @author Projeto Incluir
 */
class Application_Model_Atividade {

    /**
     *
     * @var int 
     */
    private $id_atividade;

    /**
     *
     * @var DateTime 
     */
    private $data_atividade;

    /**
     *
     * @var string 
     */
    private $nome_atividade;

    /**
     *
     * @var string 
     */
    private $desc_atividade;

    /**
     *
     * @var float 
     */
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
        $this->valor = (float) $valor;
    }

    /**
     * Retorna o id da atividade
     * @param boolean $isView Indica se o id será criptografado ou não
     * @return int|string
     */
    public function getIdAtividade($isView = null) {
        if ($isView)
            return base64_encode($this->id_atividade);
        return $this->id_atividade;
    }

    public function getNomeAtividade() {
        return $this->nome_atividade;
    }

    /**
     * Retorna a data em que ocorreu a atividade
     * @param boolean $isView Indica o formato da data
     * @return string|null
     */
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
    
    /**
     * Retorna um array com as informações da atividade.
     * Utilizado tanto para popular formulários de atividade quanto para cadastro/alteração no banco de dados
     * @param boolean $isView Indica qual será o formato de alguns dos dados (id, data...)
     * @return array
     */
    public function parseArray($isView = null) {
        return array(
            'id_atividade' => $this->getIdAtividade($isView),
            'nome' => $this->nome_atividade,
            'data_funcionamento' => $this->getDataAtividade($isView),
            'descricao' => $this->desc_atividade,
            'valor_total' => $this->valor,
        );
    }

    /**
     * Retorna o valor da atividade
     * @param boolean $isView Indica o formato do valor
     * @return float|string|null
     */
    public function getValor($isView = false) {
        if ($isView)
            return number_format($this->valor, 2, ',', '.');
        return $this->valor;
    }
    
    /**
     * Retorna a turma ao qual a atividade pertence
     * @return Application_Model_Turma
     */
    public function getTurma() {
        return $this->turma;
    }

    /**
     * Converte uma data em string para um objeto de DateTime
     * @param string $data
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

}
