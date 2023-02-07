<?php
/**
 * Created by PhpStorm.
 * User: carlosalves
 * Date: 07/03/17
 * Time: 11:08
 */


namespace UFT\SluBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SieUsuario
 *
 * @ORM\Table(name="DBSM.USUARIOS_GRUPOS")
 * @ORM\Entity
 */
class SieUsuarioGrupo
{

    /**
     * @var integer
     * @ORM\Id
     * * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="UFT\SluBundle\Doctrine\CustomIdGenerator")
     * @ORM\Column(name="ID_USUARIO_GRUPO", type="integer")
     */
    private $idUsuarioGrupo;

    /**
     * @var integer
     * @ORM\Column(name="ID_USUARIO", type="integer")
     */
    private $idUsuario;

    /**
     * @var integer
     * @ORM\Column(name="ID_GRUPO", type="integer")
     */
    private $idgrupo;

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
     * @var string
     * @ORM\Column(name="ENDERECO_FISICO", type="string", length=100 , nullable=true)
     */
    private $enderecoFisico;

    /**
     * @return int
     */
    public function getIdUsuarioGrupo()
    {
        return $this->idUsuarioGrupo;
    }

    /**
     * @param int $idUsuarioGrupo
     */
    public function setIdUsuarioGrupo($idUsuarioGrupo)
    {
        $this->idUsuarioGrupo = $idUsuarioGrupo;
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
    public function getIdgrupo()
    {
        return $this->idgrupo;
    }

    /**
     * @param int $idgrupo
     */
    public function setIdgrupo($idgrupo)
    {
        $this->idgrupo = $idgrupo;
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