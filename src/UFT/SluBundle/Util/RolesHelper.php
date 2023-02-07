<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 08/06/16
 * Time: 11:20
 */

namespace UFT\SluBundle\Util;


class RolesHelper
{
    private $rolesHierarchy;
    private $security;

    public function __construct($rolesHierarchy,$security)
    {
        $this->rolesHierarchy = $rolesHierarchy;
        $this->security = $security;
    }

    /**
     * Return roles.
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = array();
        $rol = $this->rolesHierarchy;
        $rolesUser = $this->security->getToken()->getUser()->getRoles();
        foreach ($rol as $key => $value) {
            $roles['PRINCIPAL_'.$key] = $key;

            foreach ($value as $value2) {
                $roles[$value2] = $value2;
            }
            unset($rol['ROLE_SUPER_ADMIN']);
        }

        return array_unique($roles);
    }

    /**
     * Return roles.
     *
     * @return array
     */
    public function getGroupRoles()
    {
        $roles = array();
        $rol = $this->rolesHierarchy;
        unset($rol['ROLE_SUPER_ADMIN']);
        foreach ($rol as $key => $value) {
            $roles['PRINCIPAL_'.$key] = $key;

            foreach ($value as $value2) {
                $roles[$value2] = $value2;
            }
        }

        return array_unique($roles);
    }
}