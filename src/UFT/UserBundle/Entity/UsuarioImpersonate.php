<?php

namespace UFT\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use UFT\SluBundle\Entity\PessoaLdap;


/**
 * @ORM\Entity()
 * @ORM\Table(name="slu_usuario_impersonate")
 * @ORM\Entity(repositoryClass="UFT\UserBundle\Repository\UsuarioImpersonateRepository")
 */
class UsuarioImpersonate
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",name="uid", nullable=false)
     */
    private $uid;

    /**
     * @ORM\Column(type="string",name="senha", nullable=false)
     */
    private $senha;

    /**
     * @ORM\Column(type="integer",name="flag", nullable=false, options={"default" = 0})
     */
    private $flag = 0;

    /**
     *
     * @var \DateTime $data_criacao
     *
     * @ORM\Column(name="data_criacao", type="datetime", nullable=false)
     */
    private $dataCriacao;

    /**
     * @ORM\Column(type="string",name="uid_auditoria", nullable=false)
     */
    private $uidAuditoria;

    /**
     * UsuarioImpersonate constructor.
     * @param $id
     * @param $uid
     * @param $senha
     */
    public function __construct(PessoaLdap $pessoaLdap)
    {
        $this->uid = $pessoaLdap->getUid();
        $this->senha = $pessoaLdap->getUserPassword()[0];
        $this->setDataCriacao(new \DateTime());
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
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     * @return UsuarioImpersonate
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * @param mixed $senha
     * @return UsuarioImpersonate
     */
    public function setSenha($senha)
    {
        $this->senha = $senha;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * @param mixed $flag
     * @return UsuarioImpersonate
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataCriacao()
    {
        return $this->dataCriacao;
    }

    /**
     * @param \DateTime $dataCriacao
     * @return UsuarioImpersonate
     */
    public function setDataCriacao($dataCriacao)
    {
        $this->dataCriacao = $dataCriacao;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUidAuditoria()
    {
        return $this->uidAuditoria;
    }

    /**
     * @param mixed $uidAuditoria
     * @return UsuarioImpersonate
     */
    public function setUidAuditoria($uidAuditoria)
    {
        $this->uidAuditoria = $uidAuditoria;
        return $this;
    }


    public function __toString()
    {
        return $this->getUid();
    }


}
