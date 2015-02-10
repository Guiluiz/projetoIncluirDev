<?php

class Application_Form_FormConfigFimPeriodo extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-config-fim-periodo.phtml')))
        );

        $opcoes_adiamento = new Zend_Form_Element_Select('opcoes_adiamento');
        $opcoes_adiamento->setLabel('Opções de Adiamento:')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StripTags')
                ->setRegisterInArrayValidator(false)
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->setMultiOptions(array(
                    '' => 'Selecione',
                    base64_encode(Application_Model_Mappers_Periodo::$adiar_um_dia) => 'Adiar uma dia',
                    base64_encode(Application_Model_Mappers_Periodo::$adiar_uma_semana) => 'Adiar uma semana'
                ))
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

        $this->addElements(array(
            $opcoes_adiamento,
            $enviar
        ));
    }

}
