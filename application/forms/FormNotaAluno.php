<?php

class Application_Form_FormNotaAluno extends Zend_Form {

    public function init() {
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'Decorators/form-nota-aluno.phtml')))
        );

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

        $atividade = new Zend_Form_Element_Select('atividade');
        $atividade->setLabel('Atividades:')
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
            $disciplina,
            $atividade,
            $turma,
            $enviar,
            $cancelar
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

    public function getNotas($dados, $quantidade, $atividade) {
        $notas = array();
        if ($quantidade > 0) {
            if ($atividade instanceof Application_Model_Atividade) {
                if (!empty($dados)) {
                    $count = 0;
                    foreach ($dados as $key => $inf) {
                        $inf = (float) $inf;
                        $pos = strpos($key, 'aluno_');

                        if ($pos !== false) {
                            if ($inf >= 0 && $inf <= $atividade->getValor()) {
                                $id_aluno = substr($key, $pos + 6);
                                $notas[$id_aluno] = new Application_Model_Nota(null, null, $inf);
                                $count++;
                            } 
                            else
                                return false;
                        }
                    }
                    if ($count == $quantidade)
                        return $notas;
                }
            }
            return false;
        }
        return $notas;
    }

}
