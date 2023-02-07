<?php
/**
 * Created by PhpStorm.
 * User: carlosalves
 * Date: 07/03/17
 * Time: 11:08
 */


namespace UFT\SluBundle\Entity\SIP;

use Doctrine\ORM\Mapping as ORM;

/**
 * SipUsuario
 *
 * @ORM\Table(name="usuario")
 * @ORM\Entity
 */
class SipUsuario
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="UFT\SluBundle\Doctrine\SipCustomIdGenerator")
     * @ORM\Column(name="id_usuario", type="integer")
     */
    private $idUsuario;

    /**
     * @var integer
     * @ORM\Column(name="id_orgao", type="integer",  nullable=true)
     */
    private $idOrgao = 0;


    /**
     * @var string
     * @ORM\Column(name="nome", type="string", length=100,  nullable=false)
     */
    private $nome = null;


    /**
     * @var string
     * @ORM\Column(name="sigla", type="string", length=100,  nullable=false)
     */
    private $sigla;

    /**
     * @var string
     * @ORM\Column(name="sin_ativo", type="string", length=100,  nullable=false)
     */
    private $sinAtivo = 'S';

    /**
     * @var string
     * @ORM\Column(name="id_origem", type="string", length=50,  nullable=true)
     */
    private $idOrigem = null;

    /**
     * @ORM\Column(name="cpf", type="bigint", nullable=true)
     */
    private $cpf = null;

    /**
     * @var string
     * @ORM\Column(name="nome_registro_civil", type="string", length=100,  nullable=false)
     */
    private $nomeRegistroCivil;


    /**
     * @var string
     * @ORM\Column(name="nome_social", type="string", length=100,  nullable=true)
     */
    private $nomeSocial = null;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=100,  nullable=true)
     */
    private $email = null;

    /**
     * @var string
     * @ORM\Column(name="sin_bloqueado", type="string", length=1,  nullable=false)
     */
    private $sinBloqueado = 'N';


    /**
     * SieUsuario constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    /**
     * @param int $idUsuario
     */
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    /**
     * @return int
     */
    public function getIdOrgao()
    {
        return $this->idOrgao;
    }

    /**
     * @param int $idOrgao
     * @return SipUsuario
     */
    public function setIdOrgao($idOrgao)
    {
        $this->idOrgao = $idOrgao;
        return $this;
    }

    /**
     * @return string
     */
    public function getSigla()
    {
        return $this->sigla;
    }

    /**
     * @param string $sigla
     * @return SipUsuario
     */
    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
        return $this;
    }

    /**
     * @return string
     */
    public function getSinAtivo()
    {
        return $this->sinAtivo;
    }

    /**
     * @param string $sinAtivo
     * @return SipUsuario
     */
    public function setSinAtivo($sinAtivo)
    {
        $this->sinAtivo = $sinAtivo;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdOrigem()
    {
        return $this->idOrigem;
    }

    /**
     * @param string $idOrigem
     * @return SipUsuario
     */
    public function setIdOrigem($idOrigem)
    {
        $this->idOrigem = $idOrigem;
        return $this;
    }

    /**
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * @param  $cpf
     * @return SipUsuario
     */
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
        return $this;
    }

    /**
     * @return string
     */
    public function getNomeRegistroCivil()
    {
        return $this->nomeRegistroCivil;
    }

    /**
     * @param string $nomeRegistroCivil
     * @return SipUsuario
     */
    public function setNomeRegistroCivil($nomeRegistroCivil)
    {
        $this->nomeRegistroCivil = $nomeRegistroCivil;
        return $this;
    }

    /**
     * @return string
     */
    public function getNomeSocial()
    {
        return $this->nomeSocial;
    }

    /**
     * @param string $nomeSocial
     * @return SipUsuario
     */
    public function setNomeSocial($nomeSocial)
    {
        $this->nomeSocial = $nomeSocial;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return SipUsuario
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getSinBloqueado()
    {
        return $this->sinBloqueado;
    }

    /**
     * @param string $sinBloqueado
     * @return SipUsuario
     */
    public function setSinBloqueado($sinBloqueado)
    {
        $this->sinBloqueado = $sinBloqueado;
        return $this;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     * @return SipUsuario
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }

}