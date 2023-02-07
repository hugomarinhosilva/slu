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
 * SieAluno
 *
 * @ORM\Table(name="DBSM.SLU_DADOS_ALUNO")
 * @ORM\Entity
 */
class SieAluno
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID_PESSOA", type="integer")
     */
    private $idPessoa;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID_ALUNO", type="integer")
     */
    private $idAluno;


    /**
     * @var string
     *
     * @ORM\Column(name="NOME_SOCIAL", type="string", length=255)
     */
    private $nome;
    /**
     * @var string
     *
     * @ORM\Column(name="NOME_SOCIAL_SA", type="string", length=255)
     */
    private $nomeSemAcento;

    /**
     * @var string
     *
     * @ORM\Column(name="NOME_ALUNO", type="string", length=255)
     */
    private $nomePessoa;
    /**
     * @var string
     *
     * @ORM\Column(name="CPF", type="string", length=15)
     */
    private $cpf;
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
     * @ORM\Column(name="DESCR_MAIL2", type="string", length=255)
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
     * @ORM\Column(name="MATR_ALUNO", type="string", length=25)
     */
    private $matricula;
    /**
     * @var integer
     *
     * @ORM\Column(name="ANO_INGRESSO", type="integer")
     */
    private $anoIngresso;
    /**
     * @var integer
     *
     * @ORM\Column(name="PERIODO_INGRESSO", type="integer")
     */
    private $periodoIngresso;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_INGRESSO", type="date")
     */
    private $dataIngresso;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DT_SAIDA", type="date")
     */
    private $dataSaida;
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
     * @ORM\Column(name="NOME_CURSO", type="string",length=500)
     */
    private $nomeCurso;
    /**
     * @var string
     *
     * @ORM\Column(name="CAMPUS", type="string",length=100)
     */
    private $nomeCampus;
    /**
     * @var string
     *
     * @ORM\Column(name="COD_ESTRUTURADO_CURSO", type="string",length=100)
     */
    private $codEstruturadoExercicio;

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
     * @return int
     */
    public function getAnoIngresso()
    {
        return $this->anoIngresso;
    }

    /**
     * @param int $anoIngresso
     */
    public function setAnoIngresso($anoIngresso)
    {
        $this->anoIngresso = $anoIngresso;
    }

    /**
     * @return int
     */
    public function getPeriodoIngresso()
    {
        return $this->periodoIngresso;
    }

    /**
     * @param int $periodoIngresso
     */
    public function setPeriodoIngresso($periodoIngresso)
    {
        $this->periodoIngresso = $periodoIngresso;
    }

    /**
     * @return \DateTime
     */
    public function getDataIngresso()
    {
        return $this->dataIngresso;
    }

    /**
     * @param \DateTime $dataIngresso
     */
    public function setDataIngresso($dataIngresso)
    {
        $this->dataIngresso = $dataIngresso;
    }

    /**
     * @return \DateTime
     */
    public function getDataSaida()
    {
        return $this->dataSaida;
    }

    /**
     * @param \DateTime $dataSaida
     */
    public function setDataSaida($dataSaida)
    {
        $this->dataSaida = $dataSaida;
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
    public function getNomeCurso()
    {
        return $this->nomeCurso;
    }

    /**
     * @param string $nomeCurso
     */
    public function setNomeCurso($nomeCurso)
    {
        $this->nomeCurso = $nomeCurso;
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

    public function getPessoaLdap($pessoaLdap){

        $pessoaLdap->setDepartmentNumber(array(trim($this->getCodEstruturadoExercicio())));
//        $pessoaLdap->setDisplayName(trim(iconv('ISO-8859-1', 'UTF-8', $this->getNome())));
        $pessoaLdap->setDisplayName(trim($this->getNomeSemAcento()));
        $pessoaLdap->setSchacDateOfBirth($this->getDataNascimento());
        switch (trim($this->getNomeCampus())){
            case 'Araguaina':
                $pessoaLdap->setCampus('Araguaína');
                break;
            case 'Tocantinopolis':
                $pessoaLdap->setCampus('Tocantinópolis');
                break;
            default:
                $pessoaLdap->setCampus(trim($this->getNomeCampus()));
        }
        $pessoaLdap->setIdPessoa($this->getIdPessoa());
        $pessoaLdap->setIdOrigem($this->getIdAluno());
        $pessoaLdap->setTipoOrigemItem(11);
        $pessoaLdap->setGivenName(trim($this->getNomePessoa()));
        if ($this->getSexo() == 'M') {
            $pessoaLdap->setSchacGender(1);
        } elseif ($this->getSexo() == 'F') {
            $pessoaLdap->setSchacGender(2);
        } else {
            $pessoaLdap->setSchacGender(9);
        }
        $pessoaLdap->setAluno(1);
        $matricula = trim($this->getMatricula());
        if ($matricula != null && ($pessoaLdap->getMatricula() == null || !in_array($matricula,  array_map('trim',$pessoaLdap->getMatricula())))) {
            $pessoaLdap->addMatricula($matricula);
        }
        return $pessoaLdap;
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
    public function getNome()
    {
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
     * @return string
     */
    public function getNomeCampus()
    {
//        return iconv('ISO-8859-1', 'UTF-8', $this->nomeCampus);
        return $this->nomeCampus;
    }

    /**
     * @param string $nomeCampus
     */
    public function setNomeCampus($nomeCampus)
    {
        $this->nomeCampus = $nomeCampus;
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
    public function getIdAluno()
    {
        return $this->idAluno;
    }

    /**
     * @param int $idAluno
     */
    public function setIdAluno($idAluno)
    {
        $this->idAluno = $idAluno;
    }

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


}