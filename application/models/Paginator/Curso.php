<?php

/**
 * Paginação -> converte array do BD em objetos do curso e faz controle de paginação
 *
 * @author Pablo Augusto
 */
class Application_Model_Paginator_Curso extends Zend_Paginator_Adapter_DbSelect {

    public function getItems($offset, $itemCountPerPage) {
        $cursos = parent::getItems($offset, $itemCountPerPage);
        $array_cursos = array();

        foreach ($cursos as $curso)
            $array_cursos[] = new Application_Model_Curso($curso['id_curso'], $curso['nome_curso'], null, $curso['status']);

        return $array_cursos;
    }

}
