<?php

class Application_Form_RelatorioAlunosTurma extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-relatorio-alunos-turma.phtml')))
        );

        $periodo = new Zend_Form_Element_Select('periodo');
        $periodo->setLabel('Período:')
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
        
        $unico_sheet = new Zend_Form_Element_Radio('unico_sheet');
        $unico_sheet->setLabel('Único Sheet: ')
                ->setMultiOptions(array(
                    base64_encode('sim') => 'Sim',
                    base64_encode('nao') => 'Não',
                ))
                ->setValue(base64_encode('sim'))
                ->setSeparator(' ')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                        //array('ViewScript', array('viewScript' => 'Decorators/form-radio.phtml'))
        ));

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
            $periodo,
            $todas_turmas,
            $turmas,
            $submit,
            $formato_saida,
            $unico_sheet,
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

    public function initializePeriodo($periodos, $periodo_atual = null) {
        $array_periodos = array();

        if (!empty($periodos)) {
            $array_periodos[''] = 'Selecione';

            foreach ($periodos as $periodo) {
                if ($periodo instanceof Application_Model_Periodo)
                    $array_periodos[$periodo->getIdPeriodo(true)] = $periodo->getNomePeriodo();
            }

            $this->getElement('periodo')
                    ->setMultiOptions($array_periodos)
                    ->setValue(($periodo_atual instanceof Application_Model_Periodo) ? $periodo_atual->getIdPeriodo(true) : '');
        }
    }

}
