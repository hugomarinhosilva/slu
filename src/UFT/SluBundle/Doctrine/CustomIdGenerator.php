<?php

namespace UFT\SluBundle\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class CustomIdGenerator extends AbstractIdGenerator {

    public function generate(EntityManager $em, $entity) {
        $tableClass = $em->getClassMetadata(get_class($entity));
        $tableName = str_replace('DBSM.', "", $tableClass->getTableName());
        $nextIdsql = "SELECT NEXTVAL FOR DBSM.SEQ_$tableName AS VAL FROM SYSIBM.SYSDUMMY1";
        $connection = $em->getConnection();
        $stmt = $connection->prepare($nextIdsql);
        $stmt->execute();
        $nextId = $stmt->fetchColumn();
        return $nextId;
    }

}
