<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Página Inicial";
        /*
          $mapper_periodo = new Application_Model_Mappers_Periodo();
          $mapper_frequencia = new Application_Model_Mappers_Frequencia();
          $mapper_turma = new Application_Model_Mappers_Turma();
          $mapper_calendario = new Application_Model_Mappers_DatasAtividade();

          $periodo_atual = $mapper_periodo->getPeriodoAtual();

          $mapper_frequencia->setDatasLancamentos($mapper_turma->buscaTurmas(array('periodo' => $periodo_atual->getIdPeriodo())), $mapper_calendario->getDatasByPeriodo($periodo_atual));
         */
    }

}
