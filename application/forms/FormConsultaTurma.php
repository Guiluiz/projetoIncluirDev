<?php

class Application_Form_FormConsultaTurma extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-consulta-turma.phtml')))
        );

        $string_filter = new Aplicacao_Filtros_StringFilter();

        $nome = new Zend_Form_Element_Text('nome_turma');
        $nome->setLabel('Nome da Turma:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter($string_filter)
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $disciplina = new Zend_Form_Element_Select('disciplina');
        $disciplina->setLabel('Disciplina:')
                ->addFilter('StripTags')
                ->setRegisterInArrayValidator(false)
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $periodo = new Zend_Form_Element_Select('periodo');
        $periodo->setLabel('Período:')
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
            $disciplina,
            $periodo,
            $buscar
        ));
    }

    public function initializeDisciplinas($disciplinas, $value = null) {
        if (!empty($disciplinas)) {
            $array_disciplinas = array('' => "Selecione");

            foreach ($disciplinas as $disciplina)
                $array_disciplinas[$disciplina->getIdDisciplina(true)] = $disciplina->getCurso()->getNomeCurso() . ' - ' . $disciplina->getNomeDisciplina();

            $this->getElement('disciplina')
                    ->setMultiOptions($array_disciplinas)
                    ->setValue($value);
        }
    }

    public function initializePeriodo($periodos, $periodo_atual = null) {
        $array_periodos = array();
        
        if (!empty($periodos)) {
            $array_periodos[''] = 'Selecione';

            foreach ($periodos as $periodo) {
                if ($periodo instanceof Application_Model_Periodo)
                    $array_periodos[$periodo->getIdPeriodo()] = $periodo->getNomePeriodo();
            }

            $this->getElement('periodo')
                    ->setMultiOptions($array_periodos)
                    ->setValue(($periodo_atual instanceof Application_Model_Periodo) ? $periodo_atual->getIdPeriodo() : '');
        }
    }

}
