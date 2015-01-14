<?php

class Application_Form_FormVoluntario extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-voluntario.phtml')))
        );

        $string_filter = new Aplicacao_Filtros_StringSimpleFilter();

        $id_voluntario = new Zend_Form_Element_Hidden('id_voluntario');
        $id_voluntario->setDecorators(array(
            'ViewHelper',
            'Errors',
            'Label'
        ));

        $nome = new Zend_Form_Element_Text('nome');
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

        $tel_fixo = new Zend_Form_Element_Text('telefone_fixo');
        $tel_fixo->setLabel('Telefone Fixo:')
                ->setAttrib('class', 'telefone')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $tel_celular = new Zend_Form_Element_Text('telefone_celular');
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

        $data_inicio = new Zend_Form_Element_Text('data_inicio');
        $data_inicio->setLabel('Data Início:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->setAttrib('class', 'data')
                ->setRequired(true)
                //->setAttrib('readonly', 'readonly')
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
                ->addFilter('StringTrim')
                //->setAttrib('readonly', 'readonly')
                ->setAttrib('class', 'data')
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
                ->addValidator('NotEmpty')
                ->addValidator('Regex', true, array('pattern' => '/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                    'messages' => array('regexNotMatch' => 'CPF Inválido')))
                ->setAttrib('class', 'obrigatorio cpf')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));


        $funcao_projeto_informatica = new Zend_Form_Element_Text('funcao_informatica');
        $funcao_projeto_informatica->setLabel('Função na Área da Informática:')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $funcao_projeto_marketing = new Zend_Form_Element_Text('funcao_marketing');
        $funcao_projeto_marketing->setLabel('Função na Área de Marketing:')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $funcao_projeto_rh = new Zend_Form_Element_Text('funcao_rh');
        $funcao_projeto_rh->setLabel('Função na Área de RH:')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
        ));

        $funcao_projeto_secretaria = new Zend_Form_Element_Text('funcao_secretaria');
        $funcao_projeto_secretaria->setLabel('Função na Área da Secretaria:')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setAttrib('class', 'obrigatorio')
                ->addFilter('StringTrim')
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

        $formacao = new Zend_Form_Element_Select('formacao');
        $formacao->setLabel('Formação:')
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


        $curso = new Zend_Form_Element_Select('curso_voluntario');
        $curso->setLabel('Curso:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->setRegisterInArrayValidator(false)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $disciplina = new Zend_Form_Element_Select('disciplina_voluntario');
        $disciplina->setLabel('Disciplina:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                //->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->setRegisterInArrayValidator(false)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $incluir_disciplina = new Zend_Form_Element_Button('incluir_disciplina');
        $incluir_disciplina->setLabel('Incluir Disciplina')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors'
        ));


        $profissao = new Zend_Form_Element_Text('profissao');
        $profissao->setLabel('Profissão:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));


        $carga_horaria = new Zend_Form_Element_Text('carga_horaria');
        $carga_horaria->setLabel('Carga Horária:')
                ->setAttrib('class', 'carga_horaria')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $atividades = new Zend_Form_Element_MultiCheckbox('atividades');
        $atividades->addMultiOptions(array(
                    Application_Model_Voluntario::$atividade_aulas => 'Aulas',
                    Application_Model_Voluntario::$atividade_informatica => 'Informática',
                    Application_Model_Voluntario::$atividade_marketing => 'Marketing',
                    Application_Model_Voluntario::$atividade_rh => 'Recursos Humanos',
                    Application_Model_Voluntario::$atividade_secretaria => 'Secretaria'
                ))
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->setDecorators(array(
                    'Errors',
                    array('ViewScript', array('viewScript' => 'Decorators/form-checkbox.phtml'))
                ))->isArray(true);

        $dias_semana = new Zend_Form_Element_MultiCheckbox('disponibilidade');
        $dias_semana->addMultiOptions(array(
                    1 => 'Segunda',
                    2 => 'Terça',
                    3 => 'Quarta',
                    4 => 'Quinta',
                    5 => 'Sexta',
                    6 => 'Sábado'
                ))
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->setDecorators(array(
                    'Errors',
                    array('ViewScript', array('viewScript' => 'Decorators/form-checkbox.phtml'))
                ))->isArray(true);


        $conhecimentos = new Zend_Form_Element_Textarea('conhecimento');
        $conhecimentos->setLabel('Conhecimentos e Habilidades (Português, Inglês, Espanhol, Francês, Internet, Word, Excel, criatividade...)')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    'Label'
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
            $atividades,
            $bairro,
            $cancelar,
            $carga_horaria,
            $cep,
            $cidade,
            $cpf,
            $conhecimentos,
            $complemento,
            $curso,
            $data_inicio,
            $data_nascimento,
            $disciplina,
            $dias_semana,
            $email,
            $endereco,
            $enviar,
            $estado,
            $formacao,
            $funcao_projeto_informatica,
            $funcao_projeto_marketing,
            $funcao_projeto_rh,
            $funcao_projeto_secretaria,
            $incluir_disciplina,
            $nome,
            $numero,
            $profissao,
            $rg,
            $tel_celular,
            $tel_fixo,
            $cidade_escolhida,
            $estado_escolhido,
            $id_voluntario
        ));
    }

    public function initializeCursos($cursos, $value = null) {
        if (!empty($cursos)) {
            $array_cursos = array('' => "Selecione");

            foreach ($cursos as $curso)
                $array_cursos[$curso->getIdCurso(true)] = $curso->getNomeCurso();

            $this->getElement('curso_voluntario')
                    ->setMultiOptions($array_cursos)
                    ->setValue($value);
        }
    }

    public function controleValidacao($dados) {
        $aux = array(Application_Model_Voluntario::$atividade_informatica => 'funcao_informatica', Application_Model_Voluntario::$atividade_marketing => 'funcao_marketing', Application_Model_Voluntario::$atividade_rh => 'funcao_rh', Application_Model_Voluntario::$atividade_secretaria => 'funcao_secretaria');

        if (isset($dados['atividades'])) {
            foreach ($aux as $key => $tipo_atividade) {
                if (!in_array($key, $dados['atividades']))
                    $this->getElement($tipo_atividade)->clearValidators()->setRequired(false)->setAttrib('disabled', 'disabled');
            }

            if (!in_array(Application_Model_Voluntario::$atividade_aulas, $dados['atividades']) || (in_array(Application_Model_Voluntario::$atividade_aulas, $dados['atividades']) && !empty($dados['disciplinas']))) {
                $this->getElement('curso_voluntario')->clearValidators()->setRequired(false);
                $this->getElement('disciplina_voluntario')->clearValidators()->setRequired(false);
            }
        }
    }

    public function setEstadoCidade($cidade, $estado) {
        $this->getElement('cidade_escolhida')->setValue($cidade);
        $this->getElement('estado_escolhido')->setValue($estado);
    }

    public function limpaValidadores() {
        foreach ($this->getElements() as $elemento) {
            if ($elemento->getType() == 'Zend_Form_Element_MultiCheckbox' ||
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
