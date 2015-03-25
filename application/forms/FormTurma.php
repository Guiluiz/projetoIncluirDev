<?php

class Application_Form_FormTurma extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-turma.phtml')))
        );
        
        //$string_filter = new Aplicacao_Filtros_StringSimpleFilter();
        
        $id_turma = new Zend_Form_Element_Hidden('id_turma');
        $id_turma->setDecorators(array(
            'ViewHelper'
        ));

        $nome = new Zend_Form_Element_Text('nome_turma');
        $nome->setLabel('Nome:')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                //->addFilter($string_filter)
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

        $sala = new Zend_Form_Element_Text('sala');
        $sala->setLabel('Sala:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
                ));

        
        $professor = new Zend_Form_Element_Select('professor');
        $professor->setLabel('Professor:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRegisterInArrayValidator(false)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
                ));

        $incluir_voluntario = new Zend_Form_Element_Button('incluir_professor');
        $incluir_voluntario->setLabel('Incluir Professor')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors'
                ));

        $hora_inicio = new Zend_Form_Element_Text('horario_inicio');
        $hora_inicio->setLabel('Horario de: ')
                ->addValidator('Regex', true, array('pattern' => '/^([0-1][0-9]|[2][0-3])(:([0-5][0-9]))$/',
                    'messages' => array('regexNotMatch' => 'Hora Inválida')
                ))
                ->setAttrib('class', 'time')
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                ));

        $hora_termino = new Zend_Form_Element_Text('horario_fim');
        $hora_termino->setLabel('até: ')
                ->addValidator('Regex', true, array('pattern' => '/^([0-1][0-9]|[2][0-3])(:([0-5][0-9]))$/',
                    'messages' => array('regexNotMatch' => 'Hora Inválida')
                ))
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'time')
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                ));

        $data_inicio = new Zend_Form_Element_Text('data_inicio');
        $data_inicio->setLabel('Data de Início:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('readonly', 'readonly')
                ->setAttrib('class', 'obrigatorio')
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
                ));

        $data_final = new Zend_Form_Element_Text('data_fim');
        $data_final->setLabel('Data de Conclusão:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('readonly', 'readonly')
                ->setAttrib('class', 'obrigatorio')
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
                ));

        /*$status = new Zend_Form_Element_Select('status');
        $status->setLabel('Status:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->setMultiOptions(array(
                    '' => 'Selecione',
                    base64_encode(Application_Model_Turma::$status_nao_iniciada) => 'Não Iniciada',
                    base64_encode(Application_Model_Turma::$status_iniciada) => 'Iniciada',
                    base64_encode(Application_Model_Turma::$status_cancelada) => 'Cancelada',
                    base64_encode(Application_Model_Turma::$status_concluida) => 'Concluída',
                ))
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
                ));*/

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
            $id_turma,
            $curso,
            $cancelar,
            $data_final,
            $data_inicio,
            $disciplina,
            $sala,
            $enviar,
            $hora_inicio,
            $hora_termino,
            $id_turma,
            $incluir_voluntario,
            $nome,
            $professor,
           // $status
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
                    ->setMultiOptions($array_disciplinas)->setValue($value);
        }
    }

    public function initializeProfessores($professores) {
        if (!empty($professores)) {
            $array_professores = array('' => "Selecione");

            foreach ($professores as $professor)
                $array_professores[$professor->getIdProfessor(true)] = $professor->getNomeVoluntario();

            $this->getElement('professor')
                    ->setMultiOptions($array_professores)->setValue('');
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

