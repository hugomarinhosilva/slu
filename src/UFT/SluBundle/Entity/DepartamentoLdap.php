<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 29/03/16
 * Time: 10:20
 */

namespace UFT\SluBundle\Entity;

use UFT\LdapOrmBundle\Annotation\Ldap\ArrayField;
use UFT\LdapOrmBundle\Annotation\Ldap\Attribute;
use UFT\LdapOrmBundle\Annotation\Ldap\Dn;
use UFT\LdapOrmBundle\Annotation\Ldap\DnLinkArray;
use UFT\LdapOrmBundle\Annotation\Ldap\ObjectClass;
use UFT\LdapOrmBundle\Annotation\Ldap\Operational;
use UFT\LdapOrmBundle\Annotation\Ldap\SearchDn;
use UFT\LdapOrmBundle\Entity\Ldap\InetOrgPerson;

/**
 * Represents a ComExamplePerson object class, which is a subclass of InetOrgPerson
 *
 * @ObjectClass("uftOrgUnit")
 * @SearchDn("ou=people,dc=uft,dc=edu,dc=br")
 * @Dn("uid={{ entity.uid }},ou=people,dc=uft,dc=edu,dc=br")
 */
class DepartamentoLdap extends InetOrgPerson
{
    public function __construct($username = null, $roles = null)
    {
        parent::__construct();
    }

    public function constroiObjetoLdap()
    {
        $this->setObjectClass($this->getNovasObjectClass());
        $this->displayName;
        $pos_espaco = strpos($this->displayName, ' ');// perceba que há um espaço aqui
        $primeiro_nome = substr($this->displayName, 0, $pos_espaco);
        if (!$pos_espaco) {
            $resto_nome = $this->displayName;

        } else {
            $resto_nome = substr($this->displayName, $pos_espaco + 1, strlen($this->displayName));
        }

        if (empty($this->mail)) {
            $this->mail = null;
        }

        if($this->teste == true){
            $this->teste = 1;
        }else{
            $this->teste = 0;
        }

        // Insere o primeiro nome e o nome completo no CN
        $this->cn = array($primeiro_nome, $this->displayName);
        //Insere o Resto do nome da pessoa (sem o primeiro nome) no campo SN
        $this->sn = array($resto_nome);
        //TODO ENCRIPTAR SENHA
        if($this->alteraSenha != 0){
            $this->userPassword = '{CRYPT}' . crypt($this->userPassword, null);
        }
        //Formatando data para o LDAP
        $this->gecos = $this->displayName;
        $this->homeDirectory = '/home/' . $this->uid;
    }

    public function getNovasObjectClass()
    {
        $objectClass[] = 'inetOrgPerson';
        $objectClass[] = 'organizationalPerson';
        $objectClass[] = 'person';
        $objectClass[] = 'top';
        $objectClass[] = 'uftOrgUnit';
        $objectClass[] = 'posixAccount';
        return $objectClass;
    }

    /**
     * @Attribute("memberOf")
     * @DnLinkArray("UFT\SluBundle\Entity\GrupoLdap")
     *
     */
    protected $memberOf;


    /**
     * @Attribute("manager")
     * @DnLinkArray("UFT\SluBundle\Entity\PessoaLdap")
     *
     */
    protected $manager;


    /**
     *
     */
    protected $teste;


    /**
     * @Attribute("Campus")
     * @ArrayField()
     */
    protected $campus;


    /**
     * @ArrayField()
     */
    protected $grupo;

    /**
     * @Attribute("uidNumber")
     */
    protected $uidNumber = 0;

    /**
     * @Attribute("gidNumber")
     */
    protected $gidNumber = 0;

    /**
     * @Attribute("homeDirectory")
     */
    protected $homeDirectory;

    /**
     * @Attribute("gecos")
     */
    protected $gecos;

    /**
     * @Attribute("createTimestamp")
     * @Operational()
     */
    protected $createTimestamp;

    /**
     * @Attribute("modifyTimestamp")
     * @Operational()
     */
    protected $modifyTimestamp;

    /**
     * @Attribute("Institucional")
     */
    protected $institucional = 1;

    /**
     *
     */
    protected $alteraSenha;

    /**
     * @return mixed
     */
    public function getAlteraSenha()
    {
        return $this->alteraSenha;
    }

    /**
     * @param mixed $alteraSenha
     */
    public function setAlteraSenha($alteraSenha)
    {
        $this->alteraSenha = $alteraSenha;
    }

    /**
     * @param mixed $mail
     * @return mixed
     */
    public function addMail($mail)
    {
        $this->mail[] = $mail;
        return $this;
    }

