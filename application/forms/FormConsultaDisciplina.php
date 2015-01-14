<?php

class Application_Form_FormConsultaDisciplina extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-consulta-disciplina.phtml')))
        );
        
        $string_filter = new Aplicacao_Filtros_StringFilter();
        
        $nome = new Zend_Form_Element_Text('nome_disciplina');
        $nome->setLabel('Nome da Disciplina:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter($string_filter)
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));
        
        $curso = new Zend_Form_Element_Select('id_curso');
        $curso->setLabel('Curso:')
                ->addFilter('StripTags')
                ->setRegisterInArrayValidator(false)
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $buscar = new Zend_Form_Element_Submit('buscar');
        $buscar->setLabel('Buscar')
                ->setDecorators(array(
                    'ViewHelper'
        ));

        $this->addElements(array(
            $nome,
            $curso,
            $buscar
        ));
        
    }
    
    public function initializeCursos($cursos, $value = null) {
        if (!empty($cursos)) {
            $array_cursos = array('' => "Selecione");

            foreach ($cursos as $curso)
                $array_cursos[$curso->getIdCurso(true)] = $curso->getNomeCurso();

            $this->getElement('id_curso')
                    ->setMultiOptions($array_cursos)
                    ->setValue($value);
        }
    }

}

