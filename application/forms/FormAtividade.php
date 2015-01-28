<?php

class Application_Form_FormAtividade extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-atividade.phtml')))
        );
        
        $string_filter = new Aplicacao_Filtros_StringSimpleFilter();
        $periodo = new Application_Model_Mappers_Periodo();
        
        $periodo_atual = $periodo->getPeriodoAtual();
        
        $id_atividade = new Zend_Form_Element_Hidden('id_atividade');
        $id_atividade->setDecorators(array(
            'ViewHelper'
        ));
        
        $nome_atividade = new Zend_Form_Element_Text('nome');
        $nome_atividade->setLabel('Nome:')
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
        
        $curso = new Zend_Form_Element_Select('curso');
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

        $disciplina = new Zend_Form_Element_Select('disciplina');
        $disciplina->setLabel('Disciplina:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setRegisterInArrayValidator(false)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $turma = new Zend_Form_Element_Select('turma');
        $turma->setLabel('Turma:')
                ->setRegisterInArrayValidator(false)
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));
        
        $valor = new Zend_Form_Element_Text('valor_total');
        $valor->setLabel('Valor da Atividade:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('Between', false, array('min' => '0', 'max' => $periodo_atual->getTotalPontosPeriodo()))
                ->setAttrib('class', 'numero obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));
        
        $data_atividade = new Zend_Form_Element_Text('data_funcionamento');
        $data_atividade->setLabel('Data da Atividade:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'data')
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));
        
        $descricao = new Zend_Form_Element_Textarea('descricao');
        $descricao->setLabel('Descrição:')
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
            $nome_atividade,
            $descricao,
            $id_atividade,
            $curso,
            $data_atividade,
            $valor,
            $disciplina,
            $turma,
            $enviar,
            $cancelar
        ));
    }
    
    
    public function initializeCursos($cursos, $value = null) {
        if (!empty($cursos)) {
            $array_cursos = array('' => "Selecione");

            foreach ($cursos as $curso)
                $array_cursos[$curso->getIdCurso(true)] = $curso->getNomeCurso();

            $this->getElement('curso')
                    ->setMultiOptions($array_cursos)
                    ->setValue($value);
        }
    }
    

    public function initializeDisciplinas($disciplinas, $value = null) {
        if (!empty($disciplinas)) {
            $array_disciplinas = array('' => "Selecione");

            foreach ($disciplinas as $disciplina)
                $array_disciplinas[$disciplina->getIdDisciplina(true)] = $disciplina->getNomeDisciplina();


            $this->getElement('disciplina')
                    ->setMultiOptions($array_disciplinas)
                    ->setValue($value);
        }
    }
    
    public function initializeTurmas($turmas, $value = null) {
        if (!empty($turmas)) {
            $array_turmas = array('' => "Selecione");

            foreach ($turmas as $turma)
                $array_turmas[$turma->getIdTurma(true)] = $turma->getNomeTurma();


            $this->getElement('turma')
                    ->setMultiOptions($array_turmas)->setValue($value);
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
