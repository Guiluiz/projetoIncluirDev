<?php

/**
 * Classe para representar uma disciplina do projeto
 * @author Pablo Augusto
 */
class Application_Model_Disciplina {

    /**
     * @var int 
     */
    private $id_disciplina;

    /**
     * @var String 
     */
    private $nome_disciplina;

    /**
     * @var String 
     */
    private $ementa_disciplina;

    /**
     * @var Application_Model_Curso
     */
    private $curso;

    /**
     * @var array 
     */
    private $pre_requisitos;

    public function __construct($id_disciplina, $nome_disciplina = null, $ementa_disciplina = null, $curso = null, $pre_requisito = null) {
        $this->id_disciplina = ((!empty($id_disciplina)) ? (int) $id_disciplina : null);
        $this->nome_disciplina = $nome_disciplina;
        $this->ementa_disciplina = $ementa_disciplina;
        $this->curso = $curso;
        $this->pre_requisitos = array();
        $this->addPreRequisitos($pre_requisito);
    }

    public function getIdDisciplina($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_disciplina);
        return $this->id_disciplina;
    }

    public function getNomeDisciplina() {
        return $this->nome_disciplina;
    }

    public function getEmentaDisciplina() {
        return $this->ementa_disciplina;
    }

    public function getCurso() {
        return $this->curso;
    }

    public function addPreRequisitos($disciplina) {
        if ($disciplina instanceof Application_Model_Disciplina)
            $this->pre_requisitos[] = $disciplina;
        
        else if (is_array($disciplina) && !empty($disciplina)) {
            foreach ($disciplina as $aux) {
                if ($aux instanceof Application_Model_Disciplina)
                    $this->pre_requisitos[] = $aux;
            }
        }
    }

    public function getPreRequisitos() {
        return $this->pre_requisitos;
    }

    public function hasPreRequisitos(){
        if(count($this->pre_requisitos)>0)
            return true;
        return false;
    }
    
    public function parseArray($isView = null) {
        return array(
            'id_disciplina' => $this->getIdDisciplina($isView),
            'nome_disciplina' => $this->nome_disciplina,
            'ementa_disciplina' => $this->ementa_disciplina,
            'id_curso' => $this->curso->getIdCurso()
        );
    }

}
