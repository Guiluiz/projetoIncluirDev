<?php

class Application_Model_EscalaFrequencia {

    private $id_frequencia;
    private $data;
    private $hora_entrada;
    private $hora_saida;
    private $is_presente;

    public function __construct($id_frequencia = null, $is_presente = false, $data = null, $hora_entrada = null, $hora_saida = null) {//, $turma = null, $aluno = null) {
        $this->id_frequencia = $id_frequencia;
        $this->is_presente = $is_presente;
        $this->data = $this->parseDate($data);
        $this->hora_entrada = $this->parseDateTime($hora_entrada);
        $this->hora_saida = $this->parseDateTime($hora_saida);
    }

    public function getIdFrequencia($isView = null) {
        if ($isView)
            return base64_encode($this->id_frequencia);
        return $this->id_frequencia;
    }

    private function parseDate($data) {
        if (!empty($data)) {
            if (strpos($data, '-') === false)
                return DateTime::createFromFormat('d/m/Y', $data);
            return new DateTime($data);
        }
        return null;
    }

    private function parseDateTime($hora) {
        $aux_tam = strlen($hora);
        if ($aux_tam == 5 || $aux_tam == 4)
            return DateTime::createFromFormat('H:i', $hora);
        if ($aux_tam == 8)
            return DateTime::createFromFormat('H:i:s', $hora);
        return null;
    }

    public function getData() {
        return $this->data;
    }

    public function getHoraEntrada() {
        return $this->hora_entrada;
    }

    public function getHoraSaida() {
        return $this->hora_saida;
    }

    public function getIsPresente() {
        return $this->is_presente;
    }

    public function setHoraEntrada($hora_entrada) {
        $this->hora_entrada = $this->parseDateTime($hora_entrada);
    }

    public function setHoraSaida($hora_saida) {
        $this->hora_saida = $this->parseDateTime($hora_saida);
    }

    public function parseArray($isView = null) {
        return array(
        'id_frequencia' => $this->getIdFrequencia($isView),
        'data_funcionamento' => $this->getFormatedData($isView),
        'hora_entrada' => ($this->hora_entrada instanceof DateTime) ? $this->hora_entrada->format('H:i') : null,
        'hora_saida' => ($this->hora_saida) ? $this->hora_saida->format('H:i') : null,
        'is_presente' => $this->is_presente
        );
    }

    public function getFormatedData($isView = null) {
        if ($this->data instanceof DateTime) {
            if ($isView)
                return $this->data->format('d/m/Y');
            return $this->data->format('Y-m-d');
        }
        return null;
    }

    public static function verificaFrequencias($frequencias) {
        if (is_array($frequencias) && !empty($frequencias)) {
            foreach ($frequencias as $frequencia) {
                if ($frequencia instanceof Application_Model_EscalaFrequencia) {
                    if ($frequencia->getIsPresente()) {
                        $hora_entrada = $frequencia->getHoraEntrada();
                        $hora_saida = $frequencia->getHoraSaida();

                        if (!$hora_entrada instanceof DateTime || !$hora_saida instanceof DateTime || $hora_saida <= $hora_entrada)
                            return false;
                    }
                } else
                    return false;
            }
        }
        return true;
    }

}
