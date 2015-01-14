<?php

class Application_Form_FormConsultaVoluntario extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-consulta-voluntario.phtml')))
        );
        
        $string_filter = new Aplicacao_Filtros_StringFilter();
        
        $nome = new Zend_Form_Element_Text('nome');
        $nome->setLabel('Nome:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter($string_filter)
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
                ));


        $cpf = new Zend_Form_Element_Text('cpf');
        $cpf->setLabel('CPF:')
                ->setAttrib('class', 'cpf')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
                ));


        $buscar = new Zend_Form_Element_Submit('buscar');
        $buscar->setLabel('Buscar')
                ->setDecorators(array(
                    'ViewHelper'
                ));

        $this->addElements(array(
            $nome,
            $cpf,
            $buscar
        ));
    }

}

