<?php

class Application_Form_FormAlimento extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-alimento.phtml')))
        );
        
        $nome = new Zend_Form_Element_Text('nome_alimento');
        $nome->setLabel('Nome:')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $id_alimento = new Zend_Form_Element_Hidden('id_alimento');
        $id_alimento->setDecorators(array(
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
            $nome,
            $id_alimento,
            $enviar,
            $cancelar
        ));
    }
    
    /**
     * Retira as validações, desabilita e/ou ativa o modo somente leitura
     * dos campos do formulário
     */
    public function limpaValidadores() {
        foreach ($this->getElements() as $element)
            $element->clearValidators()->setAttrib('readonly', 'readonly');
    }
}
