<?php

/**
 * Classe para representação do aluno
 * @author Projeto Incluir
 */
class Application_Model_Aluno {

    public static $index_turma = 1;
    public static $index_liberacao_turma = 2;
    public static $index_pagamento_turma = 3;
    public static $index_aprovacao_turma = 4;
    public static $sexo_masculino = 0;
    public static $sexo_feminino = 1;
    public static $index_faltas_turma = 5;
    public static $index_notas_turma = 6;
    public static $status_ativo = 10;
    public static $status_desligado = 11;
    public static $aluno_sem_necessidade_liberacao_turma = 0;
    public static $aluno_turma_liberada = 1;
    public static $aluno_turma_prova_nivelamento = 2;
    public static $string_liberacoes = array(0 => '', 1 => 'Liberado', 2 => 'Prova de Nivelamento');
    public static $aluno_aprovado = 1;
    public static $aluno_reprovado = 0;
    public static $aluno_sem_status_aprovacao = null;
    
    /**
     *
     * @var int 
     */
    private $id_aluno;

    /**
     *
     * @var string 
     */
    private $nome;

    /**
     *
     * @var string 
     */
    private $cpf;

    /**
     *
     * @var string 
     */
    private $sexo;

    /**
     *
     * @var string 
     */
    private $rg;

    /**
     *
     * @var DateTime 
     */
    private $data_nascimento;

    /**
     *
     * @var string 
     */
    private $email;

    /**
     *
     * @var string 
     */
    private $escolaridade;

    /**
     *
     * @var string 
     */
    private $tel_fixo;

    /**
     *
     * @var string 
     */
    private $tel_celular;

    /**
     *
     * @var string 
     */
    private $endereco;

    /**
     *
     * @var string 
     */
    private $bairro;

    /**
     *
     * @var string 
     */
    private $numero;

    /**
     *
     * @var string 
     */
    private $complemento;

    /**
     *
     * @var string 
     */
    private $cep;

    /**
     *
     * @var string 
     */
    private $cidade;

    /**
     *
     * @var string 
     */
    private $estado;

    /**
     *
     * @var DateTime 
     */
    private $data_registro;

    /**
     *
     * @var boolean 
     */
    private $is_cpf_responsavel;

    /**
     *
     * @var string 
     */
    private $nome_responsavel;

    /**
     *
     * @var array 
     */
    private $turmas;

    /**
     *
     * @var int 
     */
    private $status;

    /**
     *
     * @var DateTime 
     */
    private $data_desligamento;

    /**
     *
     * @var string 
     */
    private $motivo_desligamento;

    public function __construct($id_aluno, $nome = null, $cpf = null, $status = null, $sexo = null, $data_desligamento = null, $motivo_desligamento = null, $rg = null, $data_nascimento = null, $email = null, $escolaridade = null, $tel_fixo = null, $tel_celular = null, $endereco = null, $bairro = null, $numero = null, $complemento = null, $cep = null, $cidade = null, $estado = null, $data_registro = null, $is_cpf_responsavel = null, $nome_responsavel = null, $pagamento = null, $turma = null, $aprovacao_turma = null, $liberacao_turma = null, $falta = null) {
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
        $this->sexo = $sexo;
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

    public function getSexo($isView = false) {
        if ($isView) {
            if (is_null($this->sexo))
                return 'não definido';
            
            elseif ((int) $this->sexo === Application_Model_Aluno::$sexo_masculino)
                return 'Masculino';
            
            else
                return 'Feminino';
        }

        return $this->sexo;
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

    /**
     * Retorna a data de nascimento do aluno
     * @param boolean $isView Indica o formato da data retornada
     * @return string
     */
    public function getDataNascimento($isView = null) {
        if (!empty($this->data_nascimento)) {
            if ($isView)
                return $this->data_nascimento->format('d/m/Y');
            return $this->data_nascimento->format('Y-m-d');
        }
        return null;
    }

    /**
     * Retorna a data de registro do aluno
     * @param boolean $isView Indica o formato da data retornada
     * @return string
     */
    public function getDataRegistro($isView = null) {
        if (!empty($this->data_registro)) {
            if ($isView)
                return $this->data_registro->format('d/m/Y');
            return $this->data_registro->format('Y-m-d');
        }
        return null;
    }

    /**
     * Retorna o nome do responsável
     * @param boolean $isView Indica como será o retorno do valor
     * @return string
     */
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

    /**
     * Retorna a porcentagem de faltas do aluno de acordo com os parâmetros indicados.
     * @param int $id_turma
     * @param int $total_aulas Total de aulas ministradas até o momento ou total de aulas do semestre, em caso de semetres anteriores
     * @param boolean $isView Indica como será o retorno (90% ou apenas o valor 0.9)
     * @return string
     */
    public function getPorcentagemFaltas($id_turma, $total_aulas, $isView = null) {
        if (isset($this->turmas[$id_turma]) && (int) $total_aulas > 0) {
            $total_faltas = count($this->turmas[$id_turma][Application_Model_Aluno::$index_faltas_turma]);

            if (!empty($isView))
                return number_format(((($total_aulas - $total_faltas) / $total_aulas) * 100), 2, ',', '') . '%';

            return ($total_aulas - $total_faltas) / $total_aulas;
        }
        return '-';
    }

    /**
     * Inclui uma turma para o aluno no array de turmas
     * @param Application_Model_Turma $turma
     * @param int|string $liberacao
     * @param int $aprovado
     * @param Application_Model_Pagamento|null $pagamento
     * @param Application_Model_Falta|Application_Model_Falta[]|null $faltas
     * @param Application_Model_Nota|Application_Model_Nota[]|null $notas
     */
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

            if (!empty($faltas) && !empty($turma))
                $this->addFalta($turma, $faltas);

            if (!empty($pagamento) && !empty($turma))
                $this->addPagamento($turma, $pagamento);

            if (!empty($notas) && !empty($turma))
                $this->addNota($turma, $notas);
        }
    }

