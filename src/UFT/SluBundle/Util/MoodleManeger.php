<?php
/**
 * Created by PhpStorm.
 * User: rafaelmansilha
 * Date: 22/11/16
 * Time: 10:45
 */

namespace UFT\SluBundle\Util;


use Doctrine\ORM\EntityManager;

class MoodleManeger
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
        if ($this->emailManager === true) {
            $connection = $this->em->getConnection();
            $statement = $connection->prepare("SELECT username, email FROM moodle_19.mdl_user WHERE username like :uid");
            $statement->bindValue('uid', $uidAntiga);
            $statement->execute();
            $result = $statement->fetchAll();
            if (count($result) == 1) {
                $statement = $connection->prepare("UPDATE moodle_19.mdl_user SET username = :uidNova, email = :novoEmail WHERE username like :uidAntiga");
                $statement->bindValue('uidNova', $uidNova);
                $statement->bindValue('novoEmail', $uidNova . "@uft.edu.br");
                $statement->bindValue('uidAntiga', $uidAntiga);
                $statement->execute();
            }
        }
    }
}