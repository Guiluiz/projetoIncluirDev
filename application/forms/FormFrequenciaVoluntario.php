<?php

class Application_Form_FormFrequenciaVoluntario extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-frequencia-voluntario.phtml')))
        );

        $data = new Zend_Form_Element_Hidden('data');
        $data->setDecorators(array(
            'ViewHelper'
        ));

        $setor = new Zend_Form_Element_Select('setor');
        $setor->setLabel('Setor:')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StripTags')
                ->setRegisterInArrayValidator(false)
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $enviar = new Zend_Form_Element_Submit('enviar');
        $enviar->setLabel('Salvar')
                ->setDecorators(array(
                    'ViewHelper'
        ));

        $cancelar = new Zend_Form_Element_Submit('cancelar');
        $cancelar->setLabel('Cancelar')
                ->setAttrib('class', 'cancel')
                ->setDecorators(array(
                    'ViewHelper'
        ));

        $this->addElements(array(
            $data,
            $setor,
            $enviar,
            $cancelar
        ));
    }

    public function initializeSetores($setores = null) {
        $array_setores = array('' => 'Selecione', base64_encode('all') => "Todos os Voluntários");

        if (!empty($setores)) {
            //foreach ($setores as $setor)
            //  $array_setores[$setor->getIdCurso(true)] = $curso->getNomeCurso();
        }

        $this->getElement('setor')
                ->setMultiOptions($array_setores);
    }

    public function getFrequencia($dados, $quantidade) {
        $array_escalas_frequencias = array();

        if ($quantidade > 0) {
            if (!empty($dados)) {
                $count = 0;
                $data = (isset($dados['data'])) ? $dados['data'] : null;
                $array_voluntarios = array();

                if (!empty($data)) {
                    foreach ($dados as $key => $inf) {
                        $pos_is_presente = strpos($key, 'voluntario_presente_');
                        $pos_hora_inicio = strpos($key, 'voluntario_entrada_');
                        $pos_hora_fim = strpos($key, 'voluntario_saida_');

                        if ($pos_is_presente !== false) {
                            $id_voluntario = (int) base64_decode(substr($key, $pos_is_presente + 20));

                            if (!isset($array_voluntarios[$id_voluntario]))
                                $count++;

                            $array_voluntarios[$id_voluntario]['is_presente'] = ($inf == 'on') ? true : false;
                        }

                        elseif ($pos_hora_inicio !== false) {
                            $id_voluntario = (int) base64_decode(substr($key, $pos_hora_inicio + 18));

                            if (!isset($array_voluntarios[$id_voluntario]))
                                $count++;

                            $array_voluntarios[$id_voluntario]['hora_entrada'] = $inf;
                        }

                        elseif ($pos_hora_fim !== false) {
                            $id_voluntario = (int) base64_decode(substr($key, $pos_hora_fim + 17));

                            if (!isset($array_voluntarios[$id_voluntario]))
                                $count++;

                            $array_voluntarios[$id_voluntario]['hora_saida'] = $inf;
                        }
                    }
                    
                    if ($count != $quantidade)
                        return null;
                }
                
                foreach ($array_voluntarios as $id_voluntario => $frequencia)
                    $array_escalas_frequencias[$id_voluntario] = new Application_Model_EscalaFrequencia(null, $frequencia['is_presente'], $data, ($frequencia['is_presente']) ? $frequencia['hora_entrada']: null, ($frequencia['is_presente']) ? $frequencia['hora_saida'] : null);
                
                return $array_escalas_frequencias;
            }
            return false;
        }
        return $array_escalas_frequencias;
    }

}
