<?php

class Application_Form_FormConfirmacao extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-confirmacao.phtml')))
        );
        
        $id = new Zend_Form_Element_Hidden('id');
        $id ->setDecorators(array(
            'ViewHelper',
            'Errors',
            'Label'
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
            $id,
            $enviar,
            $cancelar
        ));
    }

}
