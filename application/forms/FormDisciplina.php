<?php

class Application_Form_FormDisciplina extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-disciplina.phtml')))
        );
        
        $string_filter = new Aplicacao_Filtros_StringSimpleFilter();

        $id_disciplina = new Zend_Form_Element_Hidden('id_disciplina');
        $id_disciplina->setDecorators(array(
            'ViewHelper'
        ));

        $nome = new Zend_Form_Element_Text('nome_disciplina');
        $nome->setLabel('Nome:')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter($string_filter)
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
                ));

        $curso = new Zend_Form_Element_Select('id_curso');
        $curso->setLabel('Curso:')
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

        $pre_requisito = new Zend_Form_Element_Select('pre_requisito');
        $pre_requisito->setLabel('Disciplinas (Pré-Requisitos):')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRegisterInArrayValidator(false)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
                ));

        $incluir_pre_requisito = new Zend_Form_Element_Button('incluir_pre_requisito');
        $incluir_pre_requisito->setLabel('Incluir Pré-Requisito')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors'
                ));

        /* $duracao = new Zend_Form_Element_Text('duracao');
          $duracao->setLabel('Duração:')
          ->setAttrib('class', 'obrigatorio')
          ->addFilter('StripTags')
          ->addFilter('StringTrim')
          ->setRequired(true)
          ->addValidator('NotEmpty')
          ->setDecorators(array(
          'ViewHelper',
          'Errors',
          'Label'
          )); */

        $ementa = new Zend_Form_Element_Textarea('ementa_disciplina');
        $ementa->setLabel('Ementa:')
                ->setDecorators(array(
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
            $id_disciplina,
            $nome,
            $ementa,
            $curso,
            $pre_requisito,
            $incluir_pre_requisito,
            //$duracao,
            $enviar,
            $cancelar
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

    public function initializeDisciplinas($disciplinas) {
        if (!empty($disciplinas)) {
            $array_disciplinas = array('' => "Selecione");

            foreach ($disciplinas as $disciplina)
                $array_disciplinas[$disciplina->getIdDisciplina(true)] = $disciplina->getNomeDisciplina();


            $this->getElement('pre_requisito')
                    ->setMultiOptions($array_disciplinas)->setValue('');
        }
    }

    /**
     * Retira as validações, desabilita e/ou ativa o modo somente leitura
     * dos campos do formulário
     */
    public function limpaValidadores() {
        foreach ($this->getElements() as $elemento) {
            if ($elemento->getType() == 'Zend_Form_Element_Checkbox' ||
                    $elemento->getType() == 'Zend_Form_Element_Select' ||
                    $elemento->getType() == 'Zend_Form_Element_Multiselect' ||
                    ($elemento->getType() == 'Zend_Form_Element_Button' && $elemento->getName() != 'cancelar'))
                $elemento->setAttrib('disabled', 'disabled');
            else
                $elemento->setAttrib('readonly', 'readonly');

            $elemento->clearValidators();
            $elemento->setRequired(false);
        }
    }

}
