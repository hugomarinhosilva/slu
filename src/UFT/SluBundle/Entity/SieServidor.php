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
 * SieUnidadeExercicio
 *
 * @ORM\Table(name="DBSM.SLU_DADOS_SERVIDOR")
 * @ORM\Entity
 */
class SieServidor
{

    /**
     * @var string
     *
     * @ORM\Column(name="NOME_SOCIAL", type="string", length=255)
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="NOME_FUNCIONARIO", type="string", length=255)
     */
    private $nomePessoa;


    /**
     * @var string
     *
     * @ORM\Column(name="NOME_SOCIAL_SA", type="string", length=255)
     */
    private $nomeSemAcento;



    /**
     * @return string
     */
    public function getNomePessoa()
    {
        return $this->nomePessoa;
    }

    /**
     * @param string $nomePessoa
     */
    public function setNomePessoa($nomePessoa)
    {
        $this->nomePessoa = $nomePessoa;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="CPF", type="string", length=15)
     */
    private $cpf;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID_PESSOA", type="integer")
     */
    private $idPessoa;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID_DOCENTE", type="integer")
     */
    private $idDocente;


    /**
     * @var string
     *
     * @ORM\Column(name="RG", type="string", length=30)
     */
    private $rg;


    /**
     * @var string
     *
     * @ORM\Column(name="NOME_PAI", type="string", length=255)
     */
    private $nomePai;

    /**
     * @var string
     *
     * @ORM\Column(name="NOME_MAE", type="string", length=255)
     */
    private $nomeMae;

    /**
     * @var string
     *
     * @ORM\Column(name="SEXO", type="string", length=1)
     */
    private $sexo;

    /**
     * @var string
     *
     * @ORM\Column(name="FONE_CELULAR", type="string", length=20)
     */
    private $telefone;

    /**
     * @var string
     *
     * @ORM\Column(name="DESCR_MAIL", type="string", length=70)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="DESCR_MAIL2", type="string", length=70)
     */
    private $email2;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_NASCIMENTO", type="date")
     */
    private $dataNascimento;

    /**
     * @var integer
     *
     * @ORM\Column(name="SITUACAO_ITEM", type="integer")
     */
    private $idSituacao;

