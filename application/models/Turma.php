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

    /**
     *
     * @var int 
     */
    private $id_turma;

    /**
     *
     * @var string 
     */
    private $nome;

    /**
     *
     * @var DateTime 
     */
    private $data_inicio;

    /**
     *
     * @var DateTime 
     */
    private $data_fim;

    /**
     *
     * @var DateTime 
     */
    private $horario_inicio;

    /**
     *
     * @var DateTime 
     */
    private $horario_termino;

    /**
     *
     * @var Application_Model_Professor[]
     */
    private $professores;

    /**
     *
     * @var Application_Model_Disciplina 
     */
    private $disciplina;

    /**
     *
     * @var int 
     */
    private $status;

    /**
     *
     * @var Application_Model_Periodo 
     */
    private $periodo;

    /**
     *
     * @var string 
     */
    private $sala;

    public function __construct($id, $nome = null, $data_inicio = null, $data_fim = null, $horario_inicio = null, $horario_termino = null, $disciplina = null, $status = null, $professor = null, $periodo = null, $sala = null) {
        $this->id_turma = ((!empty($id)) ? (int) $id : null);
        $this->nome = $nome;
        $this->sala = $sala;
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

    /**
     * Retorna o id da turma
     * @param boolean $isView Indica se o id será criptografado ou não
     * @return int|string
     */
    public function getIdTurma($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_turma);
        return $this->id_turma;
    }

    /**
     * Inclui um ou mais professores na turma
     * @param Application_Model_Professor $professor
     */
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

    /**
     * Retorna a data de início da turma
     * @param boolean $isView Indica o formato da data retornada
     * @return string|null
     */
    public function getDataInicio($isView = null) {
        if (!empty($this->data_inicio)) {
            if ($isView)
                return $this->data_inicio->format('d/m/Y');
            return $this->data_inicio->format('Y-m-d');
        }
        return null;
    }

    /**
     * Retorna o status da turma
     * @param boolean $isView Indica se o valor retornado vai ser criptografado ou não
     * @return int|string
     */
    public function getStatus($isView = null) {
        if (!empty($isView))
            return base64_encode($this->status);
        return $this->status;
    }

    public function getNomeTurma() {
        return mb_strtoupper($this->nome, 'UTF-8');
    }

    /**
     * Retorna a data de término da turma
     * @param boolean $isView Indica o formato da data retornada
     * @return string|null
     */
    public function getDataFim($isView = null) {
        if (!empty($this->data_fim)) {
            if ($isView)
                return $this->data_fim->format('d/m/Y');
            return $this->data_fim->format('Y-m-d');
        }
        return null;
    }

    /**
     * Retorna o array de professores da turma
     * @return Application_Model_Professor[]
     */
    public function getProfessores() {
        return $this->professores;
    }

    public function hasProfessores() {
        if (count($this->professores) > 0)
            return true;
        return false;
    }

    /**
     * Converte a string passada em objeto da classe DateTime
     * @param string $data
     * @return null|\DateTime
     */
    private function parseDate($data) {
        if (!empty($data)) {
            if (strpos($data, '-') === false)
                return DateTime::createFromFormat('d/m/Y', $data);
            return new DateTime($data);
        }
        return null;
    }

    /**
     * Converte a hora passada em objeto da classe DateTime
     * @param string $hora
     * @return DateTime|null
     */
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

    public function getSala() {
        return $this->sala;
    }

    public function getDisciplina() {
        return $this->disciplina;
    }

    public function getPeriodo() {
        return $this->periodo;
    }

    /**
     * Retorna o nome completo da turma, incluindo o nome da disciplina
     * @return string
     */
    public function toString() {
        if ($this->disciplina instanceof Application_Model_Disciplina)
            return $this->disciplina->getNomeDisciplina() . ' - ' . $this->getNomeTurma();
    }

    /**
     * Retorna uma string com o nome de todos os professores, separados por ",".
     * Utilizado na emissão de lista de presença.
     * @return string|null
     */
    public function getNomesProfessores() {
        if (!empty($this->professores)) {
            $nomes = '';
            foreach ($this->professores as $professor) {
                if ($professor instanceof Application_Model_Professor)
                    $nomes .= $professor->getNomeVoluntario() . ', ';
            }
            return substr($nomes, 0, -2);
        }
        return 'Não definido';
    }

    /**
     * Retorna uma string informando o horário da turma
     * @return string|null
     */
    public function horarioTurmaToString() {
        if (!empty($this->horario_inicio) && !empty($this->horario_termino))
            return 'De ' . $this->getHorarioInicio() . ' a ' . $this->getHorarioFim();
    }

    /**
     * Verifica se a turma está cancelada
     * @return boolean
     */
    public function isCancelada() {
        if ($this->status == Application_Model_Turma::$status_cancelada)
            return true;
        return false;
    }

    /**
     * Verifica se a turma é do período indicado por parâmetro
     * @param Application_Model_Periodo $periodo_atual
     * @return boolean
     */
    public function isAtual($periodo_atual) {
        return ($periodo_atual == $this->periodo);
    }

    /**
     * Retorna um array com as informações da turma.
     * Utilizado tanto para popular formulários de turma quanto para cadastro/alteração no banco de dados
     * @param boolean $isView Indica o formato de algumas informações
     * @return array
     */
    public function parseArray($isView = null) {
        $aux = array(
            'id_turma' => $this->getIdTurma($isView),
            'nome_turma' => $this->nome,
            'data_inicio' => $this->getDataInicio($isView),
            'data_fim' => $this->getDataFim($isView),
            'horario_inicio' => $this->getHorarioInicio(),
            'horario_fim' => $this->getHorarioFim(),
            'status' => $this->getStatus($isView),
            'sala' => $this->sala
        );

        if (is_null($isView)) {
            $aux['id_disciplina'] = $this->getDisciplina()->getIdDisciplina();
            $aux['id_periodo'] = $this->periodo->getIdPeriodo();
        }
        return $aux;
    }

}
