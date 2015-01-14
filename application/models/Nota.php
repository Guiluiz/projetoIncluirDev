<?php

class Application_Model_Nota {

    /**
     * @var int 
     */
    private $id_nota;

    /**
     * @var Application_Model_Atividade 
     */
    private $atividade;

    /**
     * @var float 
     */
    private $valor;

    public function __construct($id_nota = null, $atividade = null, $valor = null) {
        $this->id_nota = $id_nota;
        $this->atividade = $atividade;
        $this->valor = (float)$valor;
    }

    public function getIdNota($isView = null) {
        if ($isView)
            return base64_encode($this->id_nota);
        return base64_encode($this->id_nota);
    }

    public function getAtividade() {
        return $this->atividade;
    }

    public function getValor($isString = null) {
        if ($isString)
            return $this->valor . ' pontos';
        return $this->valor;
    }

    public function parseArray($isView = null, $get_atividade = null) {
        $aux = array(
            'id_nota' => $this->getIdNota($isView),
            'valor_nota' => $this->getValor()
        );

        if ($get_atividade && $this->atividade instanceof Application_Model_Atividade)
            $aux['atividade'] = $this->atividade->getIdAtividade(true);

        return $aux;
    }

    public function toString() {
        return  $this->getAtividade()->getNomeAtividade() . " (" . $this->getAtividade()->getValor(true) . " pts): <b>" . number_format($this->valor, 2, ',', '.') . ' pts</b>';
    }

}
