<?php

/**
 * Description of StringFilter
 *
 * @author SOLIDARIEDADE
 */
class Aplicacao_Filtros_StringFilter implements Zend_Filter_Interface {

    public function filter($value) {
        $value = $this->remove_invalids_carac($value);

        return $value;
    }

    private function remove_invalids_carac($texto) {
        $texto = trim($texto);

        $array1 = array("\\", "\'", "\"", "~", "^", "¨", "#", "&", ";", ".", "  ", "/", "[", "]", "{", "}", "+", "=", "(", ")", "@", "!", "$", "%", "*", "|", "-", ",", "á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç"
            , "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç");

        $array2 = array("", "", "", "", "", "", "", "", "", "", " ", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c"
            , "A", "A", "A", "A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C");

        $aux = str_replace($array1, $array2, $texto);

        return $aux;
    }

}