    /**
     * @var string
     *
     * @ORM\Column(name="SITUACAO_DESCRICAO", type="string", length=50)
     */
    private $descricaoSituacao;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="MATR_EXTERNA", type="string", length=25)
     */
    private $matricula;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_POSSE", type="date")
     */
    private $dataPosse;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_NOMEACAO", type="date")
     */
    private $dataNomeacao;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_ADMISSAO_CARGO", type="date")
     */
    private $dataAdmissao;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_APOSENTADORIA", type="date")
     */
    private $dataAposentadoria;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_DEMISSAO", type="date")
     */
    private $dataDemissao;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_DESLIGAMENTO", type="date")
     */
    private $dataDesligamento;

    /**
     * @var string
     *
     * @ORM\Column(name="DESCR_CARGO", type="string", length=25)
     */
    private $descricaoCargo;

    /**
     * @var integer
     *
     * @ORM\Column(name="GRUPO_CARGO_ITEM", type="integer")
     */
    private $idCargo;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID_NATURALIDADE", type="integer")
     */
    private $idNaturalidade;

    /**
     * @var string
     *
     * @ORM\Column(name="NATURALIDADE", type="string",length=100)
     */
    private $naturalidade;

    /**
     * @var string
     *
     * @ORM\Column(name="UF", type="string",length=2)
     */
    private $uf;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_ALTERACAO", type="date")
     */
    private $dataAlteracao;

    /**
     * @var string
     *
     * @ORM\Column(name="NOME_UNIDADE_EXERCICIO", type="string",length=500)
     */
    private $unidadeExercicio;

    /**
     * @var string
     *
     * @ORM\Column(name="NOME_UNIDADE_OFICIAL", type="string",length=100)
     */
    private $unidadeOficial;

    /**
     * @var string
     *
     * @ORM\Column(name="CAMPUS", type="string",length=100)
     */
    private $campus;


    /**
     * @var string
     *
     * @ORM\Column(name="COD_ESTRUTURADO_EXERCICIO", type="string",length=100)
     */
    private $codEstruturadoExercicio;

    /**
     * @var string
     *
     * @ORM\Column(name="COD_ESTRUTURADO_OFICIAL", type="string",length=100)
     */
    private $codEstruturadoOficial;


    /**
     * @return string
     */
    public function getNome()
    {
//        return iconv('ISO-8859-1', 'UTF-8', $this->nome);
        return $this->nome;
    }

    /**
     * @param string $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return string
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * @param string $cpf
     */
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
    }

    /**
     * @return string
     */
    public function getRg()
    {
        return $this->rg;
    }

    /**
     * @param string $rg
     */
    public function setRg($rg)
    {
        $this->rg = $rg;
    }

    /**
     * @return string
     */
    public function getNomePai()
    {
        return $this->nomePai;
    }

    /**
     * @param string $nomePai
     */
    public function setNomePai($nomePai)
    {
        $this->nomePai = $nomePai;
    }

    /**
     * @return string
     */
    public function getNomeMae()
    {
        return $this->nomeMae;
    }

    /**
     * @param string $nomeMae
     */
    public function setNomeMae($nomeMae)
    {
        $this->nomeMae = $nomeMae;
    }

    /**
     * @return string
     */
    public function getSexo()
    {
        return $this->sexo;
    }

    /**
     * @param string $sexo
     */
    public function setSexo($sexo)
    {
        $this->sexo = $sexo;
    }

    /**
     * @return string
     */
    public function getTelefone()
    {
        return $this->telefone;
    }

    /**
     * @param string $telefone
     */
    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
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
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * @param string $email2
     */
    public function setEmail2($email2)
    {
        $this->email2 = $email2;
    }

    /**
     * @return \DateTime
     */
    public function getDataNascimento()
    {
        return $this->dataNascimento;
    }

    /**
     * @param \DateTime $dataNascimento
     */
    public function setDataNascimento($dataNascimento)
    {
        $this->dataNascimento = $dataNascimento;
    }

    /**
     * @return int
     */
    public function getIdSituacao()
    {
        return $this->idSituacao;
    }

    /**
     * @param int $idSituacao
     */
    public function setIdSituacao($idSituacao)
    {
        $this->idSituacao = $idSituacao;
    }

    /**
     * @return string
     */
    public function getDescricaoSituacao()
    {
        return $this->descricaoSituacao;
    }

    /**
     * @param string $descricaoSituacao
     */
    public function setDescricaoSituacao($descricaoSituacao)
    {
        $this->descricaoSituacao = $descricaoSituacao;
    }

    /**
     * @return string
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * @param string $matricula
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;
    }

    /**
     * @return \DateTime
     */
    public function getDataPosse()
    {
        return $this->dataPosse;
    }

    /**
     * @param \DateTime $dataPosse
     */
    public function setDataPosse($dataPosse)
    {
        $this->dataPosse = $dataPosse;
    }

    /**
     * @return \DateTime
     */
    public function getDataNomeacao()
    {
        return $this->dataNomeacao;
    }

    /**
     * @param \DateTime $dataNomeacao
     */
    public function setDataNomeacao($dataNomeacao)
    {
        $this->dataNomeacao = $dataNomeacao;
    }

    /**
     * @return \DateTime
     */
    public function getDataAdmissao()
    {
        return $this->dataAdmissao;
    }

    /**
     * @param \DateTime $dataAdmissao
     */
    public function setDataAdmissao($dataAdmissao)
    {
        $this->dataAdmissao = $dataAdmissao;
    }

    /**
     * @return \DateTime
     */
    public function getDataAposentadoria()
    {
        return $this->dataAposentadoria;
    }

    /**
     * @param \DateTime $dataAposentadoria
     */
    public function setDataAposentadoria($dataAposentadoria)
    {
        $this->dataAposentadoria = $dataAposentadoria;
    }

    /**
     * @return \DateTime
     */
    public function getDataDemissao()
    {
        return $this->dataDemissao;
    }

    /**
     * @param \DateTime $dataDemissao
     */
    public function setDataDemissao($dataDemissao)
    {
        $this->dataDemissao = $dataDemissao;
    }

    /**
     * @return \DateTime
     */
    public function getDataDesligamento()
    {
        return $this->dataDesligamento;
    }

    /**
     * @param \DateTime $dataDesligamento
     */
    public function setDataDesligamento($dataDesligamento)
    {
        $this->dataDesligamento = $dataDesligamento;
    }

    /**
     * @return string
     */
    public function getDescricaoCargo()
    {
        return $this->descricaoCargo;
    }

    /**
     * @param string $descricaoCargo
     */
    public function setDescricaoCargo($descricaoCargo)
    {
        $this->descricaoCargo = $descricaoCargo;
    }

    /**
     * @return int
     */
    public function getIdCargo()
    {
        return $this->idCargo;
    }

    /**
     * @param int $idCargo
     */
    public function setIdCargo($idCargo)
    {
        $this->idCargo = $idCargo;
    }

    /**
     * @return int
     */
    public function getIdNaturalidade()
    {
        return $this->idNaturalidade;
    }

    /**
     * @param int $idNaturalidade
     */
    public function setIdNaturalidade($idNaturalidade)
    {
        $this->idNaturalidade = $idNaturalidade;
    }

    /**
     * @return string
     */
    public function getNaturalidade()
    {
        return $this->naturalidade;
    }

    /**
     * @param string $naturalidade
     */
    public function setNaturalidade($naturalidade)
    {
        $this->naturalidade = $naturalidade;
    }

    /**
     * @return string
     */
    public function getUf()
    {
        return $this->uf;
    }

    /**
     * @param string $uf
     */
    public function setUf($uf)
    {
        $this->uf = $uf;
    }

    /**
     * @return \DateTime
     */
    public function getDataAlteracao()
    {
        return $this->dataAlteracao;
    }

    /**
     * @param \DateTime $dataAlteracao
     */
    public function setDataAlteracao($dataAlteracao)
    {
        $this->dataAlteracao = $dataAlteracao;
    }

    /**
     * @return string
     */
    public function getUnidadeExercicio()
    {
        return $this->unidadeExercicio;
    }

    /**
     * @param string $unidadeExercicio
     */
    public function setUnidadeExercicio($unidadeExercicio)
    {
        $this->unidadeExercicio = $unidadeExercicio;
    }

    /**
     * @return string
     */
    public function getUnidadeOficial()
    {
        return $this->unidadeOficial;
    }

    /**
     * @param string $unidadeOficial
     */
    public function setUnidadeOficial($unidadeOficial)
    {
        $this->unidadeOficial = $unidadeOficial;
    }

    /**
     * @return int
     */
    public function getIdPessoa()
    {
        return $this->idPessoa;
    }

    /**
     * @param int $idPessoa
     */
    public function setIdPessoa($idPessoa)
    {
        $this->idPessoa = $idPessoa;
    }

    /**
     * @return int
     */
    public function getIdDocente()
    {
        return $this->idDocente;
    }

    /**
     * @param int $idDocente
     */
    public function setIdDocente($idDocente)
    {
        $this->idDocente = $idDocente;
    }

    /**
     * @return string
     */
    public function getCampus()
    {
//        return iconv('ISO-8859-1', 'UTF-8', $this->campus);
        return $this->campus;
    }

    /**
     * @param string $campus
     */
    public function setCampus($campus)
    {
        $this->campus = $campus;
    }

    /**
     * @return string
     */
    public function getCodEstruturadoExercicio()
    {
        return $this->codEstruturadoExercicio;
    }

    /**
     * @param string $codEstruturadoExercicio
     */
    public function setCodEstruturadoExercicio($codEstruturadoExercicio)
    {
        $this->codEstruturadoExercicio = $codEstruturadoExercicio;
    }

    /**
     * @return string
     */
    public function getCodEstruturadoOficial()
    {
        return $this->codEstruturadoOficial;
    }

    /**
     * @param string $codEstruturadoOficial
     */
    public function setCodEstruturadoOficial($codEstruturadoOficial)
    {
        $this->codEstruturadoOficial = $codEstruturadoOficial;
    }

    /**
     * @return string
     */
    public function getNomeSemAcento()
    {
        return $this->nomeSemAcento;
    }

    /**
     * @param string $nomeSemAcento
     */
    public function setNomeSemAcento($nomeSemAcento)
    {
        $this->nomeSemAcento = $nomeSemAcento;
    }




    function __toString()
    {
        return $this->getNome();
    }

    public function getPessoaLdap($pessoaLdap)
    {
        $pessoaLdap->setDepartmentNumber(array(trim($this->getCodEstruturadoExercicio())));
        $pessoaLdap->setDisplayName(trim($this->getNomeSemAcento()));
        $pessoaLdap->setSchacDateOfBirth($this->getDataNascimento());
        switch (trim($this->getCampus())){
            case 'Araguaina':
                $pessoaLdap->setCampus('Araguaína');
                break;
            case 'Tocantinopolis':
                $pessoaLdap->setCampus('Tocantinópolis');
                break;
            default:
                $pessoaLdap->setCampus(trim($this->getCampus()));
        }

        $pessoaLdap->setIdPessoa($this->getIdPessoa());
        $pessoaLdap->setIdOrigem($this->getIdPessoa());
        $pessoaLdap->setTipoOrigemItem(2);
        $pessoaLdap->setGivenName(trim($this->getNomePessoa()));
        if ($this->getSexo() == 'M') {
            $pessoaLdap->setSchacGender(1);
        } elseif ($this->getSexo() == 'F') {
            $pessoaLdap->setSchacGender(2);
        } else {
            $pessoaLdap->setSchacGender(9);
        }
        if ($this->getIdCargo() == 61 || $this->getIdCargo() == 62 || $this->getIdCargo() == 733) {
            $pessoaLdap->setProfessor(1);
            $pessoaLdap->setIdDocente($this->getIdDocente());
        } else {
            $pessoaLdap->setFuncionario(1);
        }
        $matricula = trim($this->getMatricula());
        if ($matricula != null && ($pessoaLdap->getMatricula() == null || !in_array($matricula, array_map('trim', $pessoaLdap->getMatricula())))) {
            $pessoaLdap->addMatricula($matricula);
        }
        return $pessoaLdap;
    }


}