<?php

class Application_Form_FormConsultaCurso extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-consulta-curso.phtml')))
        );
        
        $string_filter = new Aplicacao_Filtros_StringFilter();
        
        $nome = new Zend_Form_Element_Text('nome_curso');
        $nome->setLabel('Nome do Curso:')
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
                ->setDecorators(array(
                    'ViewHelper'
        ));

        $this->addElements(array(
            $nome,
            $buscar
        ));
    }

}

