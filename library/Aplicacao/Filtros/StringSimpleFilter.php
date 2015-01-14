<?php

/**
 * Description of StringFilter
 *
 * @author SOLIDARIEDADE
 */
class Aplicacao_Filtros_StringSimpleFilter implements Zend_Filter_Interface {

    public function filter($value) {
        $value = $this->remove_invalids_carac($value);

        return $value;
    }

    private function remove_invalids_carac($texto) {
        $texto = trim($texto);

        $array1 = array("\\","\'", "\"", "´", "`", "~", "^", "¨", "#", "&", ";", ".", "  ", "/", "[", "]", "{", "}", "+", "=", "(", ")", "@", "!", "$", "%", "*", "|", "-", ",");

        $array2 = array("","", "", "", "", "", "", "", "", "", "", "", " ", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "");

        $aux = str_replace($array1, $array2, $texto);

        return $aux;
    }

}
