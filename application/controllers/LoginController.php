<?php

class LoginController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $this->view->title = "Projeto Incluir - Acesso ao Sistema";
        $form_login = new Application_Form_FormLogin();

        $this->view->form = $form_login;

        $auth = Zend_Auth::getInstance();
        $usuario = $auth->getIdentity();

        if (!empty($usuario))
            $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'index'), null, true);


        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if ($form_login->isValid($dados)) {
                $login = $form_login->getValue('login');
                $senha = $form_login->getValue('pass');

                $mapper_admin = new Application_Model_Mappers_Administrador();

                if ($mapper_admin->loginAdmin($login, $senha))
                    $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'index'), null, true);

                else
                    $this->view->mensagem = "Desculpe, não foi possível acessar o sistema com os dados informados. Se necessário entre em contato com o administrador.";
            }
        }
    }

    public function logoutAction() {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity())
            $auth->clearIdentity();

        $this->_helper->redirector->goToRoute(array('controller' => 'login', 'action' => 'index'), null, true);
    }

    /*public function adminAction() {
        // action body
    }*/

}

