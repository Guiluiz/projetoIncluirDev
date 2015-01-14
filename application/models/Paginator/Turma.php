<?php

class Application_Model_Paginator_Turma extends Zend_Paginator_Adapter_DbSelect {

    public function getItems($offset, $itemCountPerPage) {
        $turmas = parent::getItems($offset, $itemCountPerPage);
        $array_turmas = array();

        foreach ($turmas as $turma) {
            $array_turmas[] = new Application_Model_Turma(
                    $turma['id_turma'], $turma['nome_turma'], null, null, null, null, new Application_Model_Disciplina($turma['id_disciplina'], $turma['nome_disciplina']), $turma['status'], null, $turma['id_periodo']
            );
        }

        return $array_turmas;
    }

}
