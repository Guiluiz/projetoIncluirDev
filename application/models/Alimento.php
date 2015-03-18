<?php

/**
 * Classe que representa um alimento do pagamento de um aluno por uma turma
 * @author Projeto Incluir
 */
class Application_Model_Alimento {
    
    /**
     *
     * @var int
     */
    private $id_alimento;
    
    /**
     *
     * @var string 
     */
    private $nome_alimento;

    public function __construct($id_alimento, $nome_alimento = null) {
        $this->id_alimento = ((!empty($id_alimento)) ? (int) $id_alimento : null);
        $this->nome_alimento = $nome_alimento;
    }
    
    /**
     * Retorna o id do alimento
     * @param boolean $isView Indica se o id será criptografado ou não
     * @return int|string
     */
    public function getIdAlimento($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_alimento);
        return $this->id_alimento;
    }
    
    public function getNomeAlimento(){
        return mb_convert_case($this->nome_alimento, MB_CASE_TITLE, 'UTF-8');
    }
    
    /**
     * Retorna um array com informações do alimento
     * Utilizado tanto para popular formulários de alimento quanto para cadastro/alteração no banco de dados
     * @param boolean $isView Indica o formato de saída de alguns dos dados
     * @return array
     */
    public function parseArray($isView = null){
        return array(
            'id_alimento' => $this->getIdAlimento($isView),
            'nome_alimento' => $this->nome_alimento
        );
    }
}

?>
