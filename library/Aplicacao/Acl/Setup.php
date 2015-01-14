<?php

/**
 * Classe para controlar os tipos de usuário e seus privilégios
 * @author Pablo Augusto
 */
class Aplicacao_Acl_Setup {

    protected $_acl;

    public function __construct() {
        $this->_acl = new Zend_Acl();
        $this->_initialize();
    }

    protected function _initialize() {
        $this->_setupRoles();
        $this->_setupResources();
        $this->_setupPrivileges();
        $this->_saveAcl();
    }

    /**
     * Especifica os tipos de usuário
     */
    protected function _setupRoles() {
        $this->_acl->addRole(new Zend_Acl_Role('nao_logado'));
        $this->_acl->addRole(new Zend_Acl_Role('admin'), 'nao_logado');
    }

    /**
     * Especifica os recursos do sistema
     */
    protected function _setupResources() {
        $this->_acl->addResource(new Zend_Acl_Resource('login'));
        $this->_acl->addResource(new Zend_Acl_Resource('error'));
        $this->_acl->addResource(new Zend_Acl_Resource('aluno'));
        $this->_acl->addResource(new Zend_Acl_Resource('disciplina'));
        $this->_acl->addResource(new Zend_Acl_Resource('curso'));
        $this->_acl->addResource(new Zend_Acl_Resource('turma'));
        $this->_acl->addResource(new Zend_Acl_Resource('voluntario'));
        $this->_acl->addResource(new Zend_Acl_Resource('ajax'));
        $this->_acl->addResource(new Zend_Acl_Resource('index'));
        $this->_acl->addResource(new Zend_Acl_Resource('periodo'));
        $this->_acl->addResource(new Zend_Acl_Resource('relatorio'));
        $this->_acl->addResource(new Zend_Acl_Resource('datas-atividades'));
        $this->_acl->addResource(new Zend_Acl_Resource('alimento'));
        $this->_acl->addResource(new Zend_Acl_Resource('frequencia'));
        $this->_acl->addResource(new Zend_Acl_Resource('atividade'));
        $this->_acl->addResource(new Zend_Acl_Resource('nota'));
        $this->_acl->addResource(new Zend_Acl_Resource('distribuicao-alunos-turmas'));
        
        
    }

    /**
     * Especifica os privilégios dos usuários
     */
    protected function _setupPrivileges() {
        $this->_acl->allow('nao_logado', 'login', array('index'))
                ->allow('nao_logado', 'error');

        $this->_acl->allow('admin', 'index')
                ->allow('admin', 'login', array('logout'))
                ->allow('admin', 'curso')
                ->allow('admin', 'disciplina')
                ->allow('admin', 'voluntario')
                ->allow('admin', 'aluno')
                ->allow('admin', 'ajax')
                ->allow('admin', 'relatorio')
                ->allow('admin', 'periodo')
                ->allow('admin', 'turma')
                ->allow('admin', 'alimento')
                ->allow('admin', 'datas-atividades')
                ->allow('admin', 'atividade')
                ->allow('admin', 'distribuicao-alunos-turmas')
                ->allow('admin', 'frequencia')
                ->allow('admin', 'nota');
    }

    protected function _saveAcl() {
        $registry = Zend_Registry::getInstance();
        $registry->set('acl', $this->_acl);
    }

}

?>
