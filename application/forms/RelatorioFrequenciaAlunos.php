<?php

class Application_Form_RelatorioFrequenciaAlunos extends Zend_Form {

    public function init() {

        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-relatorio-frequencia-alunos.phtml')))
        );

        $todas_turmas = new Zend_Form_Element_Radio('todas_turmas');
        $todas_turmas->setLabel('Todas as Turmas: ')
                ->setMultiOptions(array(
                    'sim' => 'Sim',
                    'nao' => 'Não'
                ))
                ->setValue('nao')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                        //array('ViewScript', array('viewScript' => 'Decorators/form-radio.phtml'))
                ))
                ->setSeparator(' ');

        $turmas = new Zend_Form_Element_Multiselect('turmas');
        $turmas->setLabel('Turmas: (Pressione "CTRL" e clique nas turmas desejadas)')
                ->addFilter('StripTags')
                ->setRegisterInArrayValidator(false)
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->setAttrib('size', '10')
                ->addValidator('NotEmpty')
                ->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors'
        ));

        $formato_saida = new Zend_Form_Element_Radio('formato_saida');
        $formato_saida->setLabel('Formato do Arquivo: ')
                ->setMultiOptions(array(
                    base64_encode('xls') => 'xls',
                    base64_encode('xlsx') => 'xlsx',
                ))
                ->setValue(base64_encode('xlsx'))
                ->setSeparator(' ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                        //array('ViewScript', array('viewScript' => 'Decorators/form-radio.phtml'))
        ));

        $submit = new Zend_Form_Element_Submit('enviar');
        $submit->setLabel('Gerar Relatório')
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
            $todas_turmas,
            $turmas,
            $submit,
            $formato_saida,
            $cancelar
        ));
    }

    public function initializeTurmas($turmas) {
        if (!empty($turmas)) {
            $array_turmas = array();

            foreach ($turmas as $turma)
                $array_turmas[$turma->getIdTurma(true)] = $turma->toString();

            $this->getElement('turmas')
                    ->setMultiOptions($array_turmas);
        }
    }

    public function controleTurmas($dados) {
        if (isset($dados['todas_turmas']) && $dados['todas_turmas'] == 'sim')
            $this->getElement('turmas')->clearValidators()->setRequired(false);
    }

}
