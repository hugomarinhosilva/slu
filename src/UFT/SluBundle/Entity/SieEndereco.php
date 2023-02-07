<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 17/12/15
 * Time: 15:36
 */

namespace UFT\SluBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SieEndereco
 *
 * @ORM\Table(name="DBSM.ENDERECOS")
 * @ORM\Entity
 */
class SieEndereco
{

    /**
     * @ORM\Column(name="ID_ENDERECO")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="UFT\SluBundle\Doctrine\CustomIdGenerator")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="ID_ORIGEM", type="integer")
     */
    private $idOrigem;

    /**
     * @var integer
     * @ORM\Column(name="TIPO_ORIGEM_TAB", type="integer")
     */
    private $tipoOrigemTab = 141;

    /**
     * @var integer
     * @ORM\Column(name="TIPO_ORIGEM_ITEM", type="integer")
     */
    private $tipoOrigemItem;

    /**
     * @var integer
     * @ORM\Column(name="TIPO_END_TAB", type="integer")
     */
    private $tipoEndTab = 240;

    /**
     * @var integer
     * @ORM\Column(name="TIPO_END_ITEM", type="integer")
     */
    private $tipoEndItem = 5;


    /**
     * @var string
     *
     * @ORM\Column(name="DESCR_MAIL", type="string", length=255)
     */
    private $emailPessoal;

    /**
     * @var string
     *
     * @ORM\Column(name="DESCR_MAIL2", type="string", length=255)
     */
    private $emailInstitucional;

    /**
     * @var string
     *
     * @ORM\Column(name="TIPO_ENDERECO", type="string", length=1)
     */
    private $tipoEndereco = 'S';
    /**
     * @var integer
     *
     * @ORM\Column(name="DDI_CELULAR", type="integer")
     */
    private $ddiCelular;
    /**
     * @var integer
     *
     * @ORM\Column(name="DDd_CELULAR", type="integer")
     */
    private $dddCelular;
    /**
     * @var string
     *
     * @ORM\Column(name="FONE_CELULAR", type="string", length=10)
     */
    private $foneCelular ;

/**
     * @var integer
     *
     * @ORM\Column(name="DDD_RESIDENCIAL", type="integer")
     */
    private $ddiResidencial;
    /**
     * @var integer
     *
     * @ORM\Column(name="DDI_RESIDENCIAL", type="integer")
     */
    private $dddResidencial;
    /**
     * @var string
     *
     * @ORM\Column(name="FONE_RESIDENCIAL", type="string", length=10)
     */
    private $foneResidencial ;



    /**
     * @var integer
     *
     * @ORM\Column(name="COD_OPERADOR", type="integer")
     */
    private $codigoOperador = 68285;

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
     * @ORM\Column(name="CONCORRENCIA", type="integer", nullable=true)
     *
     */
    private $concorrencia = 0;

    /**
     * SieEndereco constructor.
     */
    public function __construct()
    {
        $this->dataAlteracao = new \DateTime();
        $this->horaAlteracao = new \DateTime();
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
    }

    /**
     * @return int
     */
    public function getIdOrigem()
    {
        return $this->idOrigem;
    }

    /**
     * @param int $idOrigem
     */
    public function setIdOrigem($idOrigem)
    {
        $this->idOrigem = $idOrigem;
    }

    /**
     * @return int
     */
    public function getTipoOrigemTab()
    {
        return $this->tipoOrigemTab;
    }

    /**
     * @param int $tipoOrigemTab
     */
    public function setTipoOrigemTab($tipoOrigemTab)
    {
        $this->tipoOrigemTab = $tipoOrigemTab;
    }

    /**
     * @return int
     */
    public function getTipoOrigemItem()
    {
        return $this->tipoOrigemItem;
    }

    /**
     * @param int $tipoOrigemItem
     */
    public function setTipoOrigemItem($tipoOrigemItem)
    {
        $this->tipoOrigemItem = $tipoOrigemItem;
    }

    /**
     * @return int
     */
    public function getTipoEndTab()
    {
        return $this->tipoEndTab;
    }

    /**
     * @param int $tipoEndTab
     */
    public function setTipoEndTab($tipoEndTab)
    {
        $this->tipoEndTab = $tipoEndTab;
    }

    /**
     * @return int
     */
    public function getTipoEndItem()
    {
        return $this->tipoEndItem;
    }

    /**
     * @param int $tipoEndItem
     */
    public function setTipoEndItem($tipoEndItem)
    {
        $this->tipoEndItem = $tipoEndItem;
    }

    /**
     * @return string
     */
    public function getEmailPessoal()
    {
        return $this->emailPessoal;
    }

    /**
     * @param string $emailPessoal
     */
    public function setEmailPessoal($emailPessoal)
    {
        $this->emailPessoal = $emailPessoal;
    }

    /**
     * @return string
     */
    public function getEmailInstitucional()
    {
        return $this->emailInstitucional;
    }

    /**
     * @param string $emailInstitucional
     */
    public function setEmailInstitucional($emailInstitucional)
    {
        $this->emailInstitucional = $emailInstitucional;
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
     * @return string
     */
    public function getTipoEndereco()
    {
        return $this->tipoEndereco;
    }

    /**
     * @param string $tipoEndereco
     */
    public function setTipoEndereco($tipoEndereco)
    {
        $this->tipoEndereco = $tipoEndereco;
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
    public function getDdiCelular()
    {
        return $this->ddiCelular;
    }

    /**
     * @param int $ddiCelular
     */
    public function setDdiCelular($ddiCelular)
    {
        $this->ddiCelular = $ddiCelular;
    }

    /**
     * @return int
     */
    public function getDddCelular()
    {
        return $this->dddCelular;
    }

    /**
     * @param int $dddCelular
     */
    public function setDddCelular($dddCelular)
    {
        $this->dddCelular = $dddCelular;
    }

    /**
     * @return string
     */
    public function getFoneCelular()
    {
        return $this->foneCelular;
    }

    /**
     * @param string $foneCelular
     */
    public function setFoneCelular($foneCelular)
    {
        $this->foneCelular = $foneCelular;
    }

    /**
     * @return int
     */
    public function getDdiResidencial()
    {
        return $this->ddiResidencial;
    }

    /**
     * @param int $ddiResidencial
     */
    public function setDdiResidencial($ddiResidencial)
    {
        $this->ddiResidencial = $ddiResidencial;
    }

    /**
     * @return int
     */
    public function getDddResidencial()
    {
        return $this->dddResidencial;
    }

    /**
     * @param int $dddResidencial
     */
    public function setDddResidencial($dddResidencial)
    {
        $this->dddResidencial = $dddResidencial;
    }

    /**
     * @return string
     */
    public function getFoneResidencial()
    {
        return $this->foneResidencial;
    }

    /**
     * @param string $foneResidencial
     */
    public function setFoneResidencial($foneResidencial)
    {
        $this->foneResidencial = $foneResidencial;
    }






}