<?php

class Application_Form_FormCurso extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-curso.phtml')))
        );
        
        $string_filter = new Aplicacao_Filtros_StringSimpleFilter();
        
        $id_curso = new Zend_Form_Element_Hidden('id_curso');
        $id_curso->setDecorators(array(
            'ViewHelper'
        ));

        $nome = new Zend_Form_Element_Text('nome_curso');
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

        $descricao = new Zend_Form_Element_Textarea('descricao_curso');
        $descricao->setLabel('Descrição')
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
            $id_curso,
            $nome,
            $descricao,
            $enviar,
            $cancelar
        ));
    }

    /**
     * Retira as validações, desabilita e/ou ativa o modo somente leitura
     * dos campos do formulário
     */
    public function limpaValidadores() {
        foreach ($this->getElements() as $element)
            $element->clearValidators()->setAttrib('readonly', 'readonly');
    }

}

