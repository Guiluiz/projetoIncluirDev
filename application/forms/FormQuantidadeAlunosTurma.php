<?php

class Application_Form_FormQuantidadeAlunosTurma extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-quantidade-alunos-turma.phtml')))
        );

        $periodo = new Zend_Form_Element_Select('periodo');
        $periodo->setLabel('Período:')
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

        $this->addElements(array(
            $periodo
        ));
    }

    public function initializePeriodo($periodos, $value) {
        if (!empty($periodos)) {
            $periodos[''] = "Todos os Períodos";
            $this->getElement('periodo')
                    ->setMultiOptions($periodos)
                    ->setValue($value);
        }
    }

}
