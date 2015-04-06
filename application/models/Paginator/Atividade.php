<?php

/**
 * Paginação -> converte array do BD em objetos do curso e faz controle de paginação
 *
 * @author Pablo Augusto
 */
class Application_Model_Paginator_Atividade extends Zend_Paginator_Adapter_DbSelect {

    public function getItems($offset, $itemCountPerPage) {
        $atividades = parent::getItems($offset, $itemCountPerPage);
        $array_atividades = array();

        foreach ($atividades as $atividade) {
            $array_atividades[] = new Application_Model_Atividade(
                            $atividade['id_atividade'],
                            new Application_Model_Turma($atividade['id_turma'], $atividade['nome_turma'], null, null, null, null, new Application_Model_Disciplina($atividade['id_disciplina'], $atividade['nome_disciplina']), null, null, new Application_Model_Periodo($atividade['id_periodo'], $atividade['is_atual'])),
                            $atividade['nome'],
                            $atividade['valor_total'],
                            null,
                            $atividade['data_funcionamento']
            );
        }

        return $array_atividades;
    }

}
