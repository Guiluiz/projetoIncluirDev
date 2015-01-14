<?php

class Application_Model_Falta {

    private $id_falta;
    private $data;
    private $observacao;

    public function __construct($id_falta = null, $data = null, $observacao = null) {//, $turma = null, $aluno = null) {
        $this->id_falta = $id_falta;
        $this->data = $this->parseDate($data);
        $this->observacao = $observacao;
    }

    public function getIdFalta($isView = null) {
        if ($isView)
            return base64_encode($this->id_falta);
        return $this->id_falta;
    }

    private function parseDate($data) {
        if (!empty($data)) {
            if (strpos($data, '-') === false)
                return DateTime::createFromFormat('d/m/Y', $data);
            return new DateTime($data);
        }
        return null;
    }
    
    public function getData(){
        return $this->data;
    }

    public function parseArray($is_view = null) {
        return array(
            'id_falta' => $this->getIdFalta($is_view),
            'data_funcionamento' => ($is_view) ? $this->data->format('d/m/Y') : $this->data->format('Y-m-d'),
            'observacao' => $this->observacao
        );
    }

}
