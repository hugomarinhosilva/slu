<?php

namespace UFT\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="slu_filtro_unidade")
 */
class FiltroUnidade
{
    /**
     * @ORM\Id
     * @ORM\Column(name="cod_estruturado", type="string")
     */
    protected $codEstruturado;

    /**
     * @ORM\Column(name="nome_unidade", type="string")
     */
    protected $nomeUnidade;


    /**
     * Inverse Side
     *
     * @ORM\ManyToMany(targetEntity="Grupo", mappedBy="filtros")
     */
    private $grupos;



    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getCodEstruturado()
    {
        return $this->codEstruturado;
    }

    /**
     * @param mixed $codEstruturado
     */
    public function setCodEstruturado($codEstruturado)
    {
        $this->codEstruturado = $codEstruturado;
    }

    


    /**
     * @return mixed
     */
    public function getNomeUnidade()
    {
        return $this->nomeUnidade;
    }

    /**
     * @param mixed $nomeUnidade
     */
    public function setNomeUnidade($nomeUnidade)
    {
        $this->nomeUnidade = $nomeUnidade;
    }



    /**
     * @return mixed
     */
    public function getGrupos()
    {
        return $this->grupos;
    }

    /**
     * @param mixed $grupos
     */
    public function setGrupos($grupos)
    {
        $this->grupos = $grupos;
    }





    function __toString()
    {
        return $this->getNomeUnidade();
    }


}