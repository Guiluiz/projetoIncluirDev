<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author SOLIDARIEDADE
 */
class Zend_View_Helper_Data extends Zend_View_Helper_Abstract {

    public static $data_inicial = 0;
    public static $data_final = 1;
    public static $data_atual = 2;
    public static $data_atual_ingles = 5;
    public static $quantidade_min_alimento = 3;
    public static $valor_min = 4;

    public function data($tipo_data) {
        $mapper_periodo = new Application_Model_Mappers_Periodo();

        if (!$mapper_periodo->verificaFimPeriodo()) {
            $periodo = $mapper_periodo->getPeriodoAtual();

            switch ($tipo_data) {
                case Zend_View_Helper_Data::$data_inicial:
                    return $periodo->getDataInicio()->format('d/m/Y');

                case Zend_View_Helper_Data::$data_final:
                    return $periodo->getDataTermino()->format('d/m/Y');

                case Zend_View_Helper_Data::$data_atual:
                    $date = new DateTime();
                    return $date->format('d/m/Y');

                case Zend_View_Helper_Data::$quantidade_min_alimento:
                    return $periodo->getQuantidadeAlimentos();

                case Zend_View_Helper_Data::$valor_min:
                    return $periodo->getValorLiberacao();

                case Zend_View_Helper_Data::$data_atual_ingles:
                    $date = new DateTime();
                    return $date->format('Y-m-d');
            }
        }
        return null;
    }

}
