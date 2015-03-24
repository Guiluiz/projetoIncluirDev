<?php

class Application_Form_FormDesligamentoAluno extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-desligamento-aluno.phtml')))
        );
        
        $id_aluno = new Zend_Form_Element_Hidden('id_aluno');
        $id_aluno->setDecorators(array(
            'ViewHelper',
            'Errors',
            'Label'
        ));
        
        $data_desligamento = new Zend_Form_Element_Text('data_desligamento');
        $data_desligamento->setLabel('Data de Desligamento:')
                ->addFilter('StripTags')
                ->setAttrib('class', 'data obrigatorio')
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $motivo_desligamento = new Zend_Form_Element_Textarea('motivo_desligamento');
        $motivo_desligamento->setLabel('Motivo do Desligamento')
                ->setRequired(true)
                ->setAttrib('class', 'obrigatorio')
                ->addValidator('NotEmpty')
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
            $id_aluno,
            $data_desligamento,
            $motivo_desligamento,
            $enviar,
            $cancelar
        ));
    }

}
