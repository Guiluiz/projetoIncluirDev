<?php

class Application_Form_FormFrequenciaAluno extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-frequencia-aluno.phtml')))
        );

        $data = new Zend_Form_Element_Hidden('data');
        $data->setDecorators(array(
            'ViewHelper'
        ));

        $curso = new Zend_Form_Element_Select('curso');
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

        $disciplina = new Zend_Form_Element_Select('disciplina');
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

        $turma = new Zend_Form_Element_Select('turma');
        $turma->setLabel('Turma:')
                ->setRegisterInArrayValidator(false)
                ->setAttrib('class', 'obrigatorio')
                ->setRequired(true)
                ->addValidator('NotEmpty')
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
            $curso,
            $data,
            $disciplina,
            //$periodo,
            $turma,
            $enviar,
            $cancelar,
                //$gerar_lista
        ));
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

    public function initializePeriodo($periodos) {
        if (!empty($periodos)) {
            $periodos[''] = "Selecione";

            $this->getElement('periodo')
                    ->setMultiOptions($periodos);
        }
    }

    public function getFaltas($dados, $quantidade) {
        $faltas = array();

        if ($quantidade > 0) {
            if (!empty($dados)) {
                $count = 0;
                $data = (isset($dados['data'])) ? $dados['data'] : null;

                if (!empty($data)) {
                    foreach ($dados as $key => $inf) {
                        $pos = strpos($key, 'aluno_');

                        if ($pos !== false) {
                            $id_aluno = substr($key, $pos + 6);

                            if ($inf == 'on')
                                $faltas[$id_aluno] = new Application_Model_Falta(null, $data, $dados['observacao_' . $id_aluno]);

                            if (!empty($inf))
                                $count++;
                        }
                    }
                    if ($count == $quantidade)
                        return $faltas;
                }
            }
            return false;
        }
        return $faltas;
    }

}
