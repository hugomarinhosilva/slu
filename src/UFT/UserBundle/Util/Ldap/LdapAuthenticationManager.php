<?php

namespace UFT\UserBundle\Util\Ldap;

use FR3D\LdapBundle\Driver\LdapDriverInterface;
use FR3D\LdapBundle\Ldap\LdapManager as BaseLdapManager;
use Symfony\Component\Security\Core\User\UserInterface;
use UFT\LdapOrmBundle\Ldap\LdapEntityManager;
use UFT\SluBundle\Entity\DepartamentoLdap;
use UFT\SluBundle\Entity\PessoaLdap;

class LdapAuthenticationManager extends BaseLdapManager

{
    private $emLdap;

    public function __construct(LdapDriverInterface $driver, $userManager, array $params,LdapEntityManager $emLdap)
    {
        $this->driver = $driver;
        $this->userManager = $userManager;
        $this->emLdap = $emLdap;
        $this->params = $params;

        foreach ($this->params['attributes'] as $attr) {
            $this->ldapAttributes[] = $attr['ldap_attr'];
        }

        $this->ldapUsernameAttr = $this->ldapAttributes[0];

    }

    public function bind(UserInterface $user, $password)
    {
        $bind = $this->driver->bind($user, $password);

        if($user->getDepartmentNumber() == NULL || $user->getInstitucional() == NULL){
            $newUser = $this->emLdap->getRepository(PessoaLdap::class)->findOneByUid($user);
            if($newUser != false){
                if($user->getDepartmentNumber() == NULL){
                    $departamento = (is_array($newUser->getDepartmentNumber()))?$newUser->getDepartmentNumber()[0]:$newUser->getDepartmentNumber();
                    $user->setDepartmentNumber($departamento);
                    $this->userManager->updateUser($user);
                }
                if($newUser->getFuncionario() == NULL && $newUser->getProfessor() == NULL && $newUser->getAluno() == NULL ){
                    $newUser = $this->emLdap->getRepository(DepartamentoLdap::class)->findOneByUid($user);
                    if($user->getInstitucional() == NULL && $newUser->getInstitucional()==1){
                        $user->setInstitucional($newUser->getInstitucional());
                    }
                }
            }

        }

        return $bind;
    }

}