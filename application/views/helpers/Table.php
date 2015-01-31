<?php

class Zend_View_Helper_Table extends Zend_View_Helper_Abstract {

    public static $pre_requisito = 1;
    public static $disciplina = 2;
    public static $professor = 3;
    public static $turma = 4;
    public static $alimento = 5;
    public static $pagamento = 6;
    public $filtro_string;

    public function table($values, $type, $is_excluir = null, $opcoes_aluno = null, $opcoes_aluno_turma = null) {
        $this->filtro_string = new Aplicacao_Filtros_StringFilter();

        if (!empty($values) && is_array($values)) {
            $table = '';
            $valido = true;
            $opcao_excluir = ((empty($is_excluir)) ? '<td><div class="excluir_geral" >Excluir</div></td>' : '<td>-</td>');

            switch ($type) {
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
                    $table .= '<table id="opcoes_escolhidas_pagamentos" class="escondido"><tr><th>Disciplina - Turma</th><th>Total Pago(R$)</th><th>Total de Alimentos(kg)</th><th>Situação</th><th>Opções</th></tr>';
                    
                    $mapper_periodo = new Application_Model_Mappers_Periodo();
                    $periodo = $mapper_periodo->getPeriodoAtual();
                    
                    foreach ($opcoes_aluno_turma as $turma) {
                        if ($turma instanceof Application_Model_Turma) {
                            if (isset($values[$turma->getIdTurma(true)])) {
                                $valor_pago = $values[$turma->getIdTurma(true)];
                                $soma = 0.0;

                                if (isset($opcoes_aluno[$turma->getIdTurma(true)])) {
                                    foreach ($opcoes_aluno[$turma->getIdTurma(true)] as $quantidade)
                                        $soma += (float) $quantidade;
                                }

                                $situacao = (($soma >= $periodo->getQuantidadeAlimentos() && $valor_pago >= $periodo->getValorLiberacao()) ? 'Liberado' : 'Pendente');
                                $table .= '<tr class="pagamento_' . $this->removeInvalidCaracteres($this->filtro_string->filter($turma->getDisciplina()->getNomeDisciplina() . '_' . $turma->getNomeTurma())) . '"><input type="hidden" name="pagamento_turmas[' . $turma->getIdTurma(true) . ']" value="' . $valor_pago . '"/><td>' . $turma->getDisciplina()->getNomeDisciplina() . '-' . $turma->getNomeTurma() . '</td><td class="valor_pago">' . $valor_pago . '</td><td class="quant_alimento">' . $soma . '</td><td class="situacao"><input type="hidden" name="situacao_turmas[' . $turma->getIdTurma(true) . ']" value="' . $situacao . '"/>' . $situacao . '</td>' . $opcao_excluir . '</tr>';
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
                                    $table .='<tr class="' . $id_alimento . '"><input type="hidden" name="alimentos[' . $turma->getIdTurma(true) . '][' . $id_alimento . ']" value="' . $quantidade . '"/><td>' . $opcoes_aluno[$id_alimento]->getNomeAlimento() . '</td><td class="quantidade_alimento_turma">' . $quantidade . '</td>' . $opcao_excluir . '</tr>';

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
                            $aux = ((isset($opcoes_aluno[$turma->getIdTurma(true)])) ? $opcoes_aluno[$turma->getIdTurma(true)] : '');
                            $table .= '<tr class="' . $this->removeInvalidCaracteres($this->filtro_string->filter($turma->getDisciplina()->getNomeDisciplina() . '_' . $turma->getNomeTurma())) . '"><input type="hidden" name="turmas[]" value="' . $turma->getIdTurma(true) . '"/><td>' . $turma->getDisciplina()->getCurso()->getNomeCurso() . '</td><td>' . $turma->getDisciplina()->getNomeDisciplina() . '</td><td>' . $turma->getNomeTurma() . '</td><td><input type="hidden" name="liberacao[' . $turma->getIdTurma(true) . ']" value="' . $aux . '"/>' . $aux . '</td>' . $opcao_excluir;
                        } 
                        else {
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
