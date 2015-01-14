<?php

class Application_Form_FormDatasAtividades extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-datas-atividades.phtml')))
        );

        $data = new Zend_Form_Element_Text('data');
        $data->setLabel('Data de Atividade:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('readonly', 'readonly')
                // ->setAttrib('class', 'obrigatorio')
                // ->setRequired(true)
                // ->addValidator('NotEmpty')
                ->addValidator('Regex', true, array('pattern' => '/^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])[\/](0[1-9]|1[0,1,2])[\/]\d{4}$/',
                    'messages' => array('regexNotMatch' => 'Data Inválida')))
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $todos_sabados = new Zend_Form_Element_Checkbox('todos_sabados');
        $todos_sabados->setLabel('Todos os Sábados do Período')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
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
            $data,
            $enviar,
            $todos_sabados,
            $cancelar
        ));
    }

    public function verificaDatas(&$dados) {
        try {
            $count = 0;
            foreach ($dados as $key => &$value) {
                if (strpos($key, 'data_') !== false) {
                    $count++;
                    $value = DateTime::createFromFormat('d/m/Y', $value);
                    if (!$value instanceof DateTime)
                        return false;
                }
            }
            if ($count == 0)
                return false;

            return true;
        } catch (Exception $e) {
            $e->getMessage();
            return false;
        }
    }

}
