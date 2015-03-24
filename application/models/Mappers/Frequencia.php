<?php

class Application_Model_Mappers_Frequencia {

    private $db_frequencia;

    public function lancamentoFrequenciaAlunos($faltas, $turma, $id_turma_alunos, $data) {
        try {
            if ($data instanceof DateTime && $turma instanceof Application_Model_Turma) {
                if (!empty($id_turma_alunos)) {
                    $this->db_frequencia = new Application_Model_DbTable_Falta();
                    $where = $this->db_frequencia->getAdapter()->quoteInto('(falta.data_funcionamento = ?) AND (', $data->format('Y-m-d'));

                    foreach ($id_turma_alunos as $turma_aluno)
                        $where .= $this->db_frequencia->getAdapter()->quoteInto('falta.id_turma_aluno = ? OR ', $turma_aluno);

                    $where = substr($where, 0, -4) . ")";

                    // exclui o lançamento antigo
                    $this->db_frequencia->delete($where);

                    if (!empty($faltas)) {
                        foreach ($faltas as $id_aluno => $falta) {
                            if ($falta instanceof Application_Model_Falta && isset($id_turma_alunos[$id_aluno])) {
                                $aux = $falta->parseArray();
                                $aux['id_turma_aluno'] = $id_turma_alunos[$id_aluno];

                                $this->db_frequencia->insert($aux);
                            }
                        }
                    }

                    // inclui na tabela que o lançamento da data x para turma Y já foi feito (para previnir multiplos lançamentos, o antigo é excluído)
                    $db_datas_lancamentos = new Application_Model_DbTable_DatasLancamentosFrequenciaTurmas();
                    $db_datas_lancamentos->delete(
                            $db_datas_lancamentos->getAdapter()->quoteInto('data_funcionamento = ? AND ', $data->format('Y-m-d')) .
                            $db_datas_lancamentos->getAdapter()->quoteInto('id_turma = ?', $turma->getIdTurma())
                    );

                    $db_datas_lancamentos->insert(array('data_funcionamento' => $data->format('Y-m-d'), 'id_turma' => $turma->getIdTurma()));

                    return true;
                }
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function lancamentoFrequenciaVoluntarios($frequencias, $data) {
        try {
            if ($data instanceof DateTime) {
                $this->db_frequencia = new Application_Model_DbTable_EscalaFrequenciaVoluntario();

                $where = $this->db_frequencia->getAdapter()->quoteInto('data_funcionamento = ?', $data->format('Y-m-d'));

                // Futuramente será alterado
                $this->db_frequencia->delete($where);

                if (!empty($frequencias)) {
                    foreach ($frequencias as $id_voluntario => $frequencia) {
                        if ($frequencia instanceof Application_Model_EscalaFrequencia) {
                            $aux = $frequencia->parseArray();
                            $aux['id_voluntario'] = $id_voluntario;

                            $this->db_frequencia->insert($aux);
                        }
                    }
                }
                return true;
            }
            return false;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Méetodo para incluir datas de lançamentos
     * @param Application_Model_Turma[] $turmas
     * @param Application_Model_DatasAtividade $calendario
     * @return null
     */
    public function setDatasLancamentos($turmas, $calendario) {
        try {
            if ($calendario instanceof Application_Model_DatasAtividade && !empty($turmas)) {
                $datas = $calendario->getDatas();
                $db_datas_lancamentos = new Application_Model_DbTable_DatasLancamentosFrequenciaTurmas();

                foreach ($turmas as $turma) {
                    if ($turma instanceof Application_Model_Turma) {
                        $id_turma = $turma->getIdTurma();

                        foreach ($datas as $data)
                            $db_datas_lancamentos->insert(array('data_funcionamento' => $data->format('Y-m-d'), 'id_turma' => $id_turma));
                    }
                }
            }
        } catch (Zend_Exception $ex) {
            echo $ex->getMessage();
            return null;
        }
    }

    /**
     * Método para retornar as datas de lançamentos de acordo com os filtros passados
     * @param Application_Model_Periodo $periodo
     * @param Application_Model_Turma $turma
     */
    public function getDatasLancamentosByPeriodo($periodo = null, $turma = null) {
        try {
            $db_datas_lancamentos = new Application_Model_DbTable_DatasLancamentosFrequenciaTurmas();

            $select = $db_datas_lancamentos->select()
                    ->setIntegrityCheck(false)
                    ->from('datas_lancamentos_frequencias_turmas');

            if ($periodo instanceof Application_Model_Periodo)
                $select->joinInner('datas_funcionamento', 'datas_lancamentos_frequencias_turmas.data_funcionamento = datas_funcionamento.data_funcionamento')
                        ->where('datas_funcionamento.id_periodo = ?', $periodo->getIdPeriodo());

            if (!empty($turma))
                $select->where('datas_lancamentos_frequencias_turmas.id_turma = ?', (int) $turma);

            $datas_lancamentos = $db_datas_lancamentos->fetchAll($select);

            if (!empty($datas_lancamentos)) {
                $array_datas = array();

                foreach ($datas_lancamentos as $data)
                    $array_datas[$data->id_turma][$data->data_funcionamento] = $data->data_funcionamento;

                return $array_datas;
            }
            return null;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Método para retornar a quantidade de lançamentos das turmas passadas
     * @param Application_Model_Turma $turma
     */
    public function getQuantidadeLancamentosByPeriodo($turmas = null) {
        try {
            $db_datas_lancamentos = new Application_Model_DbTable_DatasLancamentosFrequenciaTurmas();
            $select = $db_datas_lancamentos->select();

            if (!empty($turmas)) {
                $where = "( ";

                foreach ($turmas as $id)
                    $where .= $db_datas_lancamentos->getAdapter()->quoteInto('id_turma = ?', (int) $id) . " OR ";

                $where = substr($where, 0, -4) . ")";
                $select->where($where);
            }

            $turmas = $db_datas_lancamentos->fetchAll($select);
            $array_turma = array();
            
            if (!empty($turmas)) {

                foreach ($turmas as $turma) {
                    if (!isset($array_turma[$turma->id_turma]))
                        $array_turma[$turma->id_turma] = 1;
                    else
                        $array_turma[$turma->id_turma] ++;
                }
            }
            return $array_turma;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