    /**
     * @param mixed $mail
     */
    public function removeMail($mail)
    {
        $this->mail->removeElement($mail);
    }


    /**
     * @return mixed
     */
    public function getCampus()
    {
        return $this->campus;
    }

    /**
     * @param mixed $campus
     */
    public function setCampus($campus)
    {
        $this->campus = $campus;
    }

    /**
     * @return mixed
     */
    public function getGecos()
    {
        return $this->gecos;
    }

    /**
     * @param mixed $gecos
     */
    public function setGecos($gecos)
    {
        $this->gecos = $gecos;
    }

    /**
     * @return mixed
     */
    public function getUidNumber()
    {
        return $this->uidNumber;
    }

    /**
     * @param mixed $uidNumber
     */
    public function setUidNumber($uidNumber)
    {
        $this->uidNumber = $uidNumber;
    }

    /**
     * @return mixed
     */
    public function getGidNumber()
    {
        return $this->gidNumber;
    }

    /**
     * @param mixed $gidNumber
     */
    public function setGidNumber($gidNumber)
    {
        $this->gidNumber = $gidNumber;
    }

    /**
     * @return mixed
     */
    public function getHomeDirectory()
    {
        return $this->homeDirectory;
    }

    /**
     * @param mixed $homeDirectory
     */
    public function setHomeDirectory($homeDirectory)
    {
        $this->homeDirectory = $homeDirectory;
    }

    /**
     * @return mixed
     */
    public function getMemberOf()
    {
        return $this->memberOf;
    }

    /**
     * @param mixed $memberOf
     */
    public function setMemberOf($memberOf)
    {
        $this->memberOf = $memberOf;
    }

    public function addMemberOf($member)
    {
        $this->memberOf[] = $member;
    }

    /**
     * Remove a member to the group
     *
     * @param Account $member the member to remove
     */
    public function removeMemberOf($member)
    {
        foreach ($this->memberOf as $key => $memberAccount) {
            if ($memberAccount->compare($member) == 0) {
                $this->memberOf[$key] = null;
            }
        }
        $members = array_filter($this->memberOf);
        $this->memberOf = $members;
    }

    /**
     * @return mixed
     */
    public function getTeste()
    {
        return $this->teste;
    }

    /**
     * @param mixed $teste
     */
    public function setTeste($teste)
    {
        $this->teste = $teste;
    }


    public function compare($parametro)
    {
        if ($this->uid == $parametro->getUid()) {
            return 0;
        }
        return 1;
    }

    public function ordenaMail()
    {
        $array = array();
        foreach ($this->getMail() as $email){
            if(strpos($email, '@uft') !== false && count($array) == 0 ){
                $array[] = $email;
            } else if(strpos($email, '@mail.uft') !== false && count($array) == 0){
                $array[] = str_replace('@mail.uft', '@uft', $email);
            } else{
                if($this->getPostalAddress() == null){
                    $this->setPostalAddress($email);
                }
            }
        }
        $this->setMail($array);
    }

    public function setCryptPassword($password)
    {
        $this->userPassword = '{CRYPT}' . crypt($password, null);
    }

    /**
     * @return mixed
     */
    public function getCreateTimestamp()
    {
        return $this->createTimestamp;
    }

    /**
     * @param mixed $createTimestamp
     */
    public function setCreateTimestamp($createTimestamp)
    {
        $this->createTimestamp = $createTimestamp;
    }

    /**
     * @return mixed
     */
    public function getModifyTimestamp()
    {
        return $this->modifyTimestamp;
    }

    /**
     * @param mixed $modifyTimestamp
     */
    public function setModifyTimestamp($modifyTimestamp)
    {
        $this->modifyTimestamp = $modifyTimestamp;
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
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param mixed $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Add a member to the group
     *
     * @param Account $member the mmeber to add
     */
    public function addManager($manager)
    {
        $this->manager[] = $manager;
    }

    /**
     * Remove a member to the group
     *
     * @param Account $member the mmeber to remove
     */
    public function removeManager($member)
    {
        foreach ($this->manager as $key => $memberAccount) {
//            dump($memberAccount);
//            die();
            if ($memberAccount instanceof DepartamentoLdap && $memberAccount->compare($member) == 0) {
                $this->manager[$key] = null;
                unset($this->manager[$key]);
            } elseif (!($memberAccount instanceof DepartamentoLdap) && in_array($memberAccount, $member) == 1) {
                $this->manager[$key] = null;
            }
        }
        $members = array_filter($this->manager);
        $this->manager = $members;
    }

    public function __toString()
    {
        return (string)$this->getDn();
    }
}