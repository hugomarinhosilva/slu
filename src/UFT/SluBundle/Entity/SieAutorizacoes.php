<?php
/**
 * Created by PhpStorm.
 * User: carlosalves
 * Date: 07/03/17
 * Time: 10:47
 */

namespace UFT\SluBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SieAutorizacoes
 *
 * @ORM\Table(name="DBSM.AUTORIZACOES")
 * @ORM\Entity
 */
class SieAutorizacoes
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="ID_USUARIO", type="integer")
     */
    private $idUsuario;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="ID_APLICACAO", type="integer")
     */
    private $idAplicacao;

    /**
     * @var date
     *
     * @ORM\Column(name="DT_VALIDADE", type="date",options={"default"="CURRENT_DATE"})
     */
    private $dataValidade;

    /**
     * @var string
     *
     * @ORM\Column(name="IND_REPASSE", type="string", length=1)
     */
    private $indRepasse = "N";


    /**
     * @var string
     *
     * @ORM\Column(name="OBSERVACAO", type="string", length=40,  nullable=true)
     */
    private $observacao;


    /**
     * @var string
     *
     * @ORM\Column(name="IND_MSG", type="string", length=1)
     */
    private $indMensagem = "S";

    /**
     * @var string
     *
     * @ORM\Column(name="IND_DESABILITA_MSG", type="string", length=1)
     */
    private $indDesabilitaMensagem  = "N";

    /**
     * @var integer
     *
     * @ORM\Column(name="COD_OPERADOR", type="integer")
     */
    private $codigoOperador = 75552;


    /**
     * @var date
     *
     * @ORM\Column(name="DT_ALTERACAO", type="date",options={"default"="CURRENT_DATE"})
     */
    private $dataAlteracao;

    /**
     * @var time
     *
     * @ORM\Column(name="HR_ALTERACAO", type="time",options={"default"="CURRENT_DATE"})
     */
    private $horaAlteracao ;


    /**
     * @var integer
     *
     * @ORM\Column(name="CONCORRENCIA", type="integer")
     *
     */
    private $concorrencia = 0;


    /**
     * @var integer
     *
     * @ORM\Column(name="ID_APLIC_ABERTURA", type="integer", nullable=true)
     *
     */
    private $idAplicacaoAbertura;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID_USR_REPASSE", type="integer", nullable=true)
     *
     */
    private $idUsuarioRepasse = 75552;


    /**
     * @var string
     *
     * @ORM\Column(name="ENDERECO_FISICO", type="string", length=15,  nullable=true)
     *
     */
    private $enderecoFisico;

    /**
     * SieAutorizacoes constructor.
     */
    public function __construct()
    {
        $this->dataValidade = $date = new \DateTime('2050-01-01');

    }


    /**
     * @return mixed
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    /**
     * @param mixed $idUsuario
     */
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    /**
     * @return int
     */
    public function getIdAplicacao()
    {
        return $this->idAplicacao;
    }

    /**
     * @param int $idAplicacao
     */
    public function setIdAplicacao($idAplicacao)
    {
        $this->idAplicacao = $idAplicacao;
    }

    /**
     * @return date
     */
    public function getDataValidade()
    {
        return $this->dataValidade;
    }

    /**
     * @param date $dataValidade
     */
    public function setDataValidade($dataValidade)
    {
        $this->dataValidade = $dataValidade;
    }

    /**
     * @return string
     */
    public function getIndRepasse()
    {
        return $this->indRepasse;
    }

    /**
     * @param string $indRepasse
     */
    public function setIndRepasse($indRepasse)
    {
        $this->indRepasse = $indRepasse;
    }

    /**
     * @return string
     */
    public function getObservacao()
    {
        return $this->observacao;
    }

    /**
     * @param string $observacao
     */
    public function setObservacao($observacao)
    {
        $this->observacao = $observacao;
    }

    /**
     * @return string
     */
    public function getIndMensagem()
    {
        return $this->indMensagem;
    }

    /**
     * @param string $indMensagem
     */
    public function setIndMensagem($indMensagem)
    {
        $this->indMensagem = $indMensagem;
    }

    /**
     * @return string
     */
    public function getIndDesabilitaMensagem()
    {
        return $this->indDesabilitaMensagem;
    }

    /**
     * @param string $indDesabilitaMensagem
     */
    public function setIndDesabilitaMensagem($indDesabilitaMensagem)
    {
        $this->indDesabilitaMensagem = $indDesabilitaMensagem;
    }

    /**
     * @return int
     */
    public function getCodigoOperador()
    {
        return $this->codigoOperador;
    }

    /**
     * @param int $codigoOperador
     */
    public function setCodigoOperador($codigoOperador)
    {
        $this->codigoOperador = $codigoOperador;
    }

    /**
     * @return date
     */
    public function getDataAlteracao()
    {
        return $this->dataAlteracao;
    }

    /**
     * @param date $dataAlteracao
     */
    public function setDataAlteracao($dataAlteracao)
    {
        $this->dataAlteracao = $dataAlteracao;
    }

    /**
     * @return time
     */
    public function getHoraAlteracao()
    {
        return $this->horaAlteracao;
    }

    /**
     * @param time $horaAlteracao
     */
    public function setHoraAlteracao($horaAlteracao)
    {
        $this->horaAlteracao = $horaAlteracao;
    }

    /**
     * @return int
     */
    public function getConcorrencia()
    {
        return $this->concorrencia;
    }

    /**
     * @param int $concorrencia
     */
    public function setConcorrencia($concorrencia)
    {
        $this->concorrencia = $concorrencia;
    }

    /**
     * @return int
     */
    public function getIdAplicacaoAbertura()
    {
        return $this->idAplicacaoAbertura;
    }

    /**
     * @param int $idAplicacaoAbertura
     */
    public function setIdAplicacaoAbertura($idAplicacaoAbertura)
    {
        $this->idAplicacaoAbertura = $idAplicacaoAbertura;
    }

    /**
     * @return int
     */
    public function getIdUsuarioRepasse()
    {
        return $this->idUsuarioRepasse;
    }

    /**
     * @param int $idUsuarioRepasse
     */
    public function setIdUsuarioRepasse($idUsuarioRepasse)
    {
        $this->idUsuarioRepasse = $idUsuarioRepasse;
    }

    /**
     * @return string
     */
    public function getEnderecoFisico()
    {
        return $this->enderecoFisico;
    }

    /**
     * @param string $enderecoFisico
     */
    public function setEnderecoFisico($enderecoFisico)
    {
        $this->enderecoFisico = $enderecoFisico;
    }

}