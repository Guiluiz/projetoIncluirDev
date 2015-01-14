<?php

class Application_Form_FormConsultaAlimento extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-consulta-alimento.phtml')))
        );
        
        $string_filter = new Aplicacao_Filtros_StringFilter();
        
        $nome = new Zend_Form_Element_Text('nome_alimento');
        $nome->setLabel('Nome do Alimento:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter($string_filter)
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $buscar = new Zend_Form_Element_Submit('buscar');
        $buscar->setLabel('Buscar')
                ->setAttrib('class', 'cancel')
                ->setDecorators(array(
                    'ViewHelper'
        ));

        $this->addElements(array(
            $nome,
            $buscar
        ));
    }

}
