<?php

class Application_Model_Mappers_Aluno {

    /**
     * @var Application_Model_DbTable_Aluno 
     */
    private $db_aluno;

    /**
     * Inclui um novo aluno no banco de dados
     * @param Application_Model_Aluno $aluno
     * @return boolean
     */
    public function addAluno($aluno) {
        try {
            if ($aluno instanceof Application_Model_Aluno) {
                if ($this->isValid($aluno)) {
                    if (!$this->db_aluno instanceof Application_Model_DbTable_Aluno)
                        $this->db_aluno = new Application_Model_DbTable_Aluno();

                    $id_aluno = $this->db_aluno->insert($aluno->parseArray());

                    if ($aluno->hasTurmas()) {
                        $db_pagamento = new Application_Model_DbTable_Pagamento();
                        $db_pagamento_alimentos = new Application_Model_DbTable_PagamentoAlimentos();
                        $db_aluno_turmas = new Application_Model_DbTable_TurmaAlunos();

                        foreach ($aluno->getCompleteTurmas() as $turma) {
                            $pagamento = $turma[Application_Model_Aluno::$index_pagamento_turma];

                            if ($pagamento instanceof Application_Model_Pagamento) {
                                $id_pagamento = $db_pagamento->insert($pagamento->parseArray());
                                $pagamento->setIdPagamento($id_pagamento);

                                if ($pagamento->hasAlimentos()) {
                                    foreach ($pagamento->getAlimentos() as $alimento)
                                        $db_pagamento_alimentos->insert(array('id_pagamento' => $id_pagamento, 'id_alimento' => $alimento[Application_Model_Pagamento::$index_alimento]->getIdAlimento(), 'quantidade' => $alimento[Application_Model_Pagamento::$index_quantidade_alimento]));
                                }
                            }
                            $db_aluno_turmas->insert(array('id_turma' => $turma[Application_Model_Aluno::$index_turma]->getIdTurma(), 'id_aluno' => $id_aluno, 'id_pagamento' => ($pagamento instanceof Application_Model_Pagamento) ? $pagamento->getIdPagamento() : null, 'aprovado' => $turma[Application_Model_Aluno::$index_aprovacao_turma], 'liberacao' => $turma[Application_Model_Aluno::$index_liberacao_turma]));
                        }
                    }
                    return true;
                }
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Altera um aluno no banco de dados
     * @param Application_Model_Aluno $aluno
     * @return boolean
     */
    public function alteraAluno($aluno) {
        try {
            if ($aluno instanceof Application_Model_Aluno) {
                if ($this->isValid($aluno)) {
                    if (!$this->db_aluno instanceof Application_Model_DbTable_Aluno)
                        $this->db_aluno = new Application_Model_DbTable_Aluno();

                    $this->db_aluno->update($aluno->parseArray(), $this->db_aluno->getAdapter()->quoteInto('id_aluno = ?', $aluno->getIdAluno()));

                    $this->removePagamentos($aluno->getIdAluno());
                    $this->removeTurmaAluno($aluno->getIdAluno());

                    $db_turmas_aluno = new Application_Model_DbTable_TurmaAlunos();

                    if ($aluno->hasTurmas()) {
                        $db_pagamento = new Application_Model_DbTable_Pagamento();
                        $db_pagamento_alimentos = new Application_Model_DbTable_PagamentoAlimentos();

                        foreach ($aluno->getCompleteTurmas() as $turma) {
                            $pagamento = $turma[Application_Model_Aluno::$index_pagamento_turma];

                            if ($pagamento instanceof Application_Model_Pagamento) {
                                $id_pagamento = $db_pagamento->insert($pagamento->parseArray());
                                $pagamento->setIdPagamento($id_pagamento);

                                if ($pagamento->hasAlimentos()) {
                                    foreach ($pagamento->getAlimentos() as $alimento)
                                        $db_pagamento_alimentos->insert(array('id_pagamento' => $id_pagamento, 'id_alimento' => $alimento[Application_Model_Pagamento::$index_alimento]->getIdAlimento(), 'quantidade' => $alimento[Application_Model_Pagamento::$index_quantidade_alimento]));
                                }
                            }

                            $db_turmas_aluno->insert(array('id_turma' => $turma[Application_Model_Aluno::$index_turma]->getIdTurma(), 'id_aluno' => $aluno->getIdAluno(), 'id_pagamento' => ($pagamento instanceof Application_Model_Pagamento) ? $pagamento->getIdPagamento() : null, 'aprovado' => $turma[Application_Model_Aluno::$index_aprovacao_turma], 'liberacao' => $turma[Application_Model_Aluno::$index_liberacao_turma]));
                        }
                    }
                    return true;
                }
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e;
            return false;
        }
    }

    /**
     * Exclui um aluno do banco de dados
     * @param int $id_aluno
     * @return boolean
     */
    public function deletaAluno($id_aluno) {
        try {
            $this->removePagamentos((int) $id_aluno, true);

            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $this->db_aluno->delete($this->db_aluno->getAdapter()->quoteInto('id_aluno = ?', (int) $id_aluno));

            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Remove as informações dos pagamentos do aluno passado por parâmetro
     * @param int $id_aluno
     * @param boolean $tudo Indica se apenas os pagamentos do semestre atual serão excluídos, ou todos os pagamentos
     * @return boolean
     * @throws Zend_Exception
     */
    private function removePagamentos($id_aluno, $tudo = false) {
        try {
            $db_turma_alunos = new Application_Model_DbTable_TurmaAlunos();
            $select = $db_turma_alunos->select()
                    ->setIntegrityCheck(false)
                    ->from('turma_alunos', array('id_pagamento'))
                    ->joinInner('turma', 'turma.id_turma = turma_alunos.id_turma')
                    ->where('id_aluno = ?', $id_aluno);

            if (!$tudo) {
                $select->joinInner('periodo', 'turma.id_periodo = periodo.id_periodo', array())
                        ->where('periodo.is_atual = ?', true);
            }

            $pagamentos = $db_turma_alunos->fetchAll($select)->toArray();

            if (!empty($pagamentos)) {
                $db_turma_alunos = new Application_Model_DbTable_Pagamento();
                $where = "( ";

                foreach ($pagamentos as $pagamento)
                    $where .= $db_turma_alunos->getAdapter()->quoteInto('id_pagamento = ?', (int) $pagamento['id_pagamento']) . " OR ";

                $where = substr($where, 0, -4) . ")";

                $db_turma_alunos->delete($where);
            }
            return true;
        } catch (Zend_Exception $e) {
            throw new Zend_Exception('Houve problemas');
        }
    }

    /**
     * Remove o aluno de suas turmas.
     * @param type $id_aluno
     * @param type $tudo Indica se o aluno será retirado somente das turmas do périodo atual ou de todos os outros.
     * @return boolean
     * @throws Zend_Exception
     */
    private function removeTurmaAluno($id_aluno, $tudo = false) {
        try {
            $db_turma_alunos = new Application_Model_DbTable_TurmaAlunos();
            $select = $db_turma_alunos->select()
                    ->setIntegrityCheck(false)
                    ->from('turma_alunos', array('id_turma_aluno'))
                    ->joinInner('turma', 'turma.id_turma = turma_alunos.id_turma')
                    ->where('id_aluno = ?', $id_aluno);

            if (!$tudo) {
                $select->joinInner('periodo', 'turma.id_periodo = periodo.id_periodo', array())
                        ->where('periodo.is_atual = ?', true);
            }

            $turma_alunos = $db_turma_alunos->fetchAll($select)->toArray();

            if (!empty($turma_alunos)) {
                $where = "( ";

                foreach ($turma_alunos as $turma_aluno)
                    $where .= $db_turma_alunos->getAdapter()->quoteInto('id_turma_aluno = ?', (int) $turma_aluno['id_turma_aluno']) . " OR ";

                $where = substr($where, 0, -4) . ")";

                $db_turma_alunos->delete($where);
            }
            return true;
        } catch (Zend_Exception $e) {
            throw new Zend_Exception('Houve problemas');
        }
    }

    /**
     * Método auxiliar para atualizar status de pagamentos de alunos. Utilizado somente quando há necessidade (deve ser atualizado para novo padrão de pagamentos)
     * @param Application_Model_Periodo $periodo
     * @return boolean
      public function updatePagamentos($periodo) {
      @ini_set('memory_limit', '512M');
      try {
      if ($periodo instanceof Application_Model_Periodo) {
      $db_turma_alunos = new Application_Model_DbTable_TurmaAlunos();

      $select = $db_turma_alunos->select()
      ->setIntegrityCheck(false)
      ->from('turma_alunos', array('id_pagamento'))
      ->joinInner('aluno', 'aluno.id_aluno = turma_alunos.id_aluno', array('nome_aluno'))
      ->joinInner('pagamento', 'pagamento.id_pagamento = turma_alunos.id_pagamento', array('valor_pago', 'situacao'))
      ->joinInner('pagamento_alimentos', 'pagamento.id_pagamento = pagamento_alimentos.id_pagamento', array('quantidade', 'id_alimento'))
      ->joinInner('turma', 'turma.id_turma = turma_alunos.id_turma', array('nome_turma'))
      ->where('id_periodo = ?', $periodo->getIdPeriodo())
      ->order('aluno.nome_aluno ASC');

      $pagamentos = $db_turma_alunos->fetchAll($select);

      if (!empty($pagamentos)) {
      $array_pagamentos = array();
      $db_pagamento = new Application_Model_DbTable_Pagamento();

      foreach ($pagamentos as $pagamento) {
      if (!isset($array_pagamentos[$pagamento->id_pagamento])) {
      $array_pagamentos[$pagamento->id_pagamento]['nome_aluno'] = $pagamento->nome_aluno;
      $array_pagamentos[$pagamento->id_pagamento]['valor'] = $pagamento->valor_pago;
      $array_pagamentos[$pagamento->id_pagamento]['quantidade'] = 0;
      $array_pagamentos[$pagamento->id_pagamento]['situacao_antiga'] = $pagamento->situacao;
      $array_pagamentos[$pagamento->id_pagamento]['nome_turma'] = $pagamento->nome_turma;
      }

      if (!isset($array_pagamentos[$pagamento->id_pagamento][$pagamento->id_alimento])) {
      $array_pagamentos[$pagamento->id_pagamento]['quantidade'] += $pagamento->quantidade;
      $array_pagamentos[$pagamento->id_pagamento][$pagamento->id_alimento] = true;
      }
      }

      foreach ($array_pagamentos as $id => $pagamento) {
      if ($pagamento['valor'] >= $periodo->getValorLiberacao() && $pagamento['quantidade'] >= $periodo->getQuantidadeAlimentos()) {
      $situacao = Application_Model_Pagamento::$pagamento_liberado;
      // echo 'Pagamento do Aluno(a) <b>' . mb_strtoupper($pagamento['nome_aluno'], 'UTF-8') . '</b> na turma <b>' . $pagamento['nome_turma'] . '</b> [<b>R$' . number_format($pagamento['valor'], 2, ',', '') . '</b> - <b>' . $pagamento['quantidade'] . '</b> alimento(s)] foi <b>Liberado</b>';
      } else {
      $situacao = Application_Model_Pagamento::$pagamento_pendente;
      //  echo 'Pagamento do Aluno(a) <b>' . mb_strtoupper($pagamento['nome_aluno'], 'UTF-8') . '</b> na turma <b>' . $pagamento['nome_turma'] . '</b> [<b>R$' . number_format($pagamento['valor'], 2, ',', '') . '</b> - <b>' . $pagamento['quantidade'] . '</b> alimento(s)] está <b>Pendente</b>';
      }

      //echo '<br><br>';
      $db_pagamento->update(array('situacao' => $situacao), $db_pagamento->getAdapter()->quoteInto('id_pagamento = ?', $id));
      }
      }
      }
      return false;
      } catch (Zend_Exception $e) {
      echo $e;
      return false;
      }
      }
     */

    /**
     * Busca os alunos de acordo com os filtros passados por parâmetro
     * @param array $filtros_busca
     * @param boolean $paginator Verifica se o resultado será ou não paginado
     * @return \Zend_Paginator|null|\Application_Model_Aluno
     */
    public function buscaAlunos($filtros_busca = null, $paginator = null) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $select = $this->db_aluno->select()->from('aluno', array('id_aluno', 'nome_aluno', 'cpf', 'status'));

            if (!empty($filtros_busca['nome_aluno']))
                $select->where('nome_aluno LIKE ?', '%' . $filtros_busca['nome_aluno'] . '%');

            if (!empty($filtros_busca['rg']))
                $select->where('rg = ?', $filtros_busca['id_curso']);

            if (!empty($filtros_busca['cpf'])) {
                $select->where('cpf = ?', $filtros_busca['cpf']);

                if (!empty($filtros_busca['is_responsavel']))
                    $select->where('is_cpf_responsavel = ?', $filtros_busca['is_responsavel']);
            }
            if (empty($paginator)) {
                $alunos = $this->db_aluno->fetchAll($select->order('nome_aluno'));
                if (!empty($alunos)) {
                    $array_alunos = array();

                    foreach ($alunos as $aluno)
                        $array_alunos[] = new Application_Model_Aluno($aluno->id_aluno, $aluno->nome_aluno, $aluno->cpf, $aluno->status);

                    return $array_alunos;
                }
                return null;
            }
            return new Zend_Paginator(new Application_Model_Paginator_Aluno($select->order('nome_aluno')));
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Busca um aluno completo, ou com as informações do período especificado
     * @param int $id_aluno
     * @param int $periodo
     * @return null|\Application_Model_Aluno
     */
    public function buscaAlunosByID($id_aluno, $periodo = null) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $select = $this->db_aluno->select()
                    ->setIntegrityCheck(false)
                    ->from('aluno')
                    ->joinLeft('turma_alunos', 'turma_alunos.id_aluno = aluno.id_aluno', array('id_pagamento', 'aprovado', 'liberacao'))
                    ->joinLeft('nota_aluno', 'turma_alunos.id_turma_aluno = nota_aluno.id_turma_aluno', array('id_nota', 'valor_nota'))
                    ->joinLeft('turma_atividades', 'turma_atividades.id_atividades_turma = nota_aluno.id_atividades_turma', array())
                    ->joinLeft('atividade', 'turma_atividades.id_atividade = atividade.id_atividade', array('id_atividade', 'nome', 'valor_total', 'data_funcionamento as data_atividade'))
                    ->joinLeft('falta', 'turma_alunos.id_turma_aluno = falta.id_turma_aluno', array('id_falta', 'data_funcionamento', 'observacao'))
                    ->joinLeft('pagamento', 'turma_alunos.id_pagamento = pagamento.id_pagamento', array('situacao', 'valor_pago', 'condicao', 'tipo_isencao_pendencia', 'num_recibo'))
                    ->joinLeft('pagamento_alimentos', 'turma_alunos.id_pagamento = pagamento_alimentos.id_pagamento', array('id_alimento', 'quantidade'))
                    ->joinLeft('alimento', 'pagamento_alimentos.id_alimento = alimento.id_alimento', array('nome_alimento'));

            $query = '';
            if (!empty($periodo))
                $query = $this->db_aluno->getAdapter()->quoteInto(' AND id_periodo = ?', (int) $periodo);

            $select->joinLeft('turma', 'turma.id_turma = turma_alunos.id_turma' . $query, array('id_turma', 'nome_turma', 'id_disciplina', 'id_periodo', 'data_inicio', 'data_fim', 'horario_inicio', 'horario_fim'))
                    ->joinLeft('disciplina', 'turma.id_disciplina = disciplina.id_disciplina', array('nome_disciplina', 'id_curso'))
                    ->joinLeft('curso', 'disciplina.id_curso = curso.id_curso', array('nome_curso'))
                    ->where('aluno.id_aluno = ?', (int) $id_aluno);

            $alunos = $this->db_aluno->fetchAll($select);

            if (!empty($alunos)) {
                $array_alunos = array();
                $array_pagamentos = array();
                $array_faltas = array();
                $array_notas = array();

                $inf_aluno = null;

                foreach ($alunos as $inf_aluno) {
                    if (!isset($array_alunos[$inf_aluno->id_aluno]))
                        $array_alunos[$inf_aluno->id_aluno] = new Application_Model_Aluno($inf_aluno->id_aluno, $inf_aluno->nome_aluno, $inf_aluno->cpf, $inf_aluno->status, $inf_aluno->sexo, $inf_aluno->data_desligamento, $inf_aluno->motivo_desligamento, $inf_aluno->rg, $inf_aluno->data_nascimento, $inf_aluno->email, $inf_aluno->escolaridade, $inf_aluno->telefone, $inf_aluno->celular, $inf_aluno->endereco, $inf_aluno->bairro, $inf_aluno->numero, $inf_aluno->complemento, $inf_aluno->cep, $inf_aluno->cidade, $inf_aluno->estado, $inf_aluno->data_registro, $inf_aluno->is_cpf_responsavel, $inf_aluno->nome_responsavel, null, new Application_Model_Turma($inf_aluno->id_turma, $inf_aluno->nome_turma, $inf_aluno->data_inicio, $inf_aluno->data_fim, $inf_aluno->horario_inicio, $inf_aluno->horario_fim, new Application_Model_Disciplina($inf_aluno->id_disciplina, $inf_aluno->nome_disciplina, null, new Application_Model_Curso($inf_aluno->id_curso, $inf_aluno->nome_curso)), null, null, new Application_Model_Periodo($inf_aluno->id_periodo)), $inf_aluno->aprovado, $inf_aluno->liberacao, null);
                    else
                        $array_alunos[$inf_aluno->id_aluno]->addTurma(new Application_Model_Turma($inf_aluno->id_turma, $inf_aluno->nome_turma, $inf_aluno->data_inicio, $inf_aluno->data_fim, $inf_aluno->horario_inicio, $inf_aluno->horario_fim, new Application_Model_Disciplina($inf_aluno->id_disciplina, $inf_aluno->nome_disciplina, null, new Application_Model_Curso($inf_aluno->id_curso, $inf_aluno->nome_curso)), null, null, new Application_Model_Periodo($inf_aluno->id_periodo)), $inf_aluno->liberacao, $inf_aluno->aprovado);

                    if (!empty($inf_aluno->id_pagamento)) {
                        if (!isset($array_pagamentos[$inf_aluno->id_turma][$inf_aluno->id_pagamento])) {
                            if (!empty($inf_aluno->id_alimento))
                                $array_pagamentos[$inf_aluno->id_turma][$inf_aluno->id_pagamento] = new Application_Model_Pagamento($inf_aluno->id_pagamento, $inf_aluno->situacao, $inf_aluno->valor_pago, new Application_Model_Alimento($inf_aluno->id_alimento, $inf_aluno->nome_alimento), $inf_aluno->quantidade, $inf_aluno->condicao, $inf_aluno->tipo_isencao_pendencia, $inf_aluno->num_recibo);
                            else
                                $array_pagamentos[$inf_aluno->id_turma][$inf_aluno->id_pagamento] = new Application_Model_Pagamento($inf_aluno->id_pagamento, $inf_aluno->situacao, $inf_aluno->valor_pago, null, null, $inf_aluno->condicao, $inf_aluno->tipo_isencao_pendencia, $inf_aluno->num_recibo);
                        } else
                            $array_pagamentos[$inf_aluno->id_turma][$inf_aluno->id_pagamento]->addAlimento(new Application_Model_Alimento($inf_aluno->id_alimento, $inf_aluno->nome_alimento), $inf_aluno->quantidade);
                    }

                    if (!empty($inf_aluno->id_falta)) {
                        if (!isset($array_faltas[$inf_aluno->id_turma][$inf_aluno->id_falta]))
                            $array_faltas[$inf_aluno->id_turma][$inf_aluno->id_falta] = new Application_Model_Falta($inf_aluno->id_falta, $inf_aluno->data_funcionamento, $inf_aluno->observacao);
                    }
                    if (!empty($inf_aluno->id_nota)) {
                        if (!isset($array_notas[$inf_aluno->id_turma][$inf_aluno->id_nota]))
                            $array_notas[$inf_aluno->id_turma][$inf_aluno->id_nota] = new Application_Model_Nota($inf_aluno->id_nota, new Application_Model_Atividade($inf_aluno->id_atividade, null, $inf_aluno->nome, $inf_aluno->valor_total, null, $inf_aluno->data_atividade), $inf_aluno->valor_nota);
                    }
                }

                if (!empty($inf_aluno)) {
                    if (!empty($array_pagamentos)) {
                        foreach ($array_pagamentos as $id_turma => $pagamentos) {
                            foreach ($pagamentos as $pagamento)
                                $array_alunos[$inf_aluno->id_aluno]->addPagamento(new Application_Model_Turma($id_turma), $pagamento);
                        }
                    }

                    if (!empty($array_faltas)) {
                        foreach ($array_faltas as $id_turma => $faltas) {
                            foreach ($faltas as $falta)
                                $array_alunos[$inf_aluno->id_aluno]->addFalta(new Application_Model_Turma($id_turma), $falta);
                        }
                    }

                    if (!empty($array_notas)) {
                        foreach ($array_notas as $id_turma => $notas) {
                            foreach ($notas as $nota)
                                $array_alunos[$inf_aluno->id_aluno]->addNota(new Application_Model_Turma($id_turma), $nota);
                        }
                    }
                    return $array_alunos[$inf_aluno->id_aluno];
                }
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Verifica se o cadastro do aluno é válido. 
     * Não podem haver alunos, com o mesmo cpf, a não ser que seja o cpf de um responsável.
     * O responsável também pode ser cadastrado.
     * 
     * @param Application_Model_Aluno $aluno
     * @return boolean
     * @throws Zend_Exception
     */
    private function isValid($aluno) {
        try {
            if ($aluno instanceof Application_Model_Aluno) {
                $responsavel = $this->getResponsavelCpf($aluno->getCpf(), $aluno->getIdAluno());

                if (!empty($responsavel) && $responsavel != $aluno->getNomeAluno() && !$aluno->getIsCpfResponsavel())
                    return false;

                if (!empty($responsavel) && $aluno->getIsCpfResponsavel() && $aluno->getNomeResponsavel() != $responsavel)
                    return false;

                if (!empty($responsavel) && $aluno->getIsCpfResponsavel() && $aluno->getNomeResponsavel() == $responsavel)
                    return true;

                if (!$this->db_aluno instanceof Application_Model_DbTable_Aluno)
                    $this->db_aluno = new Application_Model_DbTable_Aluno();

                $select = $this->db_aluno->select()
                        ->where('cpf = ?', $aluno->getCpf())
                        ->where('is_cpf_responsavel = ?', false)
                        ->where('removeInvalidsCharacters(nome_aluno) <> ?', $aluno->getNomeResponsavel());

                if (!is_null($aluno->getIdAluno()))
                    $select->where('id_aluno <> ?', $aluno->getIdAluno());

                if (count($this->db_aluno->fetchAll($select)->toArray()) > 0)
                    return false;
                return true;
            }
            return false;
        } catch (Zend_Exception $e) {
            throw $e;
        }
    }

    /**
     * Verifica se existe algum responsável com o cpf especificado.
     * @param string $cpf
     * @param int $exclude Informa o id que será excluído da busca
     * @return string/null
     * @throws Zend_Exception
     */
    private function getResponsavelCpf($cpf, $exclude = null) {
        try {
            if (!empty($cpf)) {
                $filter_string = new Aplicacao_Filtros_StringSimpleFilter();

                if (!$this->db_aluno instanceof Application_Model_DbTable_Aluno)
                    $this->db_aluno = new Application_Model_DbTable_Aluno();

                $select = $this->db_aluno->select()
                        ->from('aluno', array('nome_responsavel'))
                        ->where('cpf = ?', $cpf);

                if (!empty($exclude))
                    $select->where('id_aluno <> ?', (int) $exclude);

                $responsavel = $this->db_aluno->fetchRow($select);

                if (!empty($responsavel))
                    return mb_strtoupper($filter_string->filter($responsavel->nome_responsavel), 'UTF-8');
                return null;
            }
            throw new Zend_Exception();
        } catch (Zend_Exception $e) {
            throw $e;
        }
    }

    /**
     * Separa os alunos nas suas respectivas turmas [um array para cada turma] método utilizado para a geração de relatórios
     * @param array $turmas Turmas que serão buscadas
     * @param boolean $atual Indica se são somente turmas do semestre atual
     * @param int $periodo Indica qual período a busca será realizada
     * @param boolean $get_faltas Indica se as faltas serão buscadas
     * @return array
     */
    public function getAlunosOrganizadosByTurma($turmas, $atual = false, $periodo = null, $get_faltas = false) {
        $alunos = $this->getAlunos($turmas, true, false, $atual, $periodo);
        $array_alunos = array();

        foreach ($alunos as $aluno) {
            foreach ($aluno->getCompleteTurmas() as $turma) {
                $aux = clone $aluno;
                $aux->limpaTurma();
                $aux->addTurma($turma[Application_Model_Aluno::$index_turma], $turma[Application_Model_Aluno::$index_liberacao_turma], $turma[Application_Model_Aluno::$index_aprovacao_turma], $turma[Application_Model_Aluno::$index_pagamento_turma], (($get_faltas) ? $turma[Application_Model_Aluno::$index_faltas_turma] : null));
                $array_alunos[$turma[Application_Model_Aluno::$index_turma]->getIdTurma()][] = $aux;

                usort($array_alunos[$turma[Application_Model_Aluno::$index_turma]->getIdTurma()], function ($a, $b) {
                    return strcmp($a->getNomeAluno(), $b->getNomeAluno());
                });
            }
        }

        return $array_alunos;
    }

    /**
     * Mantém todos os alunos em um mesmo array método utilizado para a geração de relatórios
     * @param array $turmas Turmas que serão buscadas
     * @param boolean $atual Indica se são somente turmas do semestre atual
     * @param int $periodo Indica qual período a busca será realizada
     * @param boolean $get_faltas Indica se as faltas serão buscadas
     * @return array
     */
    public function getAlunosTurmaUnicoArray($turmas, $atual = false, $periodo = null) {
        $alunos = $this->getAlunos($turmas, true, false, $atual, $periodo);
        $array_alunos = array();

        foreach ($alunos as $aluno) {
            foreach ($aluno->getCompleteTurmas() as $turma) {
                $aux = clone $aluno;
                $aux->limpaTurma();
                $aux->addTurma($turma[Application_Model_Aluno::$index_turma], $turma[Application_Model_Aluno::$index_liberacao_turma], $turma[Application_Model_Aluno::$index_aprovacao_turma], $turma[Application_Model_Aluno::$index_pagamento_turma], $turma[Application_Model_Aluno::$index_faltas_turma], $turma[Application_Model_Aluno::$index_notas_turma]);
                $array_alunos[] = $aux;

                usort($array_alunos, function ($a, $b) {
                    return strcmp($a->getNomeAluno(), $b->getNomeAluno());
                });
            }
        }
        return $array_alunos;
    }

    /**
     * Retorna um array com os alunos da turma especificada
     * @param int $turma
     * @return null|\Application_Model_Aluno[]
     */
    public function getAlunosByTurma($turma) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $select = $this->db_aluno->select()
                    ->setIntegrityCheck(false)
                    ->from('aluno', array('id_aluno', 'nome_aluno', 'cpf', 'status'))
                    ->joinLeft('turma_alunos', 'turma_alunos.id_aluno = aluno.id_aluno', array('id_turma', 'liberacao'))
                    ->where('turma_alunos.id_turma = ?', (int) $turma)
                    ->where('aluno.status = ?', Application_Model_Aluno::$status_ativo)
                    ->order('aluno.nome_aluno ASC');

            $alunos = $this->db_aluno->fetchAll($select);

            if (!empty($alunos)) {
                $array_alunos = array();

                foreach ($alunos as $inf_aluno) {
                    if (!isset($array_alunos[$inf_aluno->id_aluno]))
                        $array_alunos[$inf_aluno->id_aluno] = new Application_Model_Aluno($inf_aluno->id_aluno, $inf_aluno->nome_aluno, $inf_aluno->cpf, $inf_aluno->status, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, new Application_Model_Turma($inf_aluno->id_turma));
                    else
                        $array_alunos[$inf_aluno->id_aluno]->addTurma(new Application_Model_Turma($inf_aluno->id_turma), $inf_aluno->liberacao);
                }
                return $array_alunos;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna um array com os alunos da turma especificada, além das suas informações sobre as suas notas
     * Utilizado no lançamento de notas
     * @param int $turma
     * @return null|\Application_Model_Aluno[]
     */
    public function getAlunosNotas($turma) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $select = $this->db_aluno->select()
                    ->setIntegrityCheck(false)
                    ->from('aluno', array('id_aluno', 'nome_aluno', 'cpf', 'status'))
                    ->joinLeft('turma_alunos', 'turma_alunos.id_aluno = aluno.id_aluno', array('id_turma'))
                    ->joinLeft('nota_aluno', 'nota_aluno.id_turma_aluno = turma_alunos.id_turma_aluno', array('id_nota', 'valor_nota'))
                    ->joinLeft('turma_atividades', 'nota_aluno.id_atividades_turma = turma_atividades.id_atividades_turma', array('id_atividade'))
                    ->joinLeft('atividade', 'nota_aluno.id_turma_aluno = turma_alunos.id_turma_aluno', array())
                    ->joinLeft('turma', 'turma.id_turma = turma_alunos.id_turma', array('nome_turma', 'id_disciplina'))
                    ->where('turma.id_turma = ?', (int) $turma)
                    ->where('aluno.status = ?', Application_Model_Aluno::$status_ativo)
                    ->order('aluno.nome_aluno ASC');

            $alunos = $this->db_aluno->fetchAll($select);

            if (!empty($alunos)) {
                $array_alunos = array();
                $array_notas = array();

                foreach ($alunos as $inf_aluno) {
                    if (!isset($array_alunos[$inf_aluno->id_aluno]))
                        $array_alunos[$inf_aluno->id_aluno] = new Application_Model_Aluno($inf_aluno->id_aluno, $inf_aluno->nome_aluno, $inf_aluno->cpf, $inf_aluno->status, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, new Application_Model_Turma($inf_aluno->id_turma, $inf_aluno->nome_turma));
                    else
                        $array_alunos[$inf_aluno->id_aluno]->addTurma(new Application_Model_Turma($inf_aluno->id_turma, $inf_aluno->nome_turma));

                    if (!empty($inf_aluno->id_nota)) {
                        if (!isset($array_notas[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_nota]))
                            $array_notas[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_nota] = new Application_Model_Nota($inf_aluno->id_nota, new Application_Model_Atividade($inf_aluno->id_atividade), $inf_aluno->valor_nota);
                    }
                }
                foreach ($array_alunos as $id_aluno => &$aluno) {
                    if (isset($array_notas[$id_aluno])) {
                        foreach ($array_notas[$id_aluno] as $id_turma => $notas) {
                            foreach ($notas as $nota)
                                $aluno->addNota(new Application_Model_Turma($id_turma), $nota);
                        }
                    }
                }

                return $array_alunos;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna um array com os alunos da turma especificada, além das suas informações sobre as suas faltas
     * Utilizado no lançamento de frequência
     * @param int $turma
     * @return null|\Application_Model_Aluno[]
     */
    public function getAlunosFaltas($turma) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $select = $this->db_aluno->select()
                    ->setIntegrityCheck(false)
                    ->from('aluno', array('id_aluno', 'nome_aluno', 'cpf', 'status'))
                    ->joinLeft('turma_alunos', 'turma_alunos.id_aluno = aluno.id_aluno', array('id_turma'))
                    ->joinLeft('falta', 'turma_alunos.id_turma_aluno = falta.id_turma_aluno', array('id_falta', 'data_funcionamento', 'observacao'))
                    ->joinLeft('turma', 'turma.id_turma = turma_alunos.id_turma', array('nome_turma', 'id_disciplina'))
                    ->where('turma.id_turma = ?', (int) $turma)
                    ->where('aluno.status = ?', Application_Model_Aluno::$status_ativo)
                    ->order('aluno.nome_aluno ASC');

            $alunos = $this->db_aluno->fetchAll($select);

            if (!empty($alunos)) {
                $array_alunos = array();
                $array_faltas = array();

                foreach ($alunos as $inf_aluno) {
                    if (!isset($array_alunos[$inf_aluno->id_aluno]))
                        $array_alunos[$inf_aluno->id_aluno] = new Application_Model_Aluno($inf_aluno->id_aluno, $inf_aluno->nome_aluno, $inf_aluno->cpf, $inf_aluno->status, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, new Application_Model_Turma($inf_aluno->id_turma, $inf_aluno->nome_turma));
                    else
                        $array_alunos[$inf_aluno->id_aluno]->addTurma(new Application_Model_Turma($inf_aluno->id_turma, $inf_aluno->nome_turma));

                    if (!empty($inf_aluno->id_falta)) {
                        if (!isset($array_faltas[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_falta]))
                            $array_faltas[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_falta] = new Application_Model_Falta($inf_aluno->id_falta, $inf_aluno->data_funcionamento, $inf_aluno->observacao);
                    }
                }

                foreach ($array_alunos as $id_aluno => &$aluno) {
                    if (isset($array_faltas[$id_aluno])) {
                        foreach ($array_faltas[$id_aluno] as $id_turma => $faltas) {
                            foreach ($faltas as $falta)
                                $aluno->addFalta(new Application_Model_Turma($id_turma), $falta);
                        }
                    }
                }

                return $array_alunos;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna os alunos de acordo com os parâmetros especificados
     * @param array $turmas Indica as turmas que os alunos serão buscados
     * @param boolean $active Indica se apenas alunos ativos serão retornados 
     * @param boolean $order Indica se eles serão ordenados alfabeticamente pelo nome
     * @param boolean $atual Indica se serão somente turmas/alunos do semestre atual
     * @param int $periodo Indica de qual período as turmas/alunos serão buscados
     * @return null|\Application_Model_Aluno
     */
    public function getAlunos($turmas = null, $active = true, $order = false, $atual = null, $periodo = null) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $select = $this->db_aluno->select()
                    ->setIntegrityCheck(false)
                    ->from('aluno')
                    ->joinLeft('turma_alunos', 'turma_alunos.id_aluno = aluno.id_aluno', array('id_turma', 'id_pagamento', 'aprovado', 'liberacao'))
                    ->joinLeft('falta', 'turma_alunos.id_turma_aluno = falta.id_turma_aluno', array('id_falta', 'data_funcionamento', 'observacao'))
                    ->joinLeft('nota_aluno', 'turma_alunos.id_turma_aluno = nota_aluno.id_turma_aluno', array('id_nota', 'valor_nota'))
                    ->joinLeft('turma_atividades', 'turma_atividades.id_atividades_turma = nota_aluno.id_atividades_turma', array())
                    ->joinLeft('atividade', 'turma_atividades.id_atividade = atividade.id_atividade', array('id_atividade', 'nome', 'valor_total'))
                    ->joinLeft('pagamento', 'turma_alunos.id_pagamento = pagamento.id_pagamento', array('situacao', 'valor_pago', 'condicao', 'tipo_isencao_pendencia', 'num_recibo'))
                    ->joinLeft('pagamento_alimentos', 'turma_alunos.id_pagamento = pagamento_alimentos.id_pagamento', array('id_alimento', 'quantidade'))
                    ->joinLeft('alimento', 'pagamento_alimentos.id_alimento = alimento.id_alimento', array('nome_alimento'))
                    ->joinLeft('turma', 'turma.id_turma = turma_alunos.id_turma', array('nome_turma', 'id_disciplina'))
                    ->joinLeft('disciplina', 'turma.id_disciplina = disciplina.id_disciplina', array('nome_disciplina', 'id_curso'))
                    ->joinLeft('curso', 'disciplina.id_curso = curso.id_curso', array('nome_curso'));

            if ($active)
                $select->where('aluno.status = ?', Application_Model_Aluno::$status_ativo);

            if ($atual)
                $select->joinInner('periodo', 'turma.id_periodo = periodo.id_periodo', array())
                        ->where('periodo.is_atual = ?', true);

            if (!empty($periodo))
                $select->where('turma.id_periodo = ?', (int) $periodo);

            if ($order)
                $select->order('nome_aluno ASC');

            if (!empty($turmas)) {
                $where = '(';

                foreach ($turmas as $turma)
                    $where .= $this->db_aluno->getAdapter()->quoteInto('turma.id_turma = ? OR ', base64_decode($turma));

                $where = substr($where, 0, -4) . ")";
                $select->where($where);
            }

            $alunos = $this->db_aluno->fetchAll($select);

            if (!empty($alunos)) {
                $array_alunos = array();
                $array_pagamentos = array();
                $array_faltas = array();
                $array_notas = array();

                foreach ($alunos as $inf_aluno) {
                    if (!isset($array_alunos[$inf_aluno->id_aluno]))
                        $array_alunos[$inf_aluno->id_aluno] = new Application_Model_Aluno($inf_aluno->id_aluno, $inf_aluno->nome_aluno, $inf_aluno->cpf, $inf_aluno->status, $inf_aluno->sexo, $inf_aluno->data_desligamento, $inf_aluno->motivo_desligamento, $inf_aluno->rg, $inf_aluno->data_nascimento, $inf_aluno->email, $inf_aluno->escolaridade, $inf_aluno->telefone, $inf_aluno->celular, $inf_aluno->endereco, $inf_aluno->bairro, $inf_aluno->numero, $inf_aluno->complemento, $inf_aluno->cep, $inf_aluno->cidade, $inf_aluno->estado, $inf_aluno->data_registro, $inf_aluno->is_cpf_responsavel, $inf_aluno->nome_responsavel, null, new Application_Model_Turma($inf_aluno->id_turma, $inf_aluno->nome_turma, null, null, null, null, new Application_Model_Disciplina($inf_aluno->id_disciplina, $inf_aluno->nome_disciplina, null, new Application_Model_Curso($inf_aluno->id_curso, $inf_aluno->nome_curso))), $inf_aluno->aprovado, $inf_aluno->liberacao, null);
                    else
                        $array_alunos[$inf_aluno->id_aluno]->addTurma(new Application_Model_Turma($inf_aluno->id_turma, $inf_aluno->nome_turma, null, null, null, null, new Application_Model_Disciplina($inf_aluno->id_disciplina, $inf_aluno->nome_disciplina, null, new Application_Model_Curso($inf_aluno->id_curso, $inf_aluno->nome_curso))), $inf_aluno->liberacao, $inf_aluno->aprovado);

                    if (!empty($inf_aluno->id_pagamento)) {
                        if (!isset($array_pagamentos[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_pagamento])) {
                            if (!empty($inf_aluno->id_alimento))
                                $array_pagamentos[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_pagamento] = new Application_Model_Pagamento($inf_aluno->id_pagamento, $inf_aluno->situacao, $inf_aluno->valor_pago, new Application_Model_Alimento($inf_aluno->id_alimento, $inf_aluno->nome_alimento), $inf_aluno->quantidade, $inf_aluno->condicao, $inf_aluno->tipo_isencao_pendencia, $inf_aluno->num_recibo);
                            else
                                $array_pagamentos[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_pagamento] = new Application_Model_Pagamento($inf_aluno->id_pagamento, $inf_aluno->situacao, $inf_aluno->valor_pago, null, null, $inf_aluno->condicao, $inf_aluno->tipo_isencao_pendencia, $inf_aluno->num_recibo);
                        }
                        elseif (!empty($inf_aluno->id_alimento))
                            $array_pagamentos[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_pagamento]->addAlimento(new Application_Model_Alimento($inf_aluno->id_alimento, $inf_aluno->nome_alimento), $inf_aluno->quantidade);
                    }

                    if (!empty($inf_aluno->id_falta)) {
                        if (!isset($array_faltas[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_falta]))
                            $array_faltas[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_falta] = new Application_Model_Falta($inf_aluno->id_falta, $inf_aluno->data_funcionamento, $inf_aluno->observacao);
                    }

                    if (!empty($inf_aluno->id_nota)) {
                        if (!isset($array_notas[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_nota]))
                            $array_notas[$inf_aluno->id_aluno][$inf_aluno->id_turma][$inf_aluno->id_nota] = new Application_Model_Nota($inf_aluno->id_nota, new Application_Model_Atividade($inf_aluno->id_atividade, null, $inf_aluno->nome, $inf_aluno->valor_total), $inf_aluno->valor_nota);
                    }
                }

                foreach ($array_alunos as $id_aluno => &$aluno) {
                    if (isset($array_pagamentos[$id_aluno])) {
                        foreach ($array_pagamentos[$id_aluno] as $id_turma => $pagamentos) {
                            foreach ($pagamentos as $pagamento)
                                $aluno->addPagamento(new Application_Model_Turma($id_turma), $pagamento);
                        }
                    }
                    if (isset($array_faltas[$id_aluno])) {
                        foreach ($array_faltas[$id_aluno] as $id_turma => $faltas) {
                            foreach ($faltas as $falta)
                                $aluno->addFalta(new Application_Model_Turma($id_turma), $falta);
                        }
                    }
                    if (isset($array_notas[$id_aluno])) {
                        foreach ($array_notas[$id_aluno] as $id_turma => $notas) {
                            foreach ($notas as $nota)
                                $aluno->addNota(new Application_Model_Turma($id_turma), $nota);
                        }
                    }
                }
                return $array_alunos;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna os id's que identificam os alunos da turma passada por parâmetro
     * @param int $turma
     * @return null|array
     */
    public function getTurmaAlunosID($turma) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();

            $select = $this->db_aluno->select()
                    ->setIntegrityCheck(false)
                    ->from('aluno', array('id_aluno'))
                    ->joinInner('turma_alunos', 'aluno.id_aluno = turma_alunos.id_aluno', array('id_turma_aluno'))
                    ->where('turma_alunos.id_turma = ?', (int) $turma);

            $turma_alunos = $this->db_aluno->fetchAll($select);

            if (!empty($turma_alunos)) {
                $array = array();

                foreach ($turma_alunos as $turma_aluno)
                    $array[$turma_aluno->id_aluno] = $turma_aluno->id_turma_aluno;
                return $array;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Verifica a existência de alunos com o nome próximo ao passado por parâmetro
     * @param string $nome
     * @return array
     */
    public function verificaAlunoNome($nome) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $select = $this->db_aluno
                    ->select()
                    ->from('aluno', array('id_aluno', 'nome_aluno', 'cpf'))
                    ->where('removeInvalidsCharacters(nome_aluno) LIKE ?', '%' . $nome . '%');

            $alunos = $this->db_aluno->fetchAll($select);

            if (!empty($alunos)) {
                $url_helper = new Zend_View_Helper_Url();
                $array_alunos = array();
                foreach ($alunos as $aluno) {
                    $array_alunos[$aluno->id_aluno]['label'] = mb_strtoupper($aluno->nome_aluno, 'UTF-8') . ' | ' . $aluno->cpf;
                    $array_alunos[$aluno->id_aluno]['value'] = '';
                    $array_alunos[$aluno->id_aluno]['url'] = $url_helper->url(array('controller' => 'aluno', 'action' => 'alterar', 'aluno' => base64_encode($aluno->id_aluno))); //'<a href="'.$url_helper->url(array('controller' => 'aluno', 'action' => 'alterar', 'aluno'=>  base64_encode($aluno->id_aluno))).'>Alterar</a>';     
                }

                return $array_alunos;
            }
            return null;
        } catch (Zend_Exception $ex) {
            echo $ex->getMessage();
            return null;
        }
    }

    /**
     * Método auxiliar utilizado para realizar a troca da turma do aluno passado por parâmetro.
     * Utilizado quando há a necessidade da troca de turma dos alunos
     * 
     * @param array $turmas array([turma_antiga] => turma_nova)
     * @param int $id_aluno
     * @return boolean
     */
    public function updateTurmaAlunos($turmas, $id_aluno) {
        try {
            $db_turmas_alunos = new Application_Model_DbTable_TurmaAlunos();

            foreach ($turmas as $turma_antiga => $turma_nova)
                $db_turmas_alunos->update(array('id_turma' => $turma_nova), $db_turmas_alunos->getAdapter()->quoteInto('id_turma = ? AND ', (int) $turma_antiga) .
                        $db_turmas_alunos->getAdapter()->quoteInto('id_aluno = ?', (int) $id_aluno));
            return true;
        } catch (Zend_Exception $e) {
            echo $e;
            return false;
        }
    }

    /**
     * Altera o status do aluno para ativo, possiibilitando que ele possa estar em uma turma
     * @param int $id_aluno
     * @return boolean
     */
    public function ativarAluno($id_aluno) {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $this->db_aluno->update(Application_Model_Aluno::parseArrayAtivacao(), $this->db_aluno->getAdapter()->quoteInto('id_aluno = ?', (int) $id_aluno));
            return true;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Desliga um aluno do projeto, alterando o seu status. Dessa forma ele não pode ser cadastrado em nenhuma turma, nem algo do tipo
     * @param Application_Model_Aluno $aluno
     * @return boolean
     */
    public function desligarAluno($aluno) {
        try {
            if ($aluno instanceof Application_Model_Aluno) {
                $this->db_aluno = new Application_Model_DbTable_Aluno();
                $this->db_aluno->update($aluno->parseArrayDesligamento(), $this->db_aluno->getAdapter()->quoteInto('id_aluno = ?', $aluno->getIdAluno()));
                return true;
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e;
            return false;
        }
    }

    /**
     * Método auxiliar que retorna os alunos organizados pelo horário e disciplina.
     * Utilizado para redistribuir alunos de uma mesma disciplina/horário, por algum critério estabelecido.
     * 
     * array([]id-disciplina_nome-disciplina_horario-inicio_horario-termino] [] => (
     *          [aluno] => id_aluno
     *          [nome_aluno] => nome
     *          [data_nascimento] => data
     *          [turma] => turma do aluno
     *          
     * @return null
     */
    public function getInfAlunosDisciplinaHorario() {
        try {
            $this->db_aluno = new Application_Model_DbTable_Aluno();
            $select = $this->db_aluno->select()
                    ->setIntegrityCheck(false)
                    ->from('aluno', array('id_aluno', 'nome_aluno', 'data_nascimento'))
                    ->joinLeft('turma_alunos', 'turma_alunos.id_aluno = aluno.id_aluno', array(''))
                    ->joinLeft('turma', 'turma.id_turma = turma_alunos.id_turma', array('id_turma', 'horario_inicio', 'horario_fim', 'id_disciplina'))
                    ->joinInner('periodo', 'periodo.id_periodo = turma.id_periodo', array())
                    ->joinLeft('disciplina', 'turma.id_disciplina = disciplina.id_disciplina', array('nome_disciplina'))
                    ->where('periodo.is_atual = ?', true)
                    ->order('aluno.data_nascimento DESC');

            $dados = $this->db_aluno->fetchAll($select);

            if (!empty($dados)) {
                $array = array();
                $i = 0;
                foreach ($dados as $data) {
                    if (!isset($array[$data->id_disciplina . '_' . $data->nome_disciplina . '_' . $data->horario_inicio . '_' . $data->horario_fim][$i])) {
                        $array[$data->id_disciplina . '_' . $data->nome_disciplina . '_' . $data->horario_inicio . '_' . $data->horario_fim][$i]['aluno'] = $data->id_aluno;
                        $array[$data->id_disciplina . '_' . $data->nome_disciplina . '_' . $data->horario_inicio . '_' . $data->horario_fim][$i]['data_nascimento'] = new DateTime($data->data_nascimento);
                        $array[$data->id_disciplina . '_' . $data->nome_disciplina . '_' . $data->horario_inicio . '_' . $data->horario_fim][$i]['nome_aluno'] = $data->nome_aluno;
                        $array[$data->id_disciplina . '_' . $data->nome_disciplina . '_' . $data->horario_inicio . '_' . $data->horario_fim][$i]['turma'] = $data->id_turma;
                    }
                    $i++;
                }
                return $array;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Aprova/desaprova alunos baseado em suas notas/frequências.
     * Só é realizado se todas as notas/frequências estiverem lançadas. 
     * Chamado quando um período é finalizado.
     */
    public function finalizaAlunos($quantidade_alunos_turma, $calendario_atual, $turmas_datas_lancamentos, $ids_atividades_turma, $notas_lancadas) {
        try {
            if ($this->verificaLancamentos($quantidade_alunos_turma, $calendario_atual, $turmas_datas_lancamentos, $ids_atividades_turma, $notas_lancadas)) {
                if ($calendario_atual instanceof Application_Model_DatasAtividade) {
                    $alunos = $this->getAlunos(null, true, true, true);
                    $periodo_atual = $calendario_atual->getPeriodoCalendario();

                    if ($periodo_atual instanceof Application_Model_Periodo && $this->verificaLancamentos($quantidade_alunos_turma, $calendario_atual, $turmas_datas_lancamentos, $ids_atividades_turma, $notas_lancadas)) {
                        $db_turmas_alunos = new Application_Model_DbTable_TurmaAlunos();

                        foreach ($alunos as $aluno) {
                            $turmas_aluno = $aluno->getCompleteTurmas();

                            foreach ($turmas_aluno as $id_turma => $turma) {
                                if ($aluno->getNotaAcumulada($id_turma, false, true) >= $periodo_atual->getMinPtsAprovacao() && $aluno->getPorcentagemFaltas($id_turma, $calendario_atual->getQuantidadeAulas()) * 100 >= $periodo_atual->getFrequenciaLiberacao())
                                    $db_turmas_alunos->update(array('aprovado' => true), $db_turmas_alunos->getAdapter()->quoteInto('id_turma = ? AND ', $id_turma) .
                                            $db_turmas_alunos->getAdapter()->quoteInto('id_aluno = ?', $aluno->getIdAluno())
                                    );
                                else
                                    $db_turmas_alunos->update(array('aprovado' => false), $db_turmas_alunos->getAdapter()->quoteInto('id_turma = ? AND ', $id_turma) .
                                            $db_turmas_alunos->getAdapter()->quoteInto('id_aluno = ?', $aluno->getIdAluno())
                                    );
                            }
                        }
                        return true;
                    }
                }
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Verifica se os lançamentos estão presentes
     * @param type $quantidade_alunos_turma
     * @param Application_Model_DatasAtividade $calendario_atual
     * @param type $turmas_datas_lancamentos
     * @param type $ids_atividades_turma
     * @param type $notas_lancadas
     * @return boolean
     */
    private function verificaLancamentos(&$quantidade_alunos_turma, &$calendario_atual, &$turmas_datas_lancamentos, &$ids_atividades_turma, &$notas_lancadas) {
        try {
            if ($calendario_atual instanceof Application_Model_DatasAtividade) {
                //verifica lançamento de frequencia            
                if (!empty($turmas_datas_lancamentos)) {
                    $count_datas_calendario = count($calendario_atual->getDatas());

                    foreach ($turmas_datas_lancamentos as $datas) {
                        if ($count_datas_calendario != count($datas))
                            return false;
                    }
                } else
                    return false;

                // verifica o lançamento de notas
                if (!empty($ids_atividades_turma) && !empty($notas_lancadas)) {
                    foreach ($ids_atividades_turma as $id_turma => $id_turma_atividade) {
                        if (count($notas_lancadas[$id_turma_atividade['id_atividade_turma']]) != $quantidade_alunos_turma[$id_turma])
                            return false;
                    }
                    return true;
                }
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Verifica se o aluno já foi aprovado em turmas de pré requisitos da disciplina em que ele está sendo inserido
     * @param type $id_aluno
     * @param type $turmas_pre_requisito
     * @return boolean|null
     * @throws Exception
     */
    public function verificaPreRequisitosAluno($id_aluno, $turmas_pre_requisito) {
        try {
            if (!empty($id_aluno) && !empty($turmas_pre_requisito) && is_array($turmas_pre_requisito)) {
                $db_turma_aluno = new Application_Model_DbTable_TurmaAlunos();
                $where = '( ';
                $select = $db_turma_aluno->select()
                        ->setIntegrityCheck(false)
                        ->from('turma_alunos')
                        ->joinInner('turma', 'turma_alunos.id_turma = turma.id_turma', array())
                        ->joinInner('periodo', 'turma.id_periodo = periodo.id_periodo', array('nome_periodo'))
                        ->where('turma_alunos.id_aluno = ?', (int) $id_aluno)
                        ->order('periodo.id_periodo ASC');

                foreach ($turmas_pre_requisito as $turma) {
                    if ($turma instanceof Application_Model_Turma)
                        $where .= $db_turma_aluno->getAdapter()->quoteInto('turma_alunos.id_turma = ?', $turma->getIdTurma()) . " OR ";
                    else
                        break;
                }

                $where = substr($where, 0, -4) . ")";
                $turmas_aluno = $db_turma_aluno->fetchAll($select->where($where));

                if ($turmas_aluno->count() > 0) {
                    $aprovado = null;

                    foreach ($turmas_aluno as $turma) {
                        if (!is_null($turma->aprovado)) {
                            if ((int) $turma->aprovado)
                                return true;

                            else {
                                $aprovado['tipo'] = 'reprovado';
                                $aprovado['periodo'] = $turma->nome_periodo;
                                $aprovado['nome_turma'] = $turmas_pre_requisito[$turma->id_turma]->toString();
                            }
                        }
                    }
                    return $aprovado;
                }
                return null;
            }
            throw new Exception('Houve problemas ao verificar os pré requisitos do aluno');
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
