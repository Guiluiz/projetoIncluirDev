<?php

class Zend_View_Helper_Table extends Zend_View_Helper_Abstract {

    public static $pre_requisito = 1;
    public static $disciplina = 2;
    public static $professor = 3;
    public static $turma = 4;
    public static $alimento = 5;
    public static $pagamento = 6;
    public $filtro_string;

    /**
     * Constrói tabela de acordo com os dados indicados
     * @param array $values 
     * 
     * Contém informações que serão exibidas: 
     * 
     * Disciplinas de Pré Requisito: Utilizado no gerenciamento de disciplinas para manutenção de pré requisitos de uma determinada disciplina
     * Disciplina: Utilizado no gerenciamento de professores/voluntários para indicação de disciplinas que o voluntário pode lecionar
     * Professor: Utilizado no gerenciamento de turmas para indicação dos professores da turma
     * Turma: Utilizado no gerenciamento de alunos para indicação das turmas do aluno
     * Alimento: Utilizado no gerenciamento de alunos para indicação dos alimentos dos pagamentos do aluno
     * Pagamento Utilizado no gerenciamento de alunos para indicação dos pagamentos registrados para as turmas do aluno
     * 
     * @param int $type Indica qual o tipo de tabela a ser construída
     * @param type $is_excluir Indica se terá uma opção de exclusão nas linhas da tabela
     * @param type $opcoes_aluno Indica valores auxiliares necessários para a construção da tabela (depende do tipo de tabela)
     * @param type $opcoes_aluno_turma Indica valores auxiliares relacionados a turma (objetos de turma) (depende do tipo de tabela)
     * @return string
     */
    public function table($values, $type, $is_excluir = null, $opcoes_aluno = null, $opcoes_aluno_turma = null) {
        $this->filtro_string = new Aplicacao_Filtros_StringFilter();

        if (!empty($values) && is_array($values)) {
            $table = '';
            $valido = true;
            $opcao_excluir = ((empty($is_excluir)) ? '<td><div class="excluir_geral" >Excluir</div></td>' : '<td>-</td>');

            switch ($type) {
                // constrói a tabela de pré requisitos da disciplina indicada
                case Zend_View_Helper_Table::$pre_requisito:
                    $table .= '<table id="opcoes_escolhidas" class="escondido"><tr><th>Disciplina(Pré-Requisito)</th><th>Opções</th></tr>';

                    foreach ($values as $pre_requisito) {
                        if ($pre_requisito instanceof Application_Model_Disciplina)
                            $table .= '<tr class="' . $pre_requisito->getIdDisciplina(true) . '"><input type="hidden" name="pre_requisitos[]" value="' . $pre_requisito->getIdDisciplina(true) . '"/><td>' . $pre_requisito->getNomeDisciplina() . '</td>' . $opcao_excluir . '</tr>';

                        else {
                            $valido = false;
                            break;
                        }
                    }
                    $table .= '</table>';
                    break;

                case Zend_View_Helper_Table::$disciplina:
                    $table .= '<table id="opcoes_escolhidas" class="escondido"><tr><th>Curso</th><th>Disciplina</th><th>Opções</th></tr>';

                    foreach ($values as $disciplina) {
                        if ($disciplina instanceof Application_Model_Disciplina)
                            $table .= '<tr class="' . $disciplina->getIdDisciplina(true) . '"><input type="hidden" name="disciplinas[]" value="' . $disciplina->getIdDisciplina(true) . '"/><td>' . $disciplina->getCurso()->getNomeCurso() . '</td><td>' . $disciplina->getNomeDisciplina() . '</td>' . $opcao_excluir . '</tr>';

                        else {
                            $valido = false;
                            break;
                        }
                    }
                    $table .= '</table>';
                    break;

                case Zend_View_Helper_Table::$professor:
                    $table .= '<table id="opcoes_escolhidas" class="escondido"><tr><th>Professor</th><th>Opções</th></tr>';

                    foreach ($values as $professor) {
                        if ($professor instanceof Application_Model_Professor)
                            $table .= '<tr class="' . $professor->getIdProfessor(true) . '"><input type="hidden" name="professores[]" value="' . $professor->getIdProfessor(true) . '"/><td>' . $professor->getNomeVoluntario() . '</td>' . $opcao_excluir;

                        else {
                            $valido = false;
                            break;
                        }
                    }
                    $table .= '</table>';
                    break;

                case Zend_View_Helper_Table::$pagamento:
                    $table .= '<table id="opcoes_escolhidas_pagamentos" class="escondido"><tr><th>Disciplina - Turma</th><th>Nº Recibo</th><th>Total Pago(R$)</th><th>Total de Alimentos(kg)</th><th>Condição</th><th>Situação</th><th>Opções</th></tr>';

                    $tipos_condicao = array(
                        '' => '',
                        Application_Model_Pagamento::$pagamento_normal => 'Pagamento Normal',
                        Application_Model_Pagamento::$pagamento_isento_parcial => 'Isenção Parcial',
                        Application_Model_Pagamento::$pagamento_isento_total => 'Isenção Total',
                        Application_Model_Pagamento::$pagamento_pendente_parcial => 'Pendente Parcial',
                        Application_Model_Pagamento::$pagamento_pendente_total => 'Pendente Total',
                    );

                    foreach ($opcoes_aluno_turma as $turma) {
                        if ($turma instanceof Application_Model_Turma) {
                            if (isset($values[$turma->getIdTurma(true)])) {
                                $valor_pago = $values[$turma->getIdTurma(true)];
                                $soma_alimentos = 0.0;
                                $situacao = $opcoes_aluno['situacao_turmas'][$turma->getIdTurma(true)];
                                $num_recibo = $opcoes_aluno['recibos_turmas'][$turma->getIdTurma(true)];

                                if (isset($opcoes_aluno['alimentos'][$turma->getIdTurma(true)])) {
                                    foreach ($opcoes_aluno['alimentos'][$turma->getIdTurma(true)] as $quantidade)
                                        $soma_alimentos += (float) $quantidade;
                                }

                                $table .= '<tr class="pagamento_' . $this->removeInvalidCaracteres($this->filtro_string->filter($turma->getDisciplina()->getNomeDisciplina() . '_' . $turma->getNomeTurma())) . '">'
                                        . '<input type="hidden" name="pagamento_turmas[' . $turma->getIdTurma(true) . ']" value="' . $valor_pago . '"/>'
                                        . '<td class="nome_turma">' . $turma->getDisciplina()->getNomeDisciplina() . ' - ' . $turma->getNomeTurma() . ' | ' . $turma->getHorarioInicio() . ' - ' . $turma->getHorarioFim() . '</td>'
                                        . '<td><input type="hidden" name="recibos_turmas[' . $turma->getIdTurma(true) . ']" value="' . $num_recibo . '"/>' . $num_recibo . '</td>'
                                        . '<td class="valor_pago">' . $valor_pago . '</td>'
                                        . '<td class="quant_alimento">' . $soma_alimentos . '</td>'
                                        . '<td class="condicao"><input type="hidden" name="condicao_turmas[' . $turma->getIdTurma(true) . ']" value="' . $opcoes_aluno['condicao'][$turma->getIdTurma(true)] . '"><input type="hidden" name="tipo_isencao_pendencia_turmas[' . $turma->getIdTurma(true) . ']" value="">' . $tipos_condicao[$opcoes_aluno['condicao'][$turma->getIdTurma(true)]] . '</td>'
                                        . '<td class="situacao"><input type="hidden" name="situacao_turmas[' . $turma->getIdTurma(true) . ']" value="' . $situacao . '"/>' . $situacao . '</td>'
                                        . '<td><div class="excluir_pagamento">Excluir</div></td></tr>';
                            }
                        } else {
                            $valido = false;
                            break;
                        }
                    }
                    $table .= '</table>';
                    break;

                case Zend_View_Helper_Table::$alimento:
                    $table = '<div id="alimentos_escolhidos">';

                    foreach ($opcoes_aluno_turma as $turma) {
                        if ($turma instanceof Application_Model_Turma) {
                            if (isset($values[$turma->getIdTurma(true)]) && $this->verificaAlimentos($values[$turma->getIdTurma(true)])) {
                                $table .= '<table class="ali_pag form_incrementa" id="alimentos_' . $this->removeInvalidCaracteres($this->filtro_string->filter($turma->getDisciplina()->getNomeDisciplina() . '_' . $turma->getNomeTurma())) . '" cellpadding="0" cellspacing="0"><tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr>';

                                foreach ($values[$turma->getIdTurma(true)] as $id_alimento => $quantidade)
                                    $table .='<tr class="' . $id_alimento . '"><input type="hidden" name="alimentos[' . $turma->getIdTurma(true) . '][' . $id_alimento . ']" value="' . $quantidade . '"/><td>' . $opcoes_aluno[$id_alimento]->getNomeAlimento() . '</td><td class="quantidade_alimento_turma">' . $quantidade . '</td><td><div class="excluir_alimento">Excluir</div></td></tr>';

                                $table .= '</table>';
                            }
                        } else {
                            $valido = false;
                            break;
                        }
                    }
                    $table.='</div>';

                    break;
                case Zend_View_Helper_Table::$turma:
                    $table .= '<table id="opcoes_escolhidas" class="escondido"><tr><th>Curso</th><th>Disciplina</th><th>Turma</th><th>Liberação de Requisitos</th><th>Opções</th></tr>';

                    foreach ($values as $turma) {
                        if ($turma instanceof Application_Model_Turma) {
                            $liberacao = ((isset($opcoes_aluno[$turma->getIdTurma(true)])) ? $opcoes_aluno[$turma->getIdTurma(true)] : '');
                            $table .= '<tr class="' . $this->removeInvalidCaracteres($this->filtro_string->filter($turma->getDisciplina()->getNomeDisciplina() . '_' . $turma->getNomeTurma())) . '"><input type="hidden" name="turmas[]" value="' . $turma->getIdTurma(true) . '"/><td>' . $turma->getDisciplina()->getCurso()->getNomeCurso() . '</td><td>' . $turma->getDisciplina()->getNomeDisciplina() . '</td><td class="turma_aluno" id="' . $turma->getIdTurma(true) . '" hora_inicio="' . $turma->getHorarioInicio() . '" hora_fim="' . $turma->getHorarioFim() . '" data_inicio="' . $turma->getDataInicio(true) . '" data_fim="' . $turma->getDataFim(true) . '">' . $turma->getNomeTurma() . ' | ' . $turma->getHorarioInicio() . ' - ' . $turma->getHorarioFim() . '</td><td><input type="hidden" name="liberacao[' . $turma->getIdTurma(true) . ']" value="' . $liberacao . '"/>' . $liberacao . '</td>' . '<td><div class="alterar_turma">Alterar</div><div class="excluir_turma">Excluir</div></td></tr>';
                        } else {
                            $valido = false;
                            break;
                        }
                    }
                    $table .= '</table>';
                    break;
                default:
                    $valido = false;
                    break;
            }

            if ($valido)
                return $table;

            return 'Houve problemas com os valores escolhidos, contate o administrador do sistema';
        }
    }

    public function removeInvalidCaracteres($texto) {
        $array1 = array(" ", " - ", "__");
        $array2 = array("_", "_", "_");

        $aux = str_replace($array1, $array2, $texto);
        //$aux = str_replace(' ', '_', $aux);

        return strtolower($aux);
    }

    public function verificaAlimentos($array_values) {
        foreach ($array_values as $key => $value) {
            if (empty($key) || empty($value))
                return false;
        }
        return true;
    }

}

?>
