<?php

namespace UFT\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Role Entity
 *
 * @ORM\Entity
 * @ORM\Table( name="slu_role" )
 */
class Role implements RoleInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="role", unique=true, length=50)
     */
    private $role;

    /**
     * @ORM\Column(type="string", name="descricao",length=100)
     */
    private $roleIdentifier;

    /**
     * @ORM\Column(type="integer", name="principal")
     */
    private $principal;
    
    /**
     * @ORM\Column(type="integer", name="nivel")
     */
    private $nivel;



    /**
     * @ORM\OneToMany(targetEntity="UFT\UserBundle\Entity\Role", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="UFT\UserBundle\Entity\Role", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\ManyToMany(targetEntity="UFT\UserBundle\Entity\Usuario", mappedBy="roles")
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="UFT\UserBundle\Entity\Grupo", mappedBy="roles")
     */
    private $grupos;

    /**
     * Populate the role field
     * @param string $role ROLE_FOO etc
     */
    public function __construct( $role = 0 )
    {
        if (0 !== strlen($role)) {
            $this->role = strtoupper($role);
        }
        $this->children = new ArrayCollection();

    }
    public function serialize()
    {
        return \json_encode(array(
            $this->id,
            $this->role
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->role
            ) = \json_decode($serialized);
    }


    /**
     * Return the role field.
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
    /**
     * @return mixed
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * @param mixed $nivel
     */
    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set roleName
     *
     * @param string $roleName
     */
    public function setRole($roleName)
    {
        $this->role = $roleName;

        return $this;
    }

    /**
     * Set roleIdentifier
     *
     * @param string $roleIdentifier
     */
    public function setRoleIdentifier($roleIdentifier)
    {
        $this->roleIdentifier = $roleIdentifier;

        return $this;
    }



    /**
     * Get roleIdentifier
     *
     * @return string
     */
    public function getRoleIdentifier()
    {
        return $this->roleIdentifier;
    }

    /**
     * @return mixed
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    /**
     * @param mixed $principal
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function addUser($user, $addRoleToUser = true)
    {
        $this->users->add($user);
        $addRoleToUser && $user->addRole($this, false);
    }

    public function removeUser($user)
    {
        $this->users->removeElement($user);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChildren(Role $child, $setParentToChild = true)
    {
        $this->children->add($child);
        $setParentToChild && $child->setParent($this, false);
    }

    public function getDescendant(& $descendants = array())
    {
        foreach ($this->children as $role) {
            $descendants[spl_object_hash($role)] = $role;
            $role->getDescendant($descendants);
        }
        return $descendants;
    }

    public function removeChildren(Role $children)
    {
        $this->children->removeElement($children);
    }

    public function getGrupos()
    {
        return $this->grupos;
    }

    public function addGrupo($grupo, $addRoleToGrupo = true)
    {
        $this->grupos->add($grupo);
        $addRoleToGrupo && $grupo->addRole($this, false);
    }

    public function removeGrupo($grupo)
    {
        $this->grupos->removeElement($grupo);
    }

    /**
     * Return the role field.
     * @return string
     */
//    public function __toString()
//    {
//        return (string) $this->role;
//    }


    public function __toString()
    {
        if ($this->children->count()) {
            $childNameList = array();
            foreach ($this->children as $child) {
                $childNameList[] = $child->getRole();
            }
            return sprintf('%s [%s]', $this->role, implode(', ', $childNameList));
        }
        return sprintf('%s', $this->role);
    }




}