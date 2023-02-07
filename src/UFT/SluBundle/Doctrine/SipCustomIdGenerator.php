<?php

namespace UFT\SluBundle\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class SipCustomIdGenerator extends AbstractIdGenerator {

    public function generate(EntityManager $em, $entity) {
        $tableClass = $em->getClassMetadata(get_class($entity));
        $tableName = $tableClass->getTableName();

        $nextIdsql = "SELECT num_atual FROM infra_sequencia where nome_tabela = '$tableName'";
        $connection = $em->getConnection();
        $stmt = $connection->prepare($nextIdsql);
        $stmt->execute();
        $nextId = $stmt->fetchColumn() + 1;
        $updateIdsql = "UPDATE  infra_sequencia SET num_atual = $nextId where nome_tabela = '$tableName'";
        $stmt2 = $connection->prepare($updateIdsql);
        $stmt2->execute();
        return $nextId;
    }

}
