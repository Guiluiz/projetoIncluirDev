<?php

class AjaxController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction() {
        // action body
    }

    public function cepAction() {
        if ($this->_request->isPost()) {
            $dados = $this->getRequest()->getPost();
            try {
                if (!empty($dados['cep'])) {
                    $url = "http://cep.republicavirtual.com.br/web_cep.php?formato=xml&cep=" . $dados['cep'];
                    $ch = @curl_init();
                    if ($ch !== false) {
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $reg = simplexml_load_string(curl_exec($ch));

                        //var_dump($reg);
                        if ($reg instanceof SimpleXMLElement) {
                            $dados = array();

                            $dados['sucesso'] = (string) $reg->resultado;
                            $dados['rua'] = (string) $reg->tipo_logradouro . ' ' . $reg->logradouro;
                            $dados['bairro'] = (string) $reg->bairro;
                            $dados['cidade'] = (string) $reg->cidade;
                            $dados['estado'] = (string) $reg->uf;

                            echo json_encode($dados);
                            return;
                        }
                    }
                }
                echo json_encode('Desculpe, houve problemas ao buscar o CEP, verifique se ele é válido.');
            } catch (Exception $e) {
                echo json_encode('Desculpe, houve problemas ao buscar o CEP, verifique se ele é válido.');
            }
        }
    }

    public function ajaxRelatorioAction() {
        echo json_encode('Relatório Finalizado');
    }

}
