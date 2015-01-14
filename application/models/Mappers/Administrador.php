<?php

/**
 * Classe para gerenciar o administrador no banco de dados
 * @author Pablo Augusto
 */
class Application_Model_Mappers_Administrador {

    /**
     * Valida o login do administrador
     * @param string $login
     * @param string $senha
     * @return boolean true se os dados são válidos e false caso contrário
     */
    public function loginAdmin($login, $senha) {
        try {

            $dbAdapter = Zend_Db_Table::getDefaultAdapter();

            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
            $authAdapter->setTableName('administrador')
                    ->setIdentityColumn('login_admin')
                    ->setCredentialColumn('senha_admin')
                    ->setCredentialTreatment('MD5(?)');

            $authAdapter->setIdentity($login)
                    ->setCredential($senha);

            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($authAdapter);

            if ($result->isValid()) {
                $info = $authAdapter->getResultRowObject(null, 'senha_admin');

                $admin = new Application_Model_Administrador(
                                $info->id_admin,
                                $info->nome_admin,
                                $info->email_admin
                );

                $storage = $auth->getStorage();
                $storage->write($admin);

                $session = new Zend_Session_Namespace('Zend_Auth');
                $session->setExpirationSeconds(60*60*5);

                return true;
            }

            return false;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

}

?>
