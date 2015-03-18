<?php

/**
 * Classe que representa um usuário Administrador
 * @author Projeto Incluir
 */
class Application_Model_Administrador implements Application_Model_Interface_Usuario, Zend_Acl_Role_Interface {

    /**
     *  @var int
     */
    private $id_admin;

    /**
     * @var string 
     */
    private $nome_admin;

    /**
     * @var string 
     */
    private $email_admin;

    /**
     * @var string 
     */
    private $login_admin;

    /**
     * @var string 
     */
    private $senha_admin;

    /**
     * @var string 
     */
    private $tipo_usuario;
    
    /**
     * @var string 
     */
    private $_roleId;

    public function __construct($id_admin = null, $nome_admin = null, $email_admin = null, $login_admin = null, $senha_admin = null) {
        $this->id_admin = (($id_admin == null) ? null : (int) $id_admin);
        $this->nome_admin = $nome_admin;
        $this->email_admin = $email_admin;
        $this->login_admin = $login_admin;
        $this->senha_admin = $senha_admin;
        $this->tipo_usuario = Application_Model_Interface_Usuario::administrador;
        $this->_roleId = "admin";
    }

    public function getTipoUsuario() {
        return $this->tipo_usuario;
    }

    public function setTipoUsuario($tipo) {
        $this->tipo_usuario = $tipo;
    }

    public function getNomeUsuario() {
        return $this->nome_admin;
    }

    public function getEmailUsuario() {
        return $this->email_admin;
    }

    public function getLoginAdmin() {
        return $this->login_admin;
    }

    public function getSenhaAdmin() {
        return $this->senha_admin;
    }

    public function getIdAdmin() {
        return $this->id_admin;
    }

    public function getRoleId() {
        return $this->_roleId;
    }

    public function getUserIndex() {
        return array('controller' => 'index', 'action' => 'index');
    }

}

?>
