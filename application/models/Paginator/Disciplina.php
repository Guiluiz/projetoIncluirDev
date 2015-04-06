<?php

class Application_Model_Paginator_Disciplina extends Zend_Paginator_Adapter_DbSelect {

    public function getItems($offset, $itemCountPerPage) {
        $disciplinas = parent::getItems($offset, $itemCountPerPage);
        $array_disciplinas = array();

        foreach ($disciplinas as $disciplina) {
            $array_disciplinas[] = new Application_Model_Disciplina(
                    $disciplina['id_disciplina'], $disciplina['nome_disciplina'], null, new Application_Model_Curso(null, $disciplina['nome_curso']), null, $disciplina['status']
            );
        }

        return $array_disciplinas;
    }

}
