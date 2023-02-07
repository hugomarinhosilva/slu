<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 29/03/16
 * Time: 10:20
 */

namespace UFT\SluBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
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
class PessoaLdap extends InetOrgPerson
{

    /**
     * @Attribute("postalAddress")
     * @Assert\Regex( pattern="/^((?!uft.edu.br).)*$/", message="O endereço nâo pode ser da UFT")
     */
    protected $postalAddress;

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

        if (empty($this->departmentNumber)) {
            $this->departmentNumber = null;
        }
        if (empty($this->matricula)) {
            $this->matricula = null;
        }
        if($this->teste == true){
            $this->teste = 1;
        }else{
            $this->teste = 0;
        }


        /*$arrayFone = null;
        foreach ($this->telephoneNumber as $telefone) {
            $arrayFone[] = '+' . preg_replace("/[^0-9]/", "", $telefone);
        }
        $this->telephoneNumber = $arrayFone;*/
        $this->telephoneNumber = preg_replace("/[^0-9]/", "", $this->telephoneNumber);


        $this->brPersonCPF = preg_replace("/[^0-9]/", "", $this->brPersonCPF);
        // Insere o primeiro nome e o nome completo no CN
        $this->cn = array($primeiro_nome, $this->displayName);
        //Insere o Resto do nome da pessoa (sem o primeiro nome) no campo SN
        $this->sn = array($resto_nome);
//TODO ENCRIPTAR SENHA
        if($this->alteraSenha != 0){
            $this->userPassword = '{CRYPT}' . crypt($this->userPassword, null);
        }
        //Formatando data para o LDAP
        $this->schacDateOfBirth = !empty($this->schacDateOfBirth) ? $this->schacDateOfBirth->format('dmY'): $this->schacDateOfBirth ;
        $this->homeDirectory = '/home/' . $this->uid;
        $this->gecos = $this->displayName;
        $this->cpf = $this->brPersonCPF;
    }

    public function getNovasObjectClass()
    {
        $objectClass[] = 'brPerson';
        $objectClass[] = 'inetOrgPerson';
        $objectClass[] = 'organizationalPerson';
        $objectClass[] = 'person';
        $objectClass[] = 'schacPersonalCharacteristics';
        $objectClass[] = 'top';
        $objectClass[] = 'uftOrgUnit';
        $objectClass[] = 'posixAccount';
        return $objectClass;
    }

    public function isRecadastrado(){
        return empty(array_diff($this->getNovasObjectClass(),$this->getObjectClass())) && empty(array_diff($this->getNovasObjectClass(),$this->getObjectClass()));
    }

    /**
     * @Attribute("brPersonCPF")
     */
    protected $brPersonCPF;

    /**
     * @Attribute("memberOf")
     * @DnLinkArray("UFT\SluBundle\Entity\GrupoLdap")
     *
     */
    protected $memberOf;

    /**
     * @Attribute("schacDateOfBirth")
     */
    protected $schacDateOfBirth;

    /**
     * @Attribute("Teste")
     */
    protected $teste;

    /**
     * @Attribute("schacGender")
     */
    protected $schacGender;

    /* AGORA SÓ OBSOLETOS */
    /**
     * @Attribute("Aluno")
     */
    protected $aluno;

    /**
     * @Attribute("Funcionario")
     */
    protected $funcionario;

    /**
     * @Attribute("Professor")
     */
    protected $professor;

    /**
     * @Attribute("Institucional")
     */
    protected $institucional = 0;

    /**
     * @Attribute("Matricula")
     * @ArrayField()
     */
    protected $matricula;

    /**
     * @Attribute("Campus")
     * @ArrayField()
     */
    protected $campus;

    /**
     * @Attribute("CPF")
     */
    protected $cpf;

    /**
     * @Attribute("IDPessoa")
     */
    protected $idPessoa = 0;

    /**
     *
     * @Attribute("IDDocente")
     */
    protected $idDocente = 0;

    /**
     * @Attribute("gecos")
     */
    protected $gecos;

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
     * @ArrayField()
     */
    protected $grupo;

    /**
     * @Attribute("telephoneNumber")
     * @ArrayField()
     */
    protected $telephoneNumber;

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
     * 
     */
    protected $alteraSenha;

    /**
     *
     */
    protected $idOrigem;

    /**
     *
     */
    protected $tipoOrigemItem;

    /**
     * @return mixed
     */
    public function getTipoOrigemItem()
    {
        return $this->tipoOrigemItem;
    }

    /**
     * @param mixed $tipoOrigemItem
     */
    public function setTipoOrigemItem($tipoOrigemItem)
    {
        $this->tipoOrigemItem = $tipoOrigemItem;
    }


    /**
     * @return mixed
     */
    public function getIdOrigem()
    {
        return $this->idOrigem;
    }

    /**
     * @param mixed $idOrigem
     */
    public function setIdOrigem($idOrigem)
    {
        $this->idOrigem = $idOrigem;
    }


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
     * @return mixed
     */
    public function getBrPersonCPF()
    {
        return $this->brPersonCPF;
    }

    /**
     * @param mixed $brPersonCPF
     */
    public function setBrPersonCPF($brPersonCPF)
    {
        $this->brPersonCPF = $brPersonCPF;
    }

    /**
     * @return mixed
     */
    public function getSchacDateOfBirth()
    {
        return $this->schacDateOfBirth;
    }

    /**
     * @param mixed $schacDateOfBirth
     */
    public function setSchacDateOfBirth($schacDateOfBirth)
    {
        $this->schacDateOfBirth = $schacDateOfBirth;
    }

    /**
     * @return mixed
     */
    public function getSchacGender()
    {
        return $this->schacGender;
    }

    /**
     * @param mixed $schacGender
     */
    public function setSchacGender($schacGender)
    {
        $this->schacGender = $schacGender;
    }

    /**
     * @return mixed
     */
    public function getAluno()
    {
        return $this->aluno;
    }

    /**
     * @param mixed $aluno
     */
    public function setAluno($aluno)
    {
        $this->aluno = $aluno;
    }

    /**
     * @return mixed
     */
    public function getFuncionario()
    {
        return $this->funcionario;
    }

    /**
     * @param mixed $funcionario
     */
    public function setFuncionario($funcionario)
    {
        $this->funcionario = $funcionario;
    }

    /**
     * @return mixed
     */
    public function getProfessor()
    {
        return $this->professor;
    }

    /**
     * @param mixed $professor
     */
    public function setProfessor($professor)
    {
        $this->professor = $professor;
    }

    /**
     * @return mixed
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * @param mixed $matricula
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;
    }

    /**
     * @param mixed $matricula
     * @return mixed
     */
    public function addMatricula($matricula)
    {

        if(!in_array(trim($matricula), $this->matricula)){
            $this->matricula[] = $matricula;
        }

        return $this;
    }

    /**
     * @param mixed $matricula
     */
    public function removeMatricula($matricula)
    {
        $key = array_search($matricula, $this->matricula);
        if($key!==false){
            array_splice($this->matricula, $key, 1);
        }
    }

    /**
     * @param mixed $mail
     * @return mixed
     */
    public function addMail($mail)
    {

        if(strpos($mail, '@uft') !== false ){
            $this->mail[0] = $mail;
        }else{
            $this->postalAddress = $mail;
        }

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
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * @param mixed $cpf
     */
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
    }

    /**
     * @return mixed
     */
    public function getIdPessoa()
    {
        return $this->idPessoa;
    }

    /**
     * @param mixed $idPessoa
     */
    public function setIdPessoa($idPessoa)
    {
        $this->idPessoa = $idPessoa;
    }

    /**
     * @return mixed
     */
    public function getIdDocente()
    {
        return $this->idDocente;
    }

    /**
     * @param mixed $idDocente
     */
    public function setIdDocente($idDocente)
    {
        $this->idDocente = $idDocente;
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
    public function getTelephoneNumber()
    {
        return $this->telephoneNumber;
    }

    /**
     * @param mixed $telephoneNumber
     */
    public function setTelephoneNumber($telephoneNumber)
    {
        $this->telephoneNumber = $telephoneNumber;
    }

    public function addTelephoneNumber($telephoneNumber)
    {
        $this->telephoneNumber[] = $telephoneNumber;
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
            } else if(strpos($email, '@mail.uft') !== false && count($array) == 0 ){
                $array[] = str_replace('@mail.uft', '@uft', $email);
            } else{
                $this->setPostalAddress($email);
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

    

    public function dump()
    {
        return  var_export($this,true) ;
    }

    public function getDefaultDn()
    {
        return empty($this->dn)?"uid={$this->uid},ou=people,dc=uft,dc=edu,dc=br":$this->dn;
    }

    public function __toString()
    {
        return (string)$this->getDn();
    }



}