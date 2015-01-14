<?php

/**
 * Classe para representar um voluntário do projeto
 * @author Projeto Incluir 
 */
class Application_Model_Voluntario {

    public static $atividade_aulas = 1;
    public static $atividade_secretaria = 2;
    public static $atividade_rh = 3;
    public static $atividade_marketing = 4;
    public static $atividade_informatica = 5;
    public static $status_ativo = 1;
    public static $status_inativo = 2;
    public static $status_desligado = 3;
    protected $id_voluntario;
    protected $nome;
    protected $cpf;
    protected $rg;
    protected $data_nascimento;
    protected $email;
    protected $formacao;
    protected $profissao;
    protected $tel_fixo;
    protected $tel_celular;
    protected $endereco;
    protected $bairro;
    protected $numero;
    protected $complemento;
    protected $cep;
    protected $cidade;
    protected $estado;
    protected $carga_horaria_prevista;
    protected $data_inicio;
    protected $data_desligamento;
    protected $motivo_desligamento;
    protected $status;
    protected $conhecimentos;
    protected $disponibilidade;
    protected $funcoes;
    protected $frequencia;

    public function __construct($id_voluntario, $nome = null, $cpf = null, $rg = null, $data_nascimento = null, $email = null, $formacao = null, $profissao = null, $tel_fixo = null, $tel_celular = null, $endereco = null, $bairro = null, $cidade = null, $estado = null, $numero = null, $complemento = null, $cep = null, $carga_prevista = null, $data_inicio = null, $data_desligamento = null, $motivo_desligamento = null, $status = null, $conhecimentos = null, $disponibilidade = null, $funcao = null, $tipo_funcao = null, $frequencia = null) {
        $this->id_voluntario = ((!empty($id_voluntario)) ? (int) $id_voluntario : null);
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->rg = $rg;
        $this->data_nascimento = $this->parseDate($data_nascimento);
        $this->email = $email;
        $this->formacao = $formacao;
        $this->profissao = $profissao;
        $this->tel_fixo = $tel_fixo;
        $this->tel_celular = $tel_celular;
        $this->endereco = $endereco;
        $this->bairro = $bairro;
        $this->numero = $numero;
        $this->complemento = $complemento;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->cep = $cep;
        $this->carga_horaria_prevista = $carga_prevista;
        $this->data_inicio = $this->parseDate($data_inicio);
        $this->data_desligamento = $this->parseDate($data_desligamento);
        $this->motivo_desligamento = $motivo_desligamento;
        $this->status = $status;
        $this->conhecimentos = $conhecimentos;
        $this->disponibilidade = $disponibilidade;

        $this->funcoes = array();
        $this->frequencia = array();

        $this->addFuncao($funcao, $tipo_funcao);
        $this->addFrequencia($frequencia);
    }

