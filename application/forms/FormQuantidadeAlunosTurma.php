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

    public function initializePeriodo($periodos, $periodo_atual = null) {
        $array_periodos = array();

        if (!empty($periodos)) {
            $array_periodos[''] = 'Todos os Períodos';

            foreach ($periodos as $periodo) {
                if ($periodo instanceof Application_Model_Periodo)
                    $array_periodos[$periodo->getIdPeriodo(true)] = $periodo->getNomePeriodo();
            }

            $this->getElement('periodo')
                    ->setMultiOptions($array_periodos)
                    ->setValue(($periodo_atual instanceof Application_Model_Periodo) ? $periodo_atual->getIdPeriodo(true) : '');
        }
    }

}
