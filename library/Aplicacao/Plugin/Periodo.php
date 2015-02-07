<?php

/**
 * Description of Periodo
 *
 * @author SOLIDARIEDADE
 */
class Aplicacao_Plugin_Periodo extends Zend_Controller_Plugin_Abstract {

    private $mapper_periodo;

    public function __construct() {
        $this->mapper_periodo = new Application_Model_Mappers_Periodo();
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();

            if ($user instanceof Application_Model_Administrador && $request->getControllerName() != 'periodo' && $request->getActionName() != 'configura-fim-periodo') {
                if ($this->mapper_periodo->verificaFimPeriodo()) {
                    $periodo_atual = $this->mapper_periodo->getPeriodoAtual();

                    if ($periodo_atual instanceof Application_Model_Periodo) { // se o período tiver chegado ao fim, mas se ainda há um período setado, quer dizer que ele não foi finalizado
                        $mapper_turma = new Application_Model_Mappers_Turma();
                        $mapper_atividades = new Application_Model_Mappers_Atividade();
                        $mapper_calendario = new Application_Model_Mappers_DatasAtividade();
                        $mapper_frequencia = new Application_Model_Mappers_Frequencia();
                        $mapper_notas = new Application_Model_Mappers_Nota();
                        $mapper_alunos = new Application_Model_Mappers_Aluno();

                        if (!$mapper_alunos->finalizaAlunos($mapper_turma->getQuantidadeAlunosByPeriodo($periodo_atual->getIdPeriodo()), $mapper_calendario->getDatasByPeriodo($periodo_atual), $mapper_frequencia->getDatasLancamentosByPeriodo($periodo_atual), $mapper_atividades->getTurmaAtividadesID(), $mapper_notas->getNotasAlunos())) {
                            $request->setControllerName('periodo');
                            $request->setActionName('configura-fim-periodo');
                        }
                        
                        else
                            $this->mapper_periodo->finalizaPeriodoReserva(); // se não conseguir finaliza o período, redireciona o usuário para ele configurar o adiamento do fim do período.
                    }
                }
            }
        }
    }

}
