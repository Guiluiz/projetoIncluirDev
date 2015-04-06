<?php

/**
 * Description of Atividade
 *
 * @author user
 */
class Application_Model_Mappers_Atividade {

    private $db_atividade;

    /**
     * Adiciona uma atividade no banco de dados
     * @param Application_Model_Atividade $atividade
     * @return boolean
     */
    public function addAtividade($atividade) {
        try {
            if ($atividade instanceof Application_Model_Atividade && $this->validaAtividade($atividade)) {
                $this->db_atividade = new Application_Model_DbTable_Atividade();
                $db_turma_atividade = new Application_Model_DbTable_TurmaAtividades();

                $id_atividade = $this->db_atividade->insert($atividade->parseArray());
                $db_turma_atividade->insert(array('id_turma' => $atividade->getTurma()->getIdTurma(), 'id_atividade' => $id_atividade));

                return true;
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Altera uma atividade no banco de dados. Se a turma for alterada, as notas lançadas são removidas
     * @param Application_Model_Atividade $atividade
     * @return boolean
     */
    public function alterarAtividade($atividade) {
        try {
            if ($atividade instanceof Application_Model_Atividade && $this->validaAtividade($atividade, true)) {
                $this->db_atividade = new Application_Model_DbTable_Atividade();
                $db_turma_atividade = new Application_Model_DbTable_TurmaAtividades();

                $turma_atual = $this->getTurmaAtividade($atividade->getIdAtividade());

                $this->db_atividade->update($atividade->parseArray(), $this->db_atividade->getAdapter()->quoteInto('id_atividade = ?', $atividade->getIdAtividade()));
                $valor_atual = $this->getValorAtividade($atividade);

                if ($turma_atual != $atividade->getTurma()->getIdTurma() || $valor_atual != $atividade->getValor()) {
                    $db_turma_atividade->delete(
                            $this->db_atividade->getAdapter()->quoteInto('id_atividade = ?', $atividade->getIdAtividade())
                    );

                    $db_turma_atividade->insert(array('id_turma' => $atividade->getTurma()->getIdTurma(), 'id_atividade' => $atividade->getIdAtividade()));
                }
                return true;
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Exclui a atividade do banco de dados
     * @param Application_Model_Atividade $atividade
     * @return boolean
     */
    public function excluirAtividade($atividade) {
        try {
            if ($atividade instanceof Application_Model_Atividade) {
                $this->db_atividade = new Application_Model_DbTable_Atividade();
                $this->db_atividade->delete($this->db_atividade->getAdapter()->quoteInto('id_atividade = ?', $atividade->getIdAtividade()));
                return true;
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    private function getTurmaAtividade($id_atividade) {
        try {
            $db_turma_atividade = new Application_Model_DbTable_TurmaAtividades();
            $select = $db_turma_atividade->select()
                    ->from('atividade', array())
                    ->joinInner('turma_atividades', 'atividade.id_atividade = turma_atividades.id_atividade', array('id_turma'))
                    ->where('atividade.id_atividade = ?', (int) $id_atividade);

            $turma_atividade = $db_turma_atividade->fetchRow($select);

            if (!empty($turma_atividade))
                return $turma_atividade->id_turma;

            throw new Zend_Exception('Problemas ao buscar a turma da atividade');
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e);
        }
    }

    private function getValorAtividade($atividade) {
        try {
            if ($atividade instanceof Application_Model_Atividade) {
                if (!$this->db_atividade instanceof Application_Model_DbTable_Atividade)
                    $this->db_atividade = new Application_Model_DbTable_Atividade();

                $select = $this->db_atividade->select();
                $select->from('atividade', array('valor_total'))
                        ->where('id_atividade = ?', $atividade->getIdAtividade());

                $aux_atividade = $this->db_atividade->fetchRow($select);

                if (!empty($aux_atividade))
                    return $aux_atividade->valor_total;

                throw new Zend_Exception('Problemas ao verificar o valor da atividade');
            }
        } catch (Zend_Exception $e) {
            throw $e;
        }
    }

    /**
     * Verifica se a atividade passada não estoura o limite de atividades por turma
     * @param Application_Model_Atividade $atividade
     * @return boolean
     */
    public function validaAtividade($atividade, $exclude = false) {
        try {
            $mapper_periodo = new Application_Model_Mappers_Periodo();
            $periodo_atual = $mapper_periodo->getPeriodoAtual();

            if ($atividade instanceof Application_Model_Atividade && $periodo_atual instanceof Application_Model_Periodo) {
                $this->db_atividade = new Application_Model_DbTable_Atividade();

                $select = $this->db_atividade->select();
                $select->from('atividade')
                        ->setIntegrityCheck(false)
                        ->joinInner('turma_atividades', 'atividade.id_atividade = turma_atividades.id_atividade')
                        ->where('turma_atividades.id_turma = ?', $atividade->getTurma()->getIdTurma());

                if ($exclude)
                    $select->where('atividade.id_atividade <> ?', $atividade->getIdAtividade());

                $atividades = $this->db_atividade->fetchAll($select);

                if (!empty($atividades)) {
                    $valor = 0;

                    foreach ($atividades as $atividade_turma)
                        $valor += $atividade_turma->valor_total;

                    if (($valor + $atividade->getValor()) > $periodo_atual->getTotalPontosPeriodo())
                        return false;

                    return true;
                }
                return true;
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function buscaAtividadesTurma($id_turma, $is_paginator = null, $data_limite = null) {
        try {
            $this->db_atividade = new Application_Model_DbTable_Atividade();

            $select = $this->db_atividade->select();
            $select->from('atividade')
                    ->setIntegrityCheck(false)
                    ->joinInner('turma_atividades', 'atividade.id_atividade = turma_atividades.id_atividade')
                    ->joinInner('turma', 'turma_atividades.id_turma = turma.id_turma', array('nome_turma', 'id_disciplina'))
                    ->joinInner('periodo', 'turma.id_periodo = periodo.id_periodo', array('id_periodo', 'is_atual'))
                    ->joinInner('disciplina', 'turma.id_disciplina = disciplina.id_disciplina', array('nome_disciplina'));

            if (!empty($id_turma))
                $select->where('turma_atividades.id_turma = ?', (int) $id_turma);

            if (!empty($data_limite))
                $select->where('atividade.data_funcionamento <= ?', $data_limite);

            $atividades = $this->db_atividade->fetchAll($select);

            if (empty($is_paginator)) {
                if (!empty($atividades)) {
                    $array_atividades = array();

                    foreach ($atividades as $atividade)
                        $array_atividades[] = new Application_Model_Atividade($atividade->id_atividade, new Application_Model_Turma($atividade->id_turma, $atividade->nome_turma, null, null, null, null, new Application_Model_Disciplina($atividade->id_disciplina, $atividade->nome_disciplina), null, null, new Application_Model_Periodo($atividade->id_periodo, $atividade->is_atual)), $atividade->nome, $atividade->valor_total, $atividade->descricao, $atividade->data_funcionamento);

                    return $array_atividades;
                }
                return null;
            }
            return new Zend_Paginator(new Application_Model_Paginator_Atividade($select->order('nome')));
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function buscaAtividadeByID($id_atividade) {
        try {
            $this->db_atividade = new Application_Model_DbTable_Atividade();
            $select = $this->db_atividade->select()
                    ->setIntegrityCheck(false)
                    ->from('atividade')
                    ->joinInner('turma_atividades', 'atividade.id_atividade = turma_atividades.id_atividade')
                    ->joinInner('turma', 'turma.id_turma = turma_atividades.id_turma', array('id_turma', 'id_disciplina'))
                    ->joinInner('periodo', 'turma.id_periodo = periodo.id_periodo', array('id_periodo', 'is_atual'))
                    ->joinInner('disciplina', 'turma.id_disciplina = disciplina.id_disciplina', array('id_curso'))
                    ->where('atividade.id_atividade = ?', (int) $id_atividade);

            $atividade = $this->db_atividade->fetchRow($select);

            if (!empty($atividade))
                return new Application_Model_Atividade($atividade->id_atividade, new Application_Model_Turma($atividade->id_turma, null, null, null, null, null, new Application_Model_Disciplina($atividade->id_disciplina, null, null, new Application_Model_Curso($atividade->id_curso)), null, null, new Application_Model_Periodo($atividade->id_periodo, $atividade->is_atual)), $atividade->nome, $atividade->valor_total, $atividade->descricao, $atividade->data_funcionamento);

            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retorna os id's que identificam as atividades da turma passada por parâmetro
     * @param int $turma
     * @return null|array
     */
    public function getTurmaAtividadesID($turma = null) {
        try {
            $this->db_atividade = new Application_Model_DbTable_TurmaAtividades();
            $select = $this->db_atividade->select()
                    ->from('turma_atividades', array('id_atividades_turma', 'id_atividade', 'id_turma'));

            if (!empty($turma))
                $select->where('id_turma = ?', (int) $turma);

            $turma_atividades = $this->db_atividade->fetchAll($select);

            if (!empty($turma_atividades)) {
                $array = array();

                if (!empty($turma)) {
                    foreach ($turma_atividades as $turma_atividade)
                        $array[$turma_atividade->id_atividade] = $turma_atividade->id_atividades_turma;
                    return $array;
                }

                // utilizado para finalizar o período
                foreach ($turma_atividades as $turma_atividade) {
                    $array[$turma_atividade->id_turma]['id_atividade_turma'] = $turma_atividade->id_atividades_turma;
                    $array[$turma_atividade->id_turma]['id_atividade'] = $turma_atividade->id_atividade;
                }

                return $array;
            }
            return null;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return null;
        }
    }

}
