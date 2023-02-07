<?php

namespace UFT\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\User as BaseUser;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Component\Config\Definition\Exception\Exception;


/**
 * @ORM\Entity()
 * @ORM\Table(name="slu_usuario")
 */
class Usuario extends BaseUser implements LdapUserInterface, \Serializable
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string",name="department_number", nullable=true)
     */
    protected $departmentNumber;

    /**
     * Ldap Object Distinguished Name
     * @var string $dn
     */
    private $dn;


    /**
     * @ORM\ManyToMany(targetEntity="UFT\UserBundle\Entity\Grupo",inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(name="slu_usuario_grupo",
     *      joinColumns={@ORM\JoinColumn(name="usuario_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="grupo_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @ORM\Column(type="integer",name="institucional", nullable=false, options={"default" = 0})
     */
    protected $institucional = 0;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="UFT\UserBundle\Entity\Role",inversedBy="users",  cascade={"persist"})
     * @ORM\JoinTable(name="slu_usuarios_roles")
     */
    protected $roles;

    /** @ORM\ManyToMany(targetEntity="UFT\UserBundle\Entity\Grupo", mappedBy="chefes", cascade={"persist", "merge"}) */
    private $meusGrupos;

    public function __construct()
    {
        // Mantener esta línea para llamar al constructor
        // de la clase padre
        parent::__construct();
        $this->groups = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function serialize()
    {
        return \json_encode(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = json_decode($serialized);
    }

    /**
     * {@inheritDoc}
     */
    public function setDn($dn)
    {
        $this->dn = $dn;
    }

    /**
     * {@inheritDoc}
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * Returns an ARRAY of Role objects with the default Role object appended.
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles->toArray();

        foreach ($this->getGroups() as $group) {
            $rolesGroup = $group->getRoles();

            $rolesGroupAux = array();
            foreach ($rolesGroup as $role) {
                $rolesAux = new Role($role->getRole());
                $rolesAux->setId($role->getId());
                $rolesAux->setNivel($role->getNivel());
                $rolesGroupAux[] = $rolesAux;
            }
            $roles = array_merge($roles, $rolesGroupAux);
        }

        if(!empty($this->getMeusGrupos())){
            foreach ($this->getMeusGrupos() as $group) {
                $rolesGroup = $group->getRoles();

                $rolesGroupAux = array();
                foreach ($rolesGroup as $role) {
                    $rolesAux = new Role($role->getRole());
                    $rolesAux->setId($role->getId());
                    $rolesAux->setNivel($role->getNivel());
                    $rolesGroupAux[] = $rolesAux;
                }
                $roles = array_merge($roles, $rolesGroupAux);
            }
        }

        if ($this->getMeusGrupos() != null && $this->getMeusGrupos()->count() > 0) {
            $roles = array_merge($roles, array(new Role('ROLE_GERENTE_GRUPO')));
        }
        if ($this->getInstitucional() == 1) {
            return array(new Role('ROLE_INSTITUCIONAL'));
        }
        return array_merge($roles, array(new Role(parent::ROLE_DEFAULT)));
    }

    /**
     * Returns the true ArrayCollection of Roles.
     */
    public function getRolesCollection()
    {
        return $this->roles;
    }

    /**
     * Pass a string, get the desired Role object or null.
     * @param string $role
     * @return Role|null
     */
    public function getRole($role)
    {
        foreach ($this->getRoles() as $roleItem) {
            if ($role == $roleItem->getRole()) {
                return $roleItem;
            }
        }
        return null;
    }

    /**
     * Pass a string, checks if we have that Role. Same functionality as getRole() except returns a real boolean.
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        if ($this->getRole($role)) {
            return true;
        }
        return false;
    }

    /**
     * Adds a Role OBJECT to the ArrayCollection. Can't type hint due to interface so throws Exception.
     * @throws Exception
     * @param Role $role
     */
    public function addRole($role)
    {
        if (!$role instanceof Role) {
            throw new \Exception("addRole takes a Role object as the parameter");
        }

        if (!$this->hasRole($role->getRole())) {
            $this->roles->add($role);
        }
    }

    /**
     * Pass a string, remove the Role object from collection.
     * @param string $role
     */
    public function removeRole($role)
    {
        $roleElement = $this->getRole($role);
        if ($roleElement) {
            $this->roles->removeElement($roleElement);
        }
    }

    /**
     * Pass an ARRAY of Role objects and will clear the collection and re-set it with new Roles.
     * Type hinted array due to interface.
     * @param array $roles Of Role objects.
     */
    public function setRoles(array $roles)
    {
        $this->roles->clear();
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    /**
     * Directly set the ArrayCollection of Roles. Type hinted as Collection which is the parent of (Array|Persistent)Collection.
     *
     */
    public function setRolesCollection(ArrayCollection $collection)
    {
        $this->roles = $collection;
    }


    function getId()
    {
        return $this->id;
    }


    function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInstitucional()
    {
        return $this->institucional;
    }

    /**
     * @param mixed $institucional
     */
    public function setInstitucional($institucional)
    {
        $this->institucional = $institucional;
    }


    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * Pass a string, get the desired Role object or null.
     * @param string $role
     * @return Role|null
     */
    public function getGroup($group)
    {
        foreach ($this->getGroups() as $groupItem) {
            if ($group == $groupItem->getId()) {
                return $group;
            }
        }
        return null;
    }

    /**
     * Pass a string, checks if we have that Role. Same functionality as getGroup() except returns a real boolean.
     * @param string $role
     * @return boolean
     */
    public function hasGroup($group)
    {
        if ($this->getGroup($group)) {
            return true;
        }
        return false;
    }

    /**
     * Add a member to the group
     *
     * @param Usuario $member the mmeber to add
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->hasGroup($group->getId())) {
            $this->groups->add($group);
        }
        return $this;
    }

    /**
     * Remove a user to the group
     *
     * @param Usuario $member the mmeber to remove
     */
    public function removeGroup(GroupInterface $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * @return mixed
     */
    public function getMeusGrupos()
    {
        return $this->meusGrupos;
    }

    /**
     * @param mixed $groups
     */
    public function setMeusGrupos($groups)
    {
        $this->meusGrupos = $groups;
    }

    /**
     * Pass a string, get the desired Role object or null.
     * @param string $role
     * @return Role|null
     */
    public function getMeuGrupo($group)
    {
        foreach ($this->getMeusGrupos() as $groupItem) {
            if ($group == $groupItem->getId()) {
                return $group;
            }
        }
        return null;
    }

    /**
     * Pass a string, checks if we have that Role. Same functionality as getGroup() except returns a real boolean.
     * @param string $role
     * @return boolean
     */
    public function hasMeusGrupos($group)
    {
        if ($this->getMeuGrupo($group)) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getDepartmentNumber()
    {
        return $this->departmentNumber;
    }

    /**
     * @param mixed $departmentNumber
     */
    public function setDepartmentNumber($departmentNumber)
    {
        $this->departmentNumber = $departmentNumber;
    }

}
