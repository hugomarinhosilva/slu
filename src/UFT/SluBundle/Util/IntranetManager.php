<?php
/**
 * Created by PhpStorm.
 * User: rafaelmansilha
 * Date: 07/11/17
 * Time: 09:50
 */

namespace UFT\SluBundle\Util;


use Doctrine\ORM\EntityManager;

class IntranetManager
{
    /* @var $em EntityManager*/
    private $em;
    /* @var $emailManager Boolean*/
    private $emailManager;
    public function __construct($entityManager, $emailManager)
    {
        $this->em = $entityManager;
        $this->emailManager = $emailManager;
    }

    public function renomearUsuario($uidNova, $uidAntiga){
        if ($this->emailManager === true){
            $connection = $this->em->getConnection();
            $emailNovo = $uidNova . '@uft.edu.br';
            $emailAntigo = $uidAntiga . '@uft.edu.br';

            /* aval_users (username) */
            $statement = $connection->prepare("UPDATE prod_intranet.aval_users SET username = :uidNova WHERE username like :uidAntiga");
            $statement->bindValue('uidNova', $uidNova);
            $statement->bindValue('uidAntiga', $uidAntiga);
            $statement->execute();

            /* certificado_users (login) */
            $statement = $connection->prepare("UPDATE prod_intranet.certificado_users SET login = :uidNova WHERE login LIKE :uidAntiga");
            $statement->bindValue('uidNova', $uidNova);
            $statement->bindValue('uidAntiga', $uidAntiga);
            $statement->execute();

            /* jos_jfusion_users (username) */
            $statement = $connection->prepare("UPDATE prod_intranet.jos_jfusion_users SET username = :uidNova WHERE username LIKE :uidAntiga");
            $statement->bindValue('uidNova', $uidNova);
            $statement->bindValue('uidAntiga', $uidAntiga);
            $statement->execute();

            /* jos_jfusion_users_plugin (username e userid) */
            $statement = $connection->prepare("UPDATE prod_intranet.jos_jfusion_users_plugin SET username = :uidNova, userid = :uidNova WHERE username LIKE :uidAntiga AND userid LIKE :uidAntiga");
            $statement->bindValue('uidNova', $uidNova);
            $statement->bindValue('uidAntiga', $uidAntiga);
            $statement->execute();

            /* jos_users (username e email)*/
            $statement = $connection->prepare("UPDATE prod_intranet.jos_users SET username = :uidNova, email = :emailNovo WHERE username LIKE :uidAntiga AND email LIKE :emailAntigo");
            $statement->bindValue('uidNova', $uidNova);
            $statement->bindValue('emailNovo', $emailNovo);
            $statement->bindValue('emailAntigo', $emailAntigo);
            $statement->bindValue('uidAntiga', $uidAntiga);
            $statement->execute();
            $connection->close();
        }
    }
}