<?php

class Application_Model_Alimento {

    private $id_alimento;
    private $nome_alimento;

    public function __construct($id_alimento, $nome_alimento = null) {
        $this->id_alimento = ((!empty($id_alimento)) ? (int) $id_alimento : null);
        $this->nome_alimento = $nome_alimento;
    }
    
    public function getIdAlimento($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_alimento);
        return $this->id_alimento;
    }
    
    public function getNomeAlimento(){
        return $this->nome_alimento;
    }
    
    public function parseArray($isView = null){
        return array(
            'id_alimento' => $this->getIdAlimento($isView),
            'nome_alimento' => $this->nome_alimento
        );
    }
}

?>
