<?php

class Application_Form_FormAluno extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-aluno.phtml')))
        );

        $string_filter = new Aplicacao_Filtros_StringSimpleFilter();

        $nome = new Zend_Form_Element_Text('nome_aluno');
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

        $sexo = new Zend_Form_Element_Select('sexo');
        $sexo->setLabel('Sexo:')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->setMultiOptions(array(
                    '' => 'Selecione',
                    Application_Model_Aluno::$sexo_masculino => 'Masculino',
                    Application_Model_Aluno::$sexo_feminino => 'Feminino'
                ))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $quantidade_turmas = new Zend_Form_Element_Select('quantidade_turmas');
        $quantidade_turmas->setLabel('Quantidade de Turmas:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setMultiOptions(array(
                    '1' => '1',
                    '2' => '2'
                ))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $recibo = new Zend_Form_Element_Text('num_recibo');
        $recibo->setLabel('Número do Recibo:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('disabled', 'disabled')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));


        $condicao_matricula = new Zend_Form_Element_Select('condicao_matricula');
        $condicao_matricula->setLabel('Condição de Matrícula:')
                ->setAttrib('disabled', 'disabled')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setMultiOptions(array(
                    '' => 'Selecione',
                    Application_Model_Pagamento::$pagamento_normal => 'Pagamento Normal',
                    Application_Model_Pagamento::$pagamento_isento_parcial => 'Isenção Parcial',
                    Application_Model_Pagamento::$pagamento_isento_total => 'Isenção Total',
                    Application_Model_Pagamento::$pagamento_pendente_parcial => 'Pendente Parcial',
                    Application_Model_Pagamento::$pagamento_pendente_total => 'Pendente Total'
                ))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $tipo_isencao_pendencia = new Zend_Form_Element_Select('tipo_isencao_pendencia');
        $tipo_isencao_pendencia->setLabel('Tipo:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRequired(false)
                ->setAttrib('disabled', 'disabled')
                ->setMultiOptions(array(
                    '' => 'Selecione',
                    Application_Model_Pagamento::$isencao_pendencia_alimento => 'Alimento',
                    Application_Model_Pagamento::$isencao_pendencia_pagamento => 'Pagamento',
                    Application_Model_Pagamento::$isencao_pendencia_alimento_pagamento => 'Alimento e Pagamento'
                ))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));


        $tel_fixo = new Zend_Form_Element_Text('telefone');
        $tel_fixo->setLabel('Telefone Fixo:')
                ->setAttrib('class', 'telefone')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $tel_celular = new Zend_Form_Element_Text('celular');
        $tel_celular->setLabel('Telefone Celular:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'telefone')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
                ->setAttrib('class', 'mail')
                ->addValidator("EmailAddress", true)
                ->addFilter("StripTags")
                ->addFilter("StringTrim")
                ->addErrorMessage('Por favor, preencha o campo acima ou o campo é inválido!')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors',
        ));

        $rg = new Zend_Form_Element_Text('rg');
        $rg->setLabel('RG:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $data_inscricao = new Zend_Form_Element_Text('data_registro');
        $data_inscricao->setLabel('Data de Matrícula:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                //->setAttrib('readonly', 'readonly')
                ->setAttrib('class', 'data obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $data_nascimento = new Zend_Form_Element_Text('data_nascimento');
        $data_nascimento->setLabel('Data de Nascimento:')
                ->addFilter('StripTags')
                //->setAttrib('readonly', 'readonly')
                ->setAttrib('class', 'data')
                ->addFilter('StringTrim')
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $cpf = new Zend_Form_Element_Text('cpf');
        $cpf->setLabel('CPF:')
                ->setRequired(true)
                ->addValidator('Regex', true, array('pattern' => '/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                    'messages' => array('regexNotMatch' => 'CPF Inválido')))
                ->addValidator('NotEmpty')
                ->setAttrib('class', 'obrigatorio cpf')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));



        $is_responsavel = new Zend_Form_Element_Checkbox('is_cpf_responsavel');
        $is_responsavel->setLabel('CPF do Responsável:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $nome_responsavel = new Zend_Form_Element_Text('nome_responsavel');
        $nome_responsavel->setLabel('Nome do Responsável:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter($string_filter)
                ->setAttrib('class', 'obrigatorio')
                ->setAttrib('disabled', 'disabled')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));


        $cep = new Zend_Form_Element_Text('cep');
        $cep->setLabel('CEP:')
                ->setAttrib('class', 'cep')
                ->setAttrib('controle', 1)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $endereco = new Zend_Form_Element_Text('endereco');
        $endereco->setLabel('Endereço:')
                ->addValidator("NotEmpty")
                ->setAttrib('controle', 1)
                ->setAttrib('class', 'endereco')
                ->addFilter("StripTags")
                ->addFilter("StringTrim")
                ->addErrorMessage('Por favor, preencha o campo acima')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors',
        ));

        $numero = new Zend_Form_Element_Text('numero');
        $numero->setLabel('Número:')
                ->setAttrib('controle', 1)
                ->setAttrib('class', 'num')
                ->addValidator('Digits')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $complemento = new Zend_Form_Element_Text('complemento');
        $complemento->setLabel('Complemento:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $bairro = new Zend_Form_Element_Text('bairro');
        $bairro->setLabel('Bairro:')
                ->setAttrib('controle', 1)
                ->setAttrib('class', 'bairro')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $estado = new Zend_Form_Element_Select('estado');
        $estado->setLabel('UF:')
                ->setRegisterInArrayValidator(false)
                ->setAttrib('class', 'estado')
                ->setAttrib('controle', 1)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $cidade = new Zend_Form_Element_Select('cidade');
        $cidade->setLabel('Cidade:')
                ->setRegisterInArrayValidator(false)
                ->setAttrib('class', 'cidade')
                ->setAttrib('controle', 1)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $escolaridade = new Zend_Form_Element_Select('escolaridade');
        $escolaridade->setLabel('Escolaridade:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRequired(false)
                ->setMultiOptions(array(
                    'Fundamental Completo' => 'Fundamental Completo',
                    'Fundamental Incompleto' => 'Fundamental Incompleto',
                    'Médio Completo' => 'Médio Completo',
                    'Médio Incompleto' => 'Médio Incompleto',
                    'Superior Completo' => 'Superior Completo',
                    'Superior Incompleto' => 'Superior Incompleto'
                ))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $curso = new Zend_Form_Element_Select('curso');
        $curso->setLabel('Curso:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRegisterInArrayValidator(false)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
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

        $turma = new Zend_Form_Element_Select('turma');
        $turma->setLabel('Turma:')
                ->setRegisterInArrayValidator(false)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $incluir_turma = new Zend_Form_Element_Button('incluir_turma');
        $incluir_turma->setLabel('Incluir Turma')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors'
        ));


        $pagamento_turma = new Zend_Form_Element_Select('pagamento_turma');
        $pagamento_turma->setLabel('Turmas do Aluno:')
                ->setAttrib('disabled', 'disabled')
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

        $alimento = new Zend_Form_Element_Select('alimento');
        $alimento->setLabel('Tipo de Alimento:')
                ->setAttrib('disabled', 'disabled')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRegisterInArrayValidator(false)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $quantidade_alimento = new Zend_Form_Element_Text('quantidade_alimento');
        $quantidade_alimento->setLabel('Quantidade(kg):')
                ->setAttrib('disabled', 'disabled')
                ->addFilter('StripTags')
                ->setAttrib('class', 'kg')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $incluir_alimento = new Zend_Form_Element_Button('incluir_alimento');
        $incluir_alimento->setLabel('Incluir Alimento')
                ->setAttrib('disabled', 'disabled')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors'
        ));

        $valor_pago = new Zend_Form_Element_Text('valor_pago');
        $valor_pago->setLabel('Valor Pago(R$):')
                ->setAttrib('disabled', 'disabled')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                //->setValue('00,00')
                ->setAttrib('class', 'dinheiro')
                //->setRequired(true)
                //->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $registrar_pagamento = new Zend_Form_Element_Button('registrar_pagamento');
        $registrar_pagamento->setLabel('Registrar Pagamento dessa Turma')
                ->setAttrib('disabled', 'disabled')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors'
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


        $cidade_escolhida = new Zend_Form_Element_Hidden('cidade_escolhida');
        $cidade_escolhida->setAttrib('controle', 1)
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $estado_escolhido = new Zend_Form_Element_Hidden('estado_escolhido');
        $estado_escolhido->setAttrib('controle', 1)
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $id_aluno = new Zend_Form_Element_Hidden('id_aluno');
        $id_aluno->setDecorators(array(
            'ViewHelper',
            'Errors',
            'Label'
        ));

        $this->addElements(array(
            $alimento,
            $bairro,
            $cancelar,
            $cep,
            $cidade,
            $complemento,
            $cpf,
            $curso,
            $data_inscricao,
            $data_nascimento,
            $disciplina,
            $email,
            $endereco,
            $enviar,
            $escolaridade,
            $estado,
            $rg,
            $condicao_matricula,
            $tipo_isencao_pendencia,
            $recibo,
            $incluir_alimento,
            $incluir_turma,
            $is_responsavel,
            $nome,
            $nome_responsavel,
            $numero,
            $pagamento_turma,
            $quantidade_alimento,
            $quantidade_turmas,
            $registrar_pagamento,
            $sexo,
            $tel_celular,
            $tel_fixo,
            $turma,
            $valor_pago,
            $cidade_escolhida,
            $estado_escolhido,
            $id_aluno
        ));
    }

    public function setEstadoCidade($cidade, $estado) {
        $this->getElement('cidade_escolhida')->setValue($cidade);
        $this->getElement('estado_escolhido')->setValue($estado);
    }

    public function initializeTurmasAlunos($turmas, $turmas_alunos = null) {
        $array_aux = array();

        foreach ($turmas as $turma) {
            if (!empty($turmas_alunos)) {
                if (in_array($turma->getIdTurma(true), $turmas_alunos))
                    $array_aux[$turma->getIdTurma(true)] = $turma->getDisciplina()->getNomeDisciplina() . ' - ' . $turma->getNomeTurma() . ' | ' . $turma->getHorarioInicio() . ' - ' . $turma->getHorarioFim();
            } 
            else
                $array_aux[$turma->getIdTurma(true)] = $turma->getDisciplina()->getNomeDisciplina() . ' - ' . $turma->getNomeTurma() . ' | ' . $turma->getHorarioInicio() . ' - ' . $turma->getHorarioFim();
        }

        $this->getElement('pagamento_turma')->setAttrib('disabled', null)->setMultiOptions($array_aux);
    }

    public function seTurmasAlunos($turmas_aluno) {
        $array_aux = array();
        foreach ($turmas_aluno as $turma)
            $array_aux[$turma->getIdTurma(true)] = $turma->getDisciplina()->getNomeDisciplina() . ' - ' . $turma->getNomeTurma();

        $this->getElement('pagamento_turma')->setMultiOptions($array_aux);
    }

    public function limpaValidadores() {
        foreach ($this->getElements() as $elemento) {
            if ($elemento->getType() == 'Zend_Form_Element_MultiCheckbox' ||
                    $elemento->getType() == 'Zend_Form_Element_Checkbox' ||
                    $elemento->getType() == 'Zend_Form_Element_Text' ||
                    $elemento->getType() == 'Zend_Form_Element_Select' ||
                    $elemento->getType() == 'Zend_Form_Element_Multiselect' ||
                    ($elemento->getType() == 'Zend_Form_Element_Button' && $elemento->getName() != 'cancelar'))
                $elemento->setAttrib('disabled', 'disabled');

            $elemento->clearValidators();
            $elemento->setRequired(false);
        }
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

    public function initializeTurmas($turmas) {
        if (!empty($turmas)) {
            $array_turmas = array('' => "Selecione");

            foreach ($turmas as $turma)
                $array_turmas[$turma->getIdTurma(true)] = $turma->getNomeTurma();

            $this->getElement('turma')
                    ->setMultiOptions($array_turmas)->setValue('');
        }
    }

    public function initializeAlimentos($alimentos) {
        if (!empty($alimentos)) {
            $array_alimentos = array('' => "Selecione");

            foreach ($alimentos as $alimento)
                $array_alimentos[$alimento->getIdAlimento(true)] = $alimento->getNomeAlimento();

            $this->getElement('alimento')
                    ->setMultiOptions($array_alimentos)->setValue('');
        }
    }

}
