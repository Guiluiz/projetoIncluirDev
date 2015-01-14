<?php

class Application_Model_Aluno {

    public static $index_turma = 1;
    public static $index_liberacao_turma = 2;
    public static $index_pagamento_turma = 3;
    public static $index_aprovacao_turma = 4;
    public static $index_faltas_turma = 5;
    public static $index_notas_turma = 6;
    public static $status_ativo = 10;
    public static $status_desligado = 11;
    public static $aluno_sem_necessidade_liberacao_turma = 0;
    public static $aluno_turma_liberada = 1;
    public static $aluno_turma_prova_nivelamento = 2;
    public static $string_liberacoes = array(0 => '', 1 => 'Liberado', 2 => 'Prova de Nivelamento');
    private $id_aluno;
    private $nome;
    private $cpf;
    private $rg;
    private $data_nascimento;
    private $email;
    private $escolaridade;
    private $tel_fixo;
    private $tel_celular;
    private $endereco;
    private $bairro;
    private $numero;
    private $complemento;
    private $cep;
    private $cidade;
    private $estado;
    private $data_registro;
    private $is_cpf_responsavel;
    private $nome_responsavel;
    private $turmas;
    private $status;
    private $data_desligamento;
    private $motivo_desligamento;

    public function __construct($id_aluno, $nome = null, $cpf = null, $status = null, $data_desligamento = null, $motivo_desligamento = null, $rg = null, $data_nascimento = null, $email = null, $escolaridade = null, $tel_fixo = null, $tel_celular = null, $endereco = null, $bairro = null, $numero = null, $complemento = null, $cep = null, $cidade = null, $estado = null, $data_registro = null, $is_cpf_responsavel = null, $nome_responsavel = null, $pagamento = null, $turma = null, $aprovacao_turma = null, $liberacao_turma = null, $falta = null) {
        $this->id_aluno = ((!empty($id_aluno)) ? (int) $id_aluno : null);
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->rg = $rg;
        $this->data_nascimento = $this->parseDate($data_nascimento);
        $this->email = $email;
        $this->escolaridade = $escolaridade;
        $this->tel_fixo = $tel_fixo;
        $this->tel_celular = $tel_celular;
        $this->endereco = $endereco;
        $this->bairro = $bairro;
        $this->numero = $numero;
        $this->complemento = $complemento;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->cep = $cep;
        $this->data_registro = $this->parseDate($data_registro);
        $this->is_cpf_responsavel = $is_cpf_responsavel;
        $this->nome_responsavel = $nome_responsavel;
        $this->status = (int) $status;
        $this->turmas = array();
        $this->addTurma($turma, $liberacao_turma, $aprovacao_turma, $pagamento, $falta);
        $this->data_desligamento = $this->parseDate($data_desligamento);
        $this->motivo_desligamento = $motivo_desligamento;
    }