    /**
     * Inclui uma ou mais faltas na turma indicada.
     * @param Application_Model_Turma $turma
     * @param Application_Model_Falta|Application_Model_Falta[] $falta
     */
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

    /**
     * Inclui uma ou mais notas na turma indicada.
     * @param Application_Model_Turma $turma
     * @param Application_Model_Nota|Application_Model_Nota[] $nota
     */
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

    /**
     * Retorna o somatório das notas do aluno na turma indicada por parâmetro.
     * @param int $id_turma
     * @param boolean $get_label Indica se a string retornada terá um label, além do somatório total do aluno e das atividades
     * @param boolean $only_nota Indica se apenas o somatório do aluno será retornado (sem o total das atividades)
     * @return string
     */
    public function getNotaAcumulada($id_turma, $get_label = true, $only_nota = false) {
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

            if (!$only_nota)
                return $nota_acumulada . ' / ' . $total_atividades;

            return $nota_acumulada;
        }
        return 'Não há nenhuma atividade do aluno na turma especificada';
    }

    /**
     * Inclui um pagamento para a turma indicada
     * @param Application_Model_Turma $turma
     * @param Application_Model_Pagamento $pagamento
     */
    public function addPagamento($turma, $pagamento) {
        if ($turma instanceof Application_Model_Turma && $pagamento instanceof Application_Model_Pagamento) {
            if (isset($this->turmas[$turma->getIdTurma()]))
                $this->turmas[$turma->getIdTurma()][Application_Model_Aluno::$index_pagamento_turma] = $pagamento;
        }
    }

    /**
     * Retorna um array contendo apenas os objetos de turma.
     * @return Application_Model_Turma[]
     */
    public function getTurmas() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma)
                $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma()] = $turma[Application_Model_Aluno::$index_turma];
        }
        return $aux;
    }

    /**
     * Retorna um array contendo todas as faltas do aluno
     * @param boolean $is_array Indica se será retornado um array com objetos de faltas ou um array contendo as informações das faltas(no último caso utilizado para respostas de requisições ajax para lançamento de frequência)
     * @return array|Application_Model_Falta[]
     */
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

    /**
     * Retorna um array contendo todas as notas do aluno
     * @param boolean $is_array Indica se será retornado um array com objetos de notas ou um array contendo as informações das notas(no último caso utilizado para respostas de requisições ajax para lançamento de notas)
     * @return array|Application_Model_Nota[]
     */
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

    /**
     * Converte a string da liberação em valor inteiro correspondente.
     * @param string|int $liberacao
     * @return int|null
     */
    private function parseLiberacao($liberacao) {
        if (is_numeric($liberacao))
            return (int) $liberacao;

        foreach (Application_Model_Aluno::$string_liberacoes as $key => $val) {
            if ($liberacao == $val)
                return $key;
        }
        return null;
    }

    /**
     * Indica se a liberação passada por parâmetro é válida 
     * @param int $liberacao
     * @return boolean
     */
    private function isValidLiberacao($liberacao) {
        if ($liberacao == Application_Model_Aluno::$aluno_sem_necessidade_liberacao_turma || $liberacao == Application_Model_Aluno::$aluno_turma_liberada || $liberacao == Application_Model_Aluno::$aluno_turma_prova_nivelamento)
            return true;

        return false;
    }

    /**
     * Retorna o array que armazen todas as turmas do aluno, incluindo informações sobre frequência, notas e pagamenntos
     * @return array
     */
    public function getCompleteTurmas() {
        return $this->turmas;
    }

    /**
     * Retorna o pagamento da turma indicada por parâmetro
     * @param int $turma
     * @return Application_Model_Pagamento|null
     */
    public function getPagamentoTurma($turma) {
        if (isset($this->turmas[$turma]))
            return $this->turmas[$turma][Application_Model_Aluno::$index_pagamento_turma];
        return null;
    }

    /**
     * Retorna um array com os valores dos pagamentos das turmas do aluno. 
     * Utilizado na construção da tabela de pagamentos do aluno, exibida na alteração do aluno.
     * @param int $isView Indica se a string da liberação será retornada ou o valor inteiro, conforme é armazenado no banco de dados.
     * @return array
     */
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

    /**
     * Retorna um array com as condições dos pagamentos das turmas do aluno. 
     * Utilizado na construção da tabela de pagamentos do aluno, exibida na alteração do aluno.
     * @param int $isView Indica se a string da liberação será retornada ou o valor inteiro, conforme é armazenado no banco de dados.
     * @return array
     */
    public function getCondicoesPagamentos() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma) {
                if (!empty($turma[Application_Model_Aluno::$index_pagamento_turma])) {
                    if ($turma[Application_Model_Aluno::$index_pagamento_turma] instanceof Application_Model_Pagamento)
                        $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_pagamento_turma]->getCondicaoPagamento();
                }
            }
        }
        return $aux;
    }

    /**
     * Retorna um array com os recibos dos pagamentos das turmas do aluno. 
     * Utilizado na construção da tabela de pagamentos do aluno, exibida na alteração do aluno.
     * @param int $isView Indica se a string da liberação será retornada ou o valor inteiro, conforme é armazenado no banco de dados.
     * @return array
     */
    public function getRecibosPagamentos() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma) {
                if (!empty($turma[Application_Model_Aluno::$index_pagamento_turma])) {
                    if ($turma[Application_Model_Aluno::$index_pagamento_turma] instanceof Application_Model_Pagamento)
                        $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_pagamento_turma]->getRecibo();
                }
            }
        }
        return $aux;
    }

    /**
     * Retorna um array com os tipos de isenção ou pendência (se houver) das turmas do aluno. 
     * Utilizado na construção da tabela de pagamentos do aluno, exibida na alteração do aluno.
     * @param int $isView Indica se a string da liberação será retornada ou o valor inteiro, conforme é armazenado no banco de dados.
     * @return array
     */
    public function getTipoIsencaoPendenciaPagamentos() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma) {
                if (!empty($turma[Application_Model_Aluno::$index_pagamento_turma])) {
                    if ($turma[Application_Model_Aluno::$index_pagamento_turma] instanceof Application_Model_Pagamento)
                        $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_pagamento_turma]->getTipoIsencaoPendencia();
                }
            }
        }
        return $aux;
    }

    /**
     * Retorna um array com as situações das turmas do aluno. 
     * Utilizado na construção da tabela de pagamentos do aluno, exibida na alteração do aluno.
     * @param int $isView Indica se a string da liberação será retornada ou o valor inteiro, conforme é armazenado no banco de dados.
     * @return array
     */
    public function getSituacoesPagamentos() {
        $aux = array();
        if ($this->hasTurmas()) {
            foreach ($this->turmas as $turma) {
                if (!empty($turma[Application_Model_Aluno::$index_pagamento_turma])) {
                    if ($turma[Application_Model_Aluno::$index_pagamento_turma] instanceof Application_Model_Pagamento)
                        $aux[$turma[Application_Model_Aluno::$index_turma]->getIdTurma(true)] = $turma[Application_Model_Aluno::$index_pagamento_turma]->getSituacao();
                }
            }
        }
        return $aux;
    }

    /**
     * Retorna um array com as liberações (prova de nivelamento ou liberado) das turmas do aluno. 
     * As liberações são necessárias quando o aluno não possui pré requisitos para cursar uma determinada disciplina.
     * Utilizado na construção da tabela de turmas do aluno, exibida na alteração do aluno.
     * @param int $isView Indica se a string da liberação será retornada ou o valor inteiro, conforme é armazenado no banco de dados.
     * @return array
     */
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

    /**
     * Retorna um array com os alimentos dos pagamentos das turmas do aluno.
     * Utilizado na construção da tabela de alimentos, exibida na alteração do aluno.
     * @return array
     */
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

    /**
     * Converte uma data em string para um objeto de DateTime
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
     * Retorna um array com as informações do aluno.
     * Utilizado tanto para popular formulários de aluno quanto para cadastro/alteração no banco de dados
     * @param boolean $isView Indica qual será o formato de alguns dos dados (id, data...)
     * @return array
     */
    public function parseArray($isView = null) {
        $aux = array(
            'id_aluno' => $this->getIdAluno($isView),
            'nome_aluno' => $this->getNomeAluno(),
            'cpf' => $this->cpf,
            'sexo' => $this->sexo,
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

    /**
     * Retorna um array com informações relacionadas ao desligamento do aluno
     * @return array
     */
    public function parseArrayDesligamento() {
        return array(
            'data_desligamento' => $this->getDataDesligamento(),
            'motivo_desligamento' => $this->motivo_desligamento,
            'status' => $this->status
        );
    }

    /**
     * Retorna um array com informações que devem ser alteradas no banco de dados para ativação do aluno
     * @return array
     */
    public static function parseArrayAtivacao() {
        return array(
            'data_desligamento' => null,
            'motivo_desligamento' => null,
            'status' => Application_Model_Aluno::$status_ativo
        );
    }

}
