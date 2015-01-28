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

}
