<?php

namespace UFT\UserBundle\Repository;

/**
 * PedidoAnaliseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsuarioImpersonateRepository extends \Doctrine\ORM\EntityRepository
{
    public function findUsuarioImpersonateAtivo($uid)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('UserBundle:UsuarioImpersonate', 'u')
            ->where('u.uid =:uid')
            ->andWhere('u.flag = 0 ')
            ->setParameter('uid', $uid)
            ->getQuery()->getOneOrNullResult();
    }

}