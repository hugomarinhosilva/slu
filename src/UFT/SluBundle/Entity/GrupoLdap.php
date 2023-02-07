<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 29/03/16
 * Time: 10:20
 */

namespace UFT\SluBundle\Entity;

use UFT\LdapOrmBundle\Annotation\Ldap\Attribute;
use UFT\LdapOrmBundle\Annotation\Ldap\Dn;
use UFT\LdapOrmBundle\Annotation\Ldap\DnLinkArray;
use UFT\LdapOrmBundle\Annotation\Ldap\DnPregMatch;
use UFT\LdapOrmBundle\Annotation\Ldap\ObjectClass;
use UFT\LdapOrmBundle\Annotation\Ldap\SearchDn;
use UFT\LdapOrmBundle\Entity\Ldap\Group;

/**
 * Represents a ComExamplePerson object class, which is a subclass of InetOrgPerson
 *
 * @ObjectClass("GroupOfNames")
 * @SearchDn("ou=Group,dc=uft,dc=edu,dc=br")
 * @Dn("cn={{ entity.cn[0] }},ou=Group,dc=uft,dc=edu,dc=br")
 */
class GrupoLdap extends Group
{
    public function __construct($username = null, $roles = null)
    {
        parent::__construct();
        $this->setObjectClass(array('GroupOfNames', 'top'));

    }

//    /**
//     * @Attribute("gidnumber")
//     * @Sequence("cn=gidSequence,ou=sequences,ou=gram,o=uft,dc=edu,dc=br")
//     */
//    private $id;

    /**
     * @Attribute("member")
     * @DnLinkArray("UFT\SluBundle\Entity\PessoaLdap")
     */
    protected $member;

    /**
     * @DnPregMatch("/ou=([a-zA-Z0-9\.]+)/")
     */
    private $entities = array('groups');


    /**
     * Set members of the groups
     *
     * @param string $members the name of the group
     */
    public function setMembers($members)
    {
        $this->members = $members;
    }


    /**
     * Add a member to the group
     *
     * @param Account $member the mmeber to add
     */
    public function addMember($member)
    {
        if($member instanceof PessoaLdap){
            if(!in_array(trim($member), $this->member)){
                $this->member[] = $member;
            }
        } else {
            if(!in_array(trim($member->getDn()), $this->member)){
                $this->member[] = $member;
            }
        }
    }

    /**
     * Remove a member to the group
     *
     * @param Account $member the mmeber to remove
     */
    public function removeMember($member)
    {
        foreach ($this->member as $key => $memberAccount) {
//            dump($memberAccount);
//            die();
            if ($memberAccount instanceof PessoaLdap && $memberAccount->compare($member) == 0) {
                $this->member[$key] = null;
                unset($this->member[$key]);
            } elseif (!($memberAccount instanceof PessoaLdap) && in_array($memberAccount, $member) == 1) {
                $this->member[$key] = null;
            }
        }
        $members = array_filter($this->member);
        $this->member = $members;
    }

    /**
     * Return the Entities of group
     *
     * @param array $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
    }


    /**
     * Return the members of the group
     *
     * @return array of object Accounts representing the of the group
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Return the name of the group
     *
     * @return string name of the group
     */
    public function getEntities()
    {
        return $this->entities;
    }

    function __toString()
    {
        return $this->getCn()[0];
    }


}