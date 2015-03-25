<?php

class Application_Form_FormDistribuicaoTurmas extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-distribuicao.phtml')))
        );

        $curso = new Zend_Form_Element_Select('curso_0');
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

        $disciplina = new Zend_Form_Element_Select('disciplina_0');
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

        $hora_inicio = new Zend_Form_Element_Text('horario_inicio_0');
        $hora_inicio->setLabel('Horario Inicial: ')
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

        $hora_termino = new Zend_Form_Element_Text('horario_fim_0');
        $hora_termino->setLabel('Hora Final: ')
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

        $dividir_por = new Zend_Form_Element_Select('dividir_por_0');
        $dividir_por->setLabel('Dividir Por:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->setMultiOptions(array(
                    '' => 'Selecione',
                    1 => 'Idade',
                    2 => 'Nome'
                ))
                ->addValidator('NotEmpty')
                ->setRegisterInArrayValidator(false)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $ordem = new Zend_Form_Element_Select('ordem_0');
        $ordem->setLabel('Ordem:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setMultiOptions(array(
                    '' => 'Selecione',
                    1 => 'Crescente',
                    2 => 'Decresente'
                ))
                ->setRegisterInArrayValidator(false)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $divisao_igualitaria = new Zend_Form_Element_Checkbox('divisao_igualitaria_0');
        $divisao_igualitaria->setLabel('Divisão igualitária:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $quantidade = new Zend_Form_Element_Text('quantidade_0');
        $quantidade->setLabel('Quantidade')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('')
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors',
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
            $curso,
            $disciplina,
            $hora_inicio,
            $hora_termino,
            $ordem,
            $dividir_por,
            $divisao_igualitaria,
            $quantidade,
            $enviar,
            $cancelar
        ));
    }

    public function initializeCursos($cursos) {
        if (!empty($cursos)) {
            $array_cursos = array('' => "Selecione");

            foreach ($cursos as $curso)
                $array_cursos[$curso->getIdCurso(true)] = $curso->getNomeCurso();

            $this->getElement('curso')
                    ->setMultiOptions($array_cursos);
        }
    }

    /**
     * Método para inicializar campos de turma da reserva
     * @param array $dados
     */
    public function atualizaForm($dados) {
        $quantidade = $dados['quantidade_turmas'];

        $departamentos = $this->getElement('departamento_0')->getMultiOptions();

        for ($index = 1; $index <= $quantidade; $index++) {
            $departamento = new Zend_Form_Element_Select('departamento_' . $index);
            $departamento->setLabel('Departamento')
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->setAttrib('class', 'form-control')
                    ->setRequired(true)
                    ->setRegisterInArrayValidator(false)
                    ->setMultiOptions($departamentos)
                    ->setDecorators(array(
                        'ViewHelper',
                        'Label',
                        'Errors'
            ));

            $materia = new Zend_Form_Element_Select('materia_' . $index);
            $materia->setLabel('Matéria')
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->setAttrib('class', 'form-control')
                    ->addErrorMessage('Escolha uma das matérias acima')
                    ->setRegisterInArrayValidator(false)
                    ->setRequired(true)
                    ->addValidator('NotEmpty')
                    ->setDecorators(array(
                        'ViewHelper',
                        'Label',
                        'Errors'
            ));

            $materia_selecionada = new Zend_Form_Element_Hidden('mat_selecionada_' . $index);
            $materia_selecionada->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors'
            ));

            $turma = new Zend_Form_Element_Text('turma_' . $index);
            $turma->setLabel('Turma')
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->setRequired(true)
                    ->setAttrib('placeholder', 'Indique o nome da turma. Campo obrigatório')
                    ->setAttrib('class', 'form-control')
                    ->addValidator('NotEmpty')
                    ->setDecorators(array(
                        'ViewHelper',
                        'Label',
                        'Errors'
            ));
            $this->addElements(array($departamento, $materia, $turma, $materia_selecionada));
        }
    }

}