    public function getIdAluno($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_aluno);
        return $this->id_aluno;
    }

    public function getNomeAluno($isView = true) {
        if ($isView)
            return mb_strtoupper($this->nome, 'UTF-8');
        return $this->nome;
    }

    public function getCpf() {
        return $this->cpf;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function getMotivoDesligamento() {
        return $this->motivo_desligamento;
    }

    public function getDataNascimento($isView = null) {
        if (!empty($this->data_nascimento)) {
            if ($isView)
                return $this->data_nascimento->format('d/m/Y');
            return $this->data_nascimento->format('Y-m-d');
        }
        return null;
    }

    public function getDataRegistro($isView = null) {
        if (!empty($this->data_registro)) {
            if ($isView)
                return $this->data_registro->format('d/m/Y');
            return $this->data_registro->format('Y-m-d');
        }
        return null;
    }

    public function getNomeResponsavel($isView = true) {
        if ($isView)
            return mb_strtoupper($this->nome_responsavel, 'UTF-8');
        return $this->nome_responsavel;
    }

    public function getRg() {
        return $this->rg;
    }

    public function getEmail() {
        return strtolower($this->email);
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function getBairro() {
        return $this->bairro;
    }

    public function getComplemento() {
        return $this->complemento;
    }

    public function getCep() {
        return $this->cep;
    }

    public function getTelefoneFixo() {
        return $this->tel_fixo;
    }

    public function getTelefoneCelular() {
        return $this->tel_celular;
    }

    public function getEscolaridade() {
        return $this->escolaridade;
    }

    public function getNumeroEndereco() {
        return $this->numero;
    }

    public function getIsCpfResponsavel() {
        return $this->is_cpf_responsavel;
    }

    public function getCompleteEndereco() {
        if (!empty($this->endereco))
            return $this->endereco . ' ' . $this->numero . ' ' . $this->complemento;
    }

    public function limpaTurma() {
        $this->turmas = array();
    }

    public function hasTurmas() {
        if (count($this->turmas) > 0)
            return true;
        return false;
    }

    public function isAtivo() {
        if (!empty($this->status)) {
            if ($this->status == Application_Model_Aluno::$status_ativo)
                return true;
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

    public function getPorcentagemFaltas($id_turma, $total_aulas, $isView = null) {
        if (isset($this->turmas[$id_turma]) && is_int($total_aulas)) {
            $total_faltas = count($this->turmas[$id_turma][Application_Model_Aluno::$index_faltas_turma]);
            if (!empty($isView))
                return number_format(((($total_aulas - $total_faltas) / $total_aulas) * 100), 2, ',', '') . '%';
            return ($total_aulas - $total_faltas) / $total_aulas;
        }
        return null;
    }

    public function addTurma($turma, $liberacao = null, $aprovado = null, $pagamento = null, $faltas = null, $notas = null) {
        $liberacao = $this->parseLiberacao($liberacao);

        if ($turma instanceof Application_Model_Turma && !is_null($turma->getIdTurma()) && $this->isValidLiberacao($liberacao)) {
            if (!isset($this->turmas[$turma->getIdTurma()])) {
                $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_turma] = $turma;
                $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_liberacao_turma] = $liberacao;
                $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_aprovacao_turma] = $aprovado;
                $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_pagamento_turma] = null;
                $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_faltas_turma] = array();
                $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_notas_turma] = array();
            }
        }
        if (!empty($faltas) && !empty($turma))
            $this->addFalta($turma, $faltas);

        if (!empty($pagamento) && !empty($turma))
            $this->addPagamento($turma, $pagamento);

        if (!empty($notas) && !empty($turma))
            $this->addNota($turma, $notas);
    }

    public function addFalta($turma, $falta) {
        if (is_array($falta)) {
            foreach ($falta as $f) {
                if ($f instanceof Application_Model_Falta && $turma instanceof Application_Model_Turma) {
                    if (isset($this->turmas[$turma->getIdTurma()]))
                        $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_faltas_turma][$f->getData()->getTimestamp()] = $f;
                }
            }
        }
        else {
            if ($falta instanceof Application_Model_Falta && $turma instanceof Application_Model_Turma) {
                if (isset($this->turmas[$turma->getIdTurma()]))
                    $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_faltas_turma][$falta->getData()->getTimestamp()] = $falta;
            }
        }
    }

    public function addNota($turma, $nota) {
        if (is_array($nota)) {
            foreach ($nota as $n) {
                if ($n instanceof Application_Model_Nota && $turma instanceof Application_Model_Turma) {
                    if (isset($this->turmas[$turma->getIdTurma()]))
                        $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_notas_turma][$n->getIdNota()] = $n;
                }
            }
        }
        else {
            if ($nota instanceof Application_Model_Nota && $turma instanceof Application_Model_Turma) {
                if (isset($this->turmas[$turma->getIdTurma()]))
                    $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_notas_turma][$nota->getIdNota()] = $nota;
            }
        }
    }

    public function getNotaAcumulada($id_turma, $get_label = true) {
        if (isset($this->turmas[$id_turma]) && !empty($this->turmas[$id_turma][Application_Model_Aluno::$index_notas_turma])) {
            $nota_acumulada = 0;
            $total_atividades = 0;

            foreach ($this->turmas[$id_turma][Application_Model_Aluno::$index_notas_turma] as $nota) {
                if ($nota instanceof Application_Model_Nota) {
                    $nota_acumulada += $nota->getValor();
                    $total_atividades += $nota->getAtividade()->getValor();
                }
            }

            if ($get_label)
                return '(Nota Acumulada/Total Distribuído):<b>(' . $nota_acumulada . ' / ' . $total_atividades . ')</b>';
            return $nota_acumulada . ' / ' . $total_atividades;
        }
        return 'Não há nenhuma atividade do aluno na turma especificada';
    }

    public function addPagamento($turma, $pagamento) {
        if ($turma instanceof Application_Model_Turma && $pagamento instanceof Application_Model_Pagamento) {
            if (isset($this->turmas[$turma->getIdTurma()]))
                $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_pagamento_turma] = $pagamento;
        }
    }

    public function getTurmas() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma)
                $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma()] = $turma[Application_Model_Aluno::$index_turma];
        }
        return $aux;
    }

    public function getFaltas($is_array = null) {
        $aux = array();
        if ($this->hasTurmas()) {
            if (empty($is_array)) {
                foreach ($this->turmas as $turma)
                    $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_faltas_turma];
            } else {
                foreach ($this->turmas as $turma) {
                    $faltas = array();
                    if (!empty($turma[Application_Model_Aluno::$index_faltas_turma])) {
                        foreach ($turma[Application_Model_Aluno::$index_faltas_turma] as $falta)
                            $faltas[] = $falta->parseArray(true);
                    }
                    $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $faltas;
                }
            }
        }
        return $aux;
    }

    public function getNotas($is_array = null) {
        $aux = array();
        if ($this->hasTurmas()) {
            if (empty($is_array)) {
                foreach ($this->turmas as $turma)
                    $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_notas_turma];
            } else {
                foreach ($this->turmas as $turma) {
                    $notas = array();
                    if (!empty($turma[Application_Model_Aluno::$index_notas_turma])) {
                        foreach ($turma[Application_Model_Aluno::$index_notas_turma] as $nota)
                            $notas[] = $nota->parseArray(true, true);
                    }
                    $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $notas;
                }
            }
        }
        return $aux;
    }

    private function parseLiberacao($liberacao) {
        if (is_numeric($liberacao))
            return (int) $liberacao;

        foreach (Application_Model_Aluno::$string_liberacoes as $key => $val) {
            if ($liberacao == $val)
                return $key;
        }
        return null;
    }

    private function isValidLiberacao($liberacao) {
        if ($liberacao == Application_Model_Aluno::$aluno_sem_necessidade_liberacao_turma || $liberacao == Application_Model_Aluno::$aluno_turma_liberada || $liberacao == Application_Model_Aluno::$aluno_turma_prova_nivelamento)
            return true;

        return false;
    }

    public function getCompleteTurmas() {
        return $this->turmas;
    }

    public function getPagamentoTurma($turma) {
        if (isset($this->turmas[$turma]))
            return $this->turmas[$turma][Application_Model_Aluno::$index_pagamento_turma];
        return null;
    }

    public function getValoresPagamentos() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma) {
                if (!empty($turma[Application_Model_Aluno::$index_pagamento_turma])) {
                    if ($turma[Application_Model_Aluno::$index_pagamento_turma] instanceof Application_Model_Pagamento)
                        $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_pagamento_turma]->getValorPagamento();
                }
            }
        }
        return $aux;
    }

    public function getLiberacaoTurmas($isView = true) {
        $aux = array();
        if ($this->hasTurmas()) {
            if (!$isView) {
                foreach ($this->turmas as $turma)
                    $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_liberacao_turma];
            } else {
                foreach ($this->turmas as $turma)
                    $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = Application_Model_Aluno::$string_liberacoes[$turma[Application_Model_Aluno::$index_liberacao_turma]];
            }
        }
        return $aux;
    }

    public function getAprovacaoTurmas() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma)
                $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_aprovacao_turma];
        }
        return $aux;
    }

    public function getAlimentosPagamentos() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma) {
                if (!empty($turma[Application_Model_Aluno::$index_pagamento_turma])) {
                    if ($turma[Application_Model_Aluno::$index_pagamento_turma] instanceof Application_Model_Pagamento)
                        $aux = array_merge($aux, array($turma[Application_Model_Aluno::$index_turma]->getIdTurma(true) => $turma[Application_Model_Aluno::$index_pagamento_turma]->getAlimentosPagamento()));
                }
            }
        }
        return $aux;
    }

    private function parseDate($data) {
        if (!empty($data)) {
            if (strpos($data, '-') === false)
                return DateTime::createFromFormat('d/m/Y', $data);
            return new DateTime($data);
        }
        return null;
    }

    public function parseArray($isView = null) {
        $aux = array(
            'id_aluno' => $this->getIdAluno($isView),
            'nome_aluno' => $this->getNomeAluno(),
            'cpf' => $this->cpf,
            'rg' => $this->rg,
            'data_nascimento' => $this->getDataNascimento($isView),
            'email' => $this->getEmail(),
            'escolaridade' => $this->escolaridade,
            'telefone' => $this->tel_fixo,
            'celular' => $this->tel_celular,
            'endereco' => $this->endereco,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'cep' => $this->cep,
            'data_registro' => $this->getDataRegistro($isView),
            'is_cpf_responsavel' => $this->is_cpf_responsavel,
            'nome_responsavel' => $this->getNomeResponsavel()
        );

        if (empty($isView))
            $aux['status'] = $this->status;

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
            'status' => Application_Model_Aluno::$status_ativo
        );
    }

}
