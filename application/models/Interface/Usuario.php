<?php

/**
 * Interface para indicar usuários (administrador e professor)
 * @author Pablo Augusto
 */
interface Application_Model_Interface_Usuario {

    const administrador = 1;
    const professor = 2;

    public function setTipoUsuario($tipo);

    public function getTipoUsuario();

    public function getNomeUsuario();

    public function getEmailUsuario();
    
    public function getUserIndex();
    
}

?>
