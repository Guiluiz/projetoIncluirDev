<?php

class Application_Form_FormConsultaAtividade extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-consulta-atividade.phtml')))
        );
        
        
        $turma = new Zend_Form_Element_Select('turma');
        $turma->setLabel('Turma:')
                ->setRegisterInArrayValidator(false)
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $buscar = new Zend_Form_Element_Submit('buscar');
        $buscar->setLabel('Buscar')
                ->setAttrib('class', 'cancel')
                ->setDecorators(array(
                    'ViewHelper'
        ));
        
        $this->addElements(array(
            $turma,
            $buscar
        ));
    }
    
    public function initializeTurmas($turmas) {
        if (!empty($turmas)) {
            $array_turmas = array('' => 'Selecione');

            foreach ($turmas as $turma)
                $array_turmas[$turma->getIdTurma(true)] = $turma->toString();

            $this->getElement('turma')
                    ->setMultiOptions($array_turmas);
        }
    }

}
