<?php

class Application_Model_Paginator_Voluntario extends Zend_Paginator_Adapter_DbSelect {

    public function getItems($offset, $itemCountPerPage) {
        $voluntarios = parent::getItems($offset, $itemCountPerPage);
        $array_voluntarios = array();

        foreach ($voluntarios as $voluntario) {
            $array_voluntarios[] = new Application_Model_Voluntario(
                    $voluntario['id_voluntario'], $voluntario['nome'], $voluntario['cpf'], null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, $voluntario['status']
            );
        }

        return $array_voluntarios;
    }

}
