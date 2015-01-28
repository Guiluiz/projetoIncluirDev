<?php

class Application_Form_FormPeriodo extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-periodo.phtml')))
        );

        $id_periodo = new Zend_Form_Element_Hidden('id_periodo');
        $id_periodo->setDecorators(array(
            'ViewHelper',
            'Label',
            'Errors'
        ));

        $data_inicio = new Zend_Form_Element_Text('data_inicio');
        $data_inicio->setLabel('Data Inicial:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('readonly', 'readonly')
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $data_termino = new Zend_Form_Element_Text('data_termino');
        $data_termino->setLabel('Data Término:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('readonly', 'readonly')
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $identificacao = new Zend_Form_Element_Text('nome_periodo');
        $identificacao->setLabel('Identificação do Período (ex: 2014/1)')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $valor_liberacao = new Zend_Form_Element_Text('valor_liberacao_periodo');
        $valor_liberacao->setLabel('Valor de Liberação(R$):')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'dinheiro obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));
        
        $quantidade_alimentos = new Zend_Form_Element_Text('quantidade_alimentos');
        $quantidade_alimentos->setLabel('Quantidade de Alimentos (kg):')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'numero obrigatorio')
                ->setRequired(true)
                ->addValidator('Between', false, array('min' => '0', 'max' => '10'))
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        
        $min_freq_aprov = new Zend_Form_Element_Text('freq_min_aprov');
        $min_freq_aprov->setLabel('Frequência Mínima para Aprovação (%):')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->addValidator('Between', false, array('min' => '0', 'max' => '100'))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $total_pts_periodo = new Zend_Form_Element_Text('total_pts_periodo');
        $total_pts_periodo->setLabel('Total de Pontos a ser Distribuído:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $min_pts_aprov = new Zend_Form_Element_Text('min_pts_aprov');
        $min_pts_aprov->setLabel('Mínimo de Pontos para Aprovação:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
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


        $this->addElements(array(
            $id_periodo,
            $data_inicio,
            $data_termino,
            $identificacao,
            $valor_liberacao,
            $total_pts_periodo,
            $min_freq_aprov,
            $min_pts_aprov,
            $quantidade_alimentos,
            $enviar,
            $cancelar
        ));
    }

}
