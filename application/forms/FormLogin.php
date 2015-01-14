<?php

class Application_Form_FormLogin extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-login.phtml'))
        ));

        $login = new Zend_Form_Element_Text('login');
        $login->setLabel('Login')
                ->addValidator('NotEmpty')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
                ));

        $senha = new Zend_Form_Element_Password('pass');
        $senha->setLabel('Senha')
                ->addValidator('NotEmpty')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
                ));

        $submit = new Zend_Form_Element_Submit('enviar');
        $submit->setlabel('Efetuar Login')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors'
                ));

        $this->addElements(array(
            $login,
            $senha,
            $submit
        ));
    }

}

