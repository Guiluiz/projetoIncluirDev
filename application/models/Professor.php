<?php

/**
 * Classe para representação de um professor
 * @author Projeto Incluir
 */
class Application_Model_Professor extends Application_Model_Voluntario {

    private $disciplinas_ministradas;

    public function __construct($id_voluntario, $nome = null, $cpf = null, $rg = null, $data_nascimento = null, $email = null, $formacao = null, $profissao = null, $tel_fixo = null, $tel_celular = null, $endereco = null, $bairro = null, $cidade = null, $estado = null, $numero = null, $complemento = null, $cep = null, $carga_prevista = null, $data_inicio = null, $data_desligamento = null, $motivo_desligamento = null, $status = null, $conhecimentos = null, $disponibilidade = null, $disciplinas_ministradas = null, $funcao = null, $tipo_funcao = null) {
        parent::__construct($id_voluntario, $nome, $cpf, $rg, $data_nascimento, $email, $formacao, $profissao, $tel_fixo, $tel_celular, $endereco, $bairro, $cidade, $estado, $numero, $complemento, $cep, $carga_prevista, $data_inicio, $data_desligamento, $motivo_desligamento, $status, $conhecimentos, $disponibilidade, $funcao, $tipo_funcao);

        $this->disciplinas_ministradas = array();
        $this->addDisciplinasMinistradas($disciplinas_ministradas);
    }

    public function getDisciplinasMinistradas() {
        return $this->disciplinas_ministradas;
    }

    public function addDisciplinasMinistradas($disciplina) {
        if ($disciplina instanceof Application_Model_Disciplina)
            $this->disciplinas_ministradas[] = $disciplina;

        else if (is_array($disciplina) && !empty($disciplina)) {
            foreach ($disciplina as $aux) {
                if ($aux instanceof Application_Model_Disciplina)
                    $this->disciplinas_ministradas[] = $aux;
            }
        }
    }

    public function hasDisciplinasMinistradas() {
        if (count($this->disciplinas_ministradas) > 0)
            return true;
        return false;
    }

    public function getIdProfessor($isView = null) {
        if (!empty($isView))
            return base64_encode($this->id_voluntario);
        return $this->id_voluntario;
    }
    
    public function parseArray($isView = null) {
        $aux = parent::parseArray($isView);
        
        if(!empty($isView)){
            $aux['atividades'][] = Application_Model_Voluntario::$atividade_aulas;
            $aux['id_professor'] = $this->getIdProfessor($isView);
        }
        return $aux;
    }

}

?>
