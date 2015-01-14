<?php

/**
 * Classe para a representação de uma turma
 * @author Projeto Incluir
 */
class Application_Model_Turma {

    public static $status_nao_iniciada = 1;
    public static $status_iniciada = 2;
    public static $status_cancelada = 3;
    public static $status_concluida = 4;
    
    private $id_turma;
    private $nome;
    private $data_inicio;
    private $data_fim;
    private $horario_inicio;
    private $horario_termino;
    private $professores;
    private $disciplina;
    private $status;
    private $periodo;

    public function __construct($id, $nome = null, $data_inicio = null, $data_fim = null, $horario_inicio = null, $horario_termino = null, $disciplina = null, $status = null, $professor = null, $periodo = null) {
        $this->id_turma = ((!empty($id)) ? (int) $id : null);
        $this->nome = $nome;
        $this->data_inicio = $this->parseDate($data_inicio);
        $this->data_fim = $this->parseDate($data_fim);
        $this->horario_inicio = $this->parseTime($horario_inicio);
        $this->horario_termino = $this->parseTime($horario_termino);
        $this->disciplina = $disciplina;
        $this->status = (int) $status;
        $this->periodo = $periodo;

        $this->professores = array();
        $this->addProfessor($professor);
    }

    public function getIdTurma($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_turma);
        return $this->id_turma;
    }

    public function addProfessor($professor) {
        if ($professor instanceof Application_Model_Professor)
            $this->professores[] = $professor;

        else if (is_array($professor) && !empty($professor)) {
            foreach ($professor as $aux) {
                if ($aux instanceof Application_Model_Professor)
                    $this->professores[] = $aux;
            }
        }
    }

    public function getDataInicio($isView = null) {
        if (!empty($this->data_inicio)) {
            if ($isView)
                return $this->data_inicio->format('d/m/Y');
            return $this->data_inicio->format('Y-m-d');
        }
        return null;
    }

    public function getStatus($isView = null) {
        if (!empty($isView))
            return base64_encode($this->status);
        return $this->status;
    }

    public function getNomeTurma() {
        return $this->nome;
    }

    public function getDataFim($isView = null) {
        if (!empty($this->data_fim)) {
            if ($isView)
                return $this->data_fim->format('d/m/Y');
            return $this->data_fim->format('Y-m-d');
        }
        return null;
    }

    public function getProfessores() {
        return $this->professores;
    }

    public function hasProfessores() {
        if (count($this->professores) > 0)
            return true;
        return false;
    }

    private function parseDate($data) {
        if (!empty($data)) {
            if (strpos($data, '-') === false)
                return DateTime::createFromFormat('d/m/Y', $data);
            return new DateTime($data);
        }
        return null;
    }

    private function parseTime($hora) {
        $aux_tam = strlen($hora);
        if ($aux_tam == 5)
            return DateTime::createFromFormat('H:i', $hora);
        if ($aux_tam == 8)
            return DateTime::createFromFormat('H:i:s', $hora);
        return null;
    }

    public function getHorarioInicio() {
        if (!empty($this->horario_inicio))
            return $this->horario_inicio->format("H:i");
        return null;
    }

    public function getHorarioFim() {
        if (!empty($this->horario_termino))
            return $this->horario_termino->format("H:i");
        return null;
    }

    public function getDisciplina() {
        return $this->disciplina;
    }

    public function toString() {
        if ($this->disciplina instanceof Application_Model_Disciplina)
            return $this->disciplina->getNomeDisciplina() . ' - ' . $this->nome;
    }

    public function horarioTurmaToString() {
        if (!empty($this->horario_inicio) && !empty($this->horario_termino))
            return 'De ' . $this->getHorarioInicio() . ' a ' . $this->getHorarioFim();
    }

    public function isCancelada() {
        if ($this->status == Application_Model_Turma::$status_cancelada)
            return true;
        return false;
    }

    public function isAtual($periodo_atual) {
        return ($periodo_atual == $this->periodo);
    }

    public function getCompleteNomeTurma() {
        return $this->disciplina->getNomeDisciplina() . ' - ' . $this->getNomeTurma();
    }

    public function parseArray($isView = null) {
        $aux = array(
            'id_turma' => $this->getIdTurma($isView),
            'nome_turma' => $this->nome,
            'data_inicio' => $this->getDataInicio($isView),
            'data_fim' => $this->getDataFim($isView),
            'horario_inicio' => $this->getHorarioInicio(),
            'horario_fim' => $this->getHorarioFim(),
            'status' => $this->getStatus($isView)
        );

        if (is_null($isView)) {
            $aux['id_disciplina'] = $this->getDisciplina()->getIdDisciplina();
            $aux['id_periodo'] = $this->periodo;
        }
        return $aux;
    }

}
