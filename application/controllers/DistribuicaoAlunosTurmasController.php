<?php

class DistribuicaoAlunosTurmasController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Distribuição de Alunos nas Turmas";

        $mapper_turma = new Application_Model_Mappers_Turma();
        $mapper_alunos = new Application_Model_Mappers_Aluno();

        $alunos_turmas = $mapper_alunos->getInfAlunosDisciplinaHorario();
        $max_alunos_turmas_disciplinas = array(
            'Básico de Informática' => 24,
            'Noções Básicas de Direito I' => 45,
            'Espanhol Instrumental Básico I Para Eventos' => 45,
            'Espanhol Instrumental Básico II Para Eventos' => 45,
            'Empreendedorismo, Negócios e Educação Financeira I' => 45,
            'Formação de Líderes e Voluntários' => 45,
            'Empreendedorismo, Negócios e Educação Financeira II' => 45,
            'Inglês Instrumental Básico I Para Eventos' => 45,
            'Inglês Instrumental Básico II Para Eventos' => 45,
            'Inglês Instrumental Intermediário I Para Eventos' => 45,
            'Inglês Para Conversação' => 45,
            'Inglês Básico I Para Crianças' => 32,
            'Inglês Básico II Para Crianças' => 30,
            'Oficina de leitura e interpretação geográfica da cidade' => 45,
            'Português Para Adultos I - Noções Gerais' => 45,
            'Português Para Adultos II - Nova Gramática e Produção de Textos' => 45,
            'Ciências, Física, Português, Matemática e Química' => 45
        );

        foreach ($alunos_turmas as $inf_turma => $alunos) {
            $aux = explode('_', $inf_turma);

            if (!empty($aux[0])) {
                $id_disciplina = $aux[0];
                $nome_disciplina = $aux[1];
                $turmas = $mapper_turma->getTurmasByDisciplinaHorario($id_disciplina, $aux[2], $aux[3]);

                if (isset($max_alunos_turmas_disciplinas[$nome_disciplina])) {
                    if ($nome_disciplina == 'Básico de Informática') {
                        usort($alunos, function ($a, $b) {
                            return strcmp($a['nome_aluno'], $b['nome_aluno']);
                        });
                    }

                    $parada = $max_alunos_turmas_disciplinas[$nome_disciplina];
                    if (count($alunos) > $parada) {
                        $i = 0;
                        $j = 0;

                        echo '<br><br><b>' . $nome_disciplina . '</b> | Alunos:<b>' . count($alunos) . '</b> | Turmas: <b>' . count($turmas) . '</b> | Limite: <b>' . $parada . '</b><br><br>';

                        foreach ($alunos as &$aluno) {
                            echo $i+1 . ' Aluno: <b>' . mb_strtoupper($aluno['nome_aluno'], 'UTF-8') . '</b>   |   Data Nascimento: <b>'.$aluno['data_nascimento']->format('d/m/Y').'</b>  |   Turma: <b>' . $turmas[$j]->getNomeTurma() . '</b><br><br>';
                            if ($j < count($turmas)) {
                                if ($i < $parada) {
                                    $aluno['nova_turma'] = $turmas[$j]->getIdTurma();
                                    $i++;
                                } else {
                                    $i = 1;
                                    if (isset($turmas[$j + 1])){
                                        echo '<br><br>';
                                        $j++;
                                    }
                                    $aluno['nova_turma'] = $turmas[$j]->getIdTurma();
                                }
                            }
                            $mapper_alunos->updateTurmaAlunos(array($aluno['turma'] => $aluno['nova_turma']), $aluno['aluno']);
                        }
                    }
                } else
                    echo 'erro, disciplina ' . $nome_disciplina . ' não foi encontrada';
            }
        }

        //svar_dump($alunos_turmas);
    }

}