    public function getIdVoluntario($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_voluntario);
        return $this->id_voluntario;
    }

    public function getNomeVoluntario() {
        return mb_strtoupper($this->nome, 'UTF-8');
    }

    public function getCpf() {
        return $this->cpf;
    }

    public function getStatusVoluntario() {
        return $this->status;
    }

    public function addFuncao($funcao, $tipo_funcao) {
        if (!empty($funcao) && !empty($tipo_funcao))
            $this->funcoes[$tipo_funcao] = $funcao;
    }

    public function getDataNascimento($isView = null) {
        if (!empty($this->data_nascimento)) {
            if ($isView)
                return $this->data_nascimento->format('d/m/Y');
            return $this->data_nascimento->format('Y-m-d');
        }
        return null;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getTelefoneFixo() {
        return $this->tel_fixo;
    }

    public function getTelefoneCelular() {
        return $this->tel_celular;
    }

    public function getDataInicio($isView = null) {
        if (!empty($this->data_inicio)) {
            if ($isView)
                return $this->data_inicio->format('d/m/Y');
            return $this->data_inicio->format('Y-m-d');
        }
        return null;
    }

    public function getDataDesligamento($isView = null) {
        if (!empty($this->data_desligamento)) {
            if ($isView)
                return $this->data_desligamento->format('d/m/Y');
            return $this->data_desligamento->format('Y-m-d');
        }
        return null;
    }

    public function getDisponibilidade($isView = null) {
        if (!empty($this->disponibilidade)) {
            if ($isView)
                return $this->disponibilidade;
            return base64_encode(serialize($this->disponibilidade));
        }
        return null;
    }

    public function getFormacao() {
        return $this->formacao;
    }

    public function getCompleteEndereco() {
        if (!empty($this->endereco))
            return $this->endereco . ' ' . $this->numero . ' ' . $this->complemento;
        return '';
    }

    public function getBairro() {
        return $this->bairro;
    }

    public function getCep() {
        return $this->cep;
    }

    public function getEmail() {
        return strtolower($this->email);
    }

    public function getFuncoes() {
        return $this->funcoes;
    }

    public function getProfissao() {
        return $this->profissao;
    }

    public function getConhecimentos() {
        return $this->conhecimentos;
    }

    public function getStatus($isView = null) {
        if (!empty($isView))
            return base64_encode($this->status);
        return $this->status;
    }

    public function getCargaHoraria() {
        return $this->carga_horaria_prevista;
    }

    public static function getUnserializeData($value) {
        if (!empty($value))
            return unserialize(base64_decode($value));
        return null;
    }

    public function getMotivoDesligamento() {
        return $this->motivo_desligamento;
    }

    public function getFrequencias($is_array = null) {
        if (empty($is_array))
            return $this->frequencia;

        $array_frequencias = array();

        if (!empty($this->frequencia)) {
            foreach ($this->frequencia as $frequencia) {
                if ($frequencia instanceof Application_Model_EscalaFrequencia)
                    $array_frequencias[] = $frequencia->parseArray(true);
            }
        }

        return $array_frequencias;
    }

    public function getTotalHoras() {
        $array_total = array('horas' => 0, 'minutos' => 0);
        
        if (!empty($this->frequencia)) {
            $array_intervalos = array();
            
            foreach ($this->frequencia as $frequencia) {
                if ($frequencia instanceof Application_Model_EscalaFrequencia && $frequencia->getIsPresente()){
                    $hora_entrada = $frequencia->getHoraEntrada();
                    $hora_saida = $frequencia->getHoraSaida();
                    
                    if($hora_entrada instanceof DateTime && $hora_saida instanceof DateTime)
                        $array_intervalos[] = $hora_saida->diff($hora_entrada);
                }
            }
            
            foreach($array_intervalos as $interval){
                $array_total['horas'] += $interval->h;
                $array_total['minutos'] += $interval->i;
            }
            
        }
        return $array_total;
    }

    public function addFrequencia($frequencia) {
        if (is_array($frequencia)) {
            foreach ($frequencia as $f) {
                if ($f instanceof Application_Model_EscalaFrequencia)
                    $this->frequencia[] = $f;
            }
        }

        elseif ($frequencia instanceof Application_Model_EscalaFrequencia)
            $this->frequencia[] = $frequencia;
    }

    private function parseDate($data) {
        if (!empty($data)) {
            if (strpos($data, '-') === false)
                return DateTime::createFromFormat('d/m/Y', $data);
            return new DateTime($data);
        }
        return null;
    }

    public function isAtivo() {
        if ($this->status == Application_Model_Voluntario::$status_ativo)
            return true;
        return false;
    }

    public function parseArray($isView = null) {
        $aux = array(
            'id_voluntario' => $this->getIdVoluntario($isView),
            'nome' => $this->getNomeVoluntario(),
            'cpf' => $this->cpf,
            'rg' => $this->rg,
            'data_nascimento' => $this->getDataNascimento($isView),
            'email' => $this->getEmail(),
            'formacao' => $this->formacao,
            'profissao' => $this->profissao,
            'telefone_fixo' => $this->tel_fixo,
            'telefone_celular' => $this->tel_celular,
            'endereco' => $this->endereco,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'cep' => $this->cep,
            'carga_horaria' => $this->carga_horaria_prevista,
            'data_inicio' => $this->getDataInicio($isView),
            'status' => $this->getStatus($isView),
            'disponibilidade' => $this->getDisponibilidade($isView),
            'conhecimento' => $this->conhecimentos,
        );

        $tipos_tarefas = array(Application_Model_Voluntario::$atividade_informatica => 'funcao_informatica', Application_Model_Voluntario::$atividade_marketing => 'funcao_marketing', Application_Model_Voluntario::$atividade_rh => 'funcao_rh', Application_Model_Voluntario::$atividade_secretaria => 'funcao_secretaria');
        foreach ($tipos_tarefas as $key => $tipo)
            $aux[$tipo] = (!empty($this->funcoes[$key]) ? $this->funcoes[$key] : null);

        if (!empty($isView))
            $aux['atividades'] = array_keys($this->getFuncoes());

        return $aux;
    }

    public function parseArrayDesligamento() {
        return array(
            'data_desligamento' => $this->getDataDesligamento(),
            'motivo_desligamento' => $this->motivo_desligamento,
            'status' => $this->status
        );
    }

    public static function parseArrayAtivacao() {
        return array(
            'data_desligamento' => null,
            'motivo_desligamento' => null,
            'status' => Application_Model_Voluntario::$status_ativo
        );
    }

}
