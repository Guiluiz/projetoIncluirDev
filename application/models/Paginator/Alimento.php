<?php

/**
 * Paginação -> converte array do BD em objetos do curso e faz controle de paginação
 *
 * @author Pablo Augusto
 */
class Application_Model_Paginator_Alimento extends Zend_Paginator_Adapter_DbSelect {

    public function getItems($offset, $itemCountPerPage) {
        $alimentos = parent::getItems($offset, $itemCountPerPage);
        $array_alimentos = array();

        foreach ($alimentos as $alimento) {
            $array_alimentos[] = new Application_Model_Alimento(
                            $alimento['id_alimento'],
                            $alimento['nome_alimento']
            );
        }

        return $array_alimentos;
    }

}
