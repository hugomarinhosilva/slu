<?php

namespace UFT\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\Group as BaseGroup;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="slu_grupo")
 */
class Grupo extends BaseGroup implements \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Owning Side
     *
     * @ORM\ManyToMany(targetEntity="FiltroUnidade", inversedBy="grupos")
     * @ORM\JoinTable(name="slu_filtros_grupos",
     *      joinColumns={@ORM\JoinColumn(name="grupo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="unidade_cod", referencedColumnName="cod_estruturado")}
     *      )
     */
    private $filtros;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="UFT\UserBundle\Entity\Role",inversedBy="grupos")
     * @ORM\JoinTable(name="slu_grupos_roles")
     */
    protected $roles;

    /** @ORM\ManyToMany(targetEntity="UFT\UserBundle\Entity\Usuario", mappedBy="groups", cascade={"persist", "merge"}) */
    private $users;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="UFT\UserBundle\Entity\Usuario",inversedBy="meusGrupos")
     * @ORM\JoinTable(name="slu_grupos_chefe")
     */
    protected $chefes;
    
    public function __construct()
    {
        parent::__construct($name = '');
        $this->users = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->chefes = new ArrayCollection();
    }

    public function serialize()
    {
        return \json_encode(array(
            $this->id,
            $this->name
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->name
            ) = \json_decode($serialized);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */

    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    public function hasUser($user)
    {
        if ($this->getuser($user)) {
            return true;
        }
        return false;
    }

    public function getUser($user)
    {
        foreach ($this->getUsers() as $userItem) {
            if ($user == $userItem) {
                return $userItem;
            }
        }
        return null;
    }



    /**
     * Add a member to the group
     *
     * @param Usuario $member the mmeber to add
     */
    public function addUser($user)
    {
        if (!$user instanceof Usuario) {
            $this->users[] = $user;
        }else if (!$this->hasUser($user)) {
            $this->users->add($user);
        }
        return $this;
    }

    /**
     * Remove a user to the group
     *
     * @param Usuario $member the mmeber to remove
     */
    public function removeUser($user)
    {
        $this->users->removeElement($user);
    }

    public function getFiltros()
    {
        return $this->filtros;
    }

    /**
     * @param mixed $filtros
     */
    public function setFiltros($filtros)
    {
        $this->filtros = $filtros;
    }
    /**
     * Returns an ARRAY of Role objects with the default Role object appended.
     * @return array
     */
    public function getRoles()
    {
        return  $this->roles ;
    }

    /**
     * Returns the true ArrayCollection of Roles.
     */
    public function getRolesArray()
    {
        return $this->roles->toArray();
    }

    /**
     * Pass a string, get the desired Role object or null.
     * @param string $role
     * @return Role|null
     */
    public function getRole( $role )
    {
        foreach ( $this->getRoles() as $roleItem )
        {
            if ( $role == $roleItem->getRole() )
            {
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
    public function hasRole( $role )
    {
        if ( $this->getRole( $role ) )
        {
            return true;
        }
        return false;
    }
    /**
     * Adds a Role OBJECT to the ArrayCollection. Can't type hint due to interface so throws Exception.
     * @throws Exception
     * @param Role $role
     */
    public function addRole( $role )
    {
        if ( !$role instanceof Role )
        {
            throw new \Exception( "addRole takes a Role object as the parameter" );
        }

        if ( !$this->hasRole( $role->getRole() ) )
        {
            $this->roles->add( $role );
        }
    }

    /**
     * Pass a string, remove the Role object from collection.
     * @param string $role
     */
    public function removeRole( $role )
    {
        $roleElement = $this->getRole( $role );
        if ( $roleElement )
        {
            $this->roles->removeElement( $roleElement );
        }
    }

    /**
     * Pass an ARRAY of Role objects and will clear the collection and re-set it with new Roles.
     * Type hinted array due to interface.
     * @param array $roles Of Role objects.
     */
    public function setRoles( array $roles )
    {
        $this->roles->clear();
        foreach ( $roles as $role )
        {
            $this->addRole( $role );
        }
    }

    /**
     * Directly set the ArrayCollection of Roles. Type hinted as Collection which is the parent of (Array|Persistent)Collection.
     *
     */
    public function setRolesCollection( ArrayCollection $collection )
    {
        $this->roles = $collection;
    }

    /**
     * Returns an ARRAY of Role objects with the default Role object appended.
     * @return array
     */
    public function getChefes()
    {
        return  $this->chefes ;
    }

    /**
     * Returns the true ArrayCollection of Roles.
     */
    public function getChefesArray()
    {
        return $this->chefes->toArray();
    }

    /**
     * Pass a string, get the desired Role object or null.
     * @param string $role
     * @return Role|null
     */
    public function getChefe( $chefe )
    {
        foreach ( $this->getChefes() as $chefeItem )
        {
            if ( $chefe == $chefeItem->getId() )
            {
                return $chefeItem;
            }
        }
        return null;
    }

    /**
     * Pass a string, checks if we have that Role. Same functionality as getRole() except returns a real boolean.
     * @param string $role
     * @return boolean
     */
    public function hasChefe( $chefe )
    {
        if ( $this->getChefe( $chefe ) )
        {
            return true;
        }
        return false;
    }
    /**
     * Adds a Role OBJECT to the ArrayCollection. Can't type hint due to interface so throws Exception.
     * @throws Exception
     * @param Role $role
     */
    public function addChefe( $chefe )
    {
        if ( !$chefe instanceof Usuario )
        {
            throw new \Exception( "addChefe takes a User object as the parameter" );
        }

        if ( !$this->hasChefe( $chefe->getId() ) )
        {
            $this->chefes->add( $chefe );
        }
    }

    /**
     * Pass a string, remove the Role object from collection.
     * @param string $role
     */
    public function removeChefe( $chefe )
    {
        $chefeElement = $this->getRole( $chefe );
        if ( $chefeElement )
        {
            $this->chefes->removeElement( $chefeElement );
        }
    }

    /**
     * Pass an ARRAY of Role objects and will clear the collection and re-set it with new Roles.
     * Type hinted array due to interface.
     * @param array $roles Of Role objects.
     */
    public function setChefes( array $chefes )
    {
        $this->chefes->clear();
        foreach ( $chefes as $chefe )
        {
            $this->addChefe( $chefe );
        }
    }

    /**
     * Directly set the ArrayCollection of Roles. Type hinted as Collection which is the parent of (Array|Persistent)Collection.
     *
     */
    public function setChefesCollection( ArrayCollection $collection )
    {
        $this->chefes = $collection;
    }


    function __toString()
    {
        return $this->getName();
    }
}