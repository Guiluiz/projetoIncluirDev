<?php

/**
 *  Classe para representar um curso do projeto
 *  @author Projeto Incluir 
 */

class Application_Model_Curso {
    
    /**
     * @var int 
     */
    private $id_curso;
    
    /**
     * @var String 
     */
    private $nome_curso;
    
    /**
     * @var string 
     */
    private $descricao_curso;
    
    public function __construct($id_curso, $nome_curso=null, $descricao_curso=null) {
        $this->id_curso = ((!empty($id_curso)) ? (int) $id_curso : null);
        $this->nome_curso = $nome_curso;
        $this->descricao_curso = $descricao_curso;
    }
    
    /**
     * Retorna o id do curso
     * @param boolean $isView Indica se o id será criptografado ou não
     * @return int|string
     */
    public function getIdCurso($isView = null){
        if(!empty($isView))
            return base64_encode($this->id_curso);
        return $this->id_curso;
    }
    
    public function getNomeCurso(){
        return $this->nome_curso;
    }
    
    public function getDescricaoCurso(){
        return $this->descricao_curso;
    }
    
    /**
     * Retorna um array com informações do curso
     * Utilizado tanto para popular formulários de curso quanto para cadastro/alteração no banco de dados
     * @param boolean $isView Indica o formato de saída de alguns dos dados
     * @return array
     */
    public function parseArray($isView = null){
        return array(
            'id_curso' => $this->getIdCurso($isView),
            'nome_curso' => $this->nome_curso,
            'descricao_curso' => $this->descricao_curso
        );
    }
}
