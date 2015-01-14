<?php

class Application_Model_Paginator_Aluno extends Zend_Paginator_Adapter_DbSelect {

    public function getItems($offset, $itemCountPerPage) {
        $alunos = parent::getItems($offset, $itemCountPerPage);
        $array_alunos = array();

        foreach ($alunos as $aluno) {
            $array_alunos[] = new Application_Model_Aluno(
                            $aluno['id_aluno'],
                            $aluno['nome_aluno'],
                            $aluno['cpf'],
                            $aluno['status']
            );
        }

        return $array_alunos;
    }

}
