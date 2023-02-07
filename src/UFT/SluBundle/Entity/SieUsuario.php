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
 * @ORM\Table(name="DBSM.USUARIOS")
 * @ORM\Entity
 */
class SieUsuario
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="UFT\SluBundle\Doctrine\CustomIdGenerator")
     * @ORM\Column(name="ID_USUARIO", type="integer")
     */
    private $idUsuario;

    /**
     * @var integer
     * @ORM\Column(name="ID_PESSOA", type="integer",  nullable=true)
     */
    private $idPessoa;


    /**
     * @var string
     * @ORM\Column(name="LOGIN", type="string", length=128)
     */
    private $login;

    /**
     * @var string
     * @ORM\Column(name="SENHA", type="string", length=128,  nullable=true)
     */
    private $senha;


    /**
     * @var string
     * @ORM\Column(name="NOME_USUARIO", type="string", length=60)
     */
    private $nomeUsuario;

    /**
     * @var string
     * @ORM\Column(name="SITUACAO_USUARIO", type="string", length=1)
     */
    private $situacaoUsuario = "A";

    /**
     * @var string
     * @ORM\Column(name="IND_SUPERUSUARIO", type="string", length=1)
     */
    private $indSuperUsuario = 'N';

    /**
     * @var string
     * @ORM\Column(name="TIPO_USUARIO", type="string", length=1)
     */
    private $tipoUsuario = 'O';

    /**
     * @var integer
     * @ORM\Column(name="MATR_SERVIDOR", type="integer",  nullable=true)
     */
    private $matriculaServidor;

    /**
     * @var string
     * @ORM\Column(name="SENHA_WEB", type="string", length=128 , nullable=true)
     */
    private $senhaWeb;

    /**
     * @var date
     *
     * @ORM\Column(name="DT_ALT_SENHA", type="date",options={"default"="CURRENT_DATE"}, nullable=true)
     */
    private $dataAlteracaoSenha;

    /**
     * @var date
     *
     * @ORM\Column(name="DT_ALT_SENHA_WEB", type="date",options={"default"="CURRENT_DATE"}, nullable=true)
     */
    private $dataAlteracaoSenhaWeb;

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
    private $horaAlteracao;

    /**
     * @var integer
     *
     * @ORM\Column(name="CONCORRENCIA", type="integer")
     *
     */
    private $concorrencia = 0;


    /**
     * @var string
     * @ORM\Column(name="KEY_URL", type="string", length=100 , nullable=true)
     */
    private $keyUrl;

    /**
     * @var string
     * @ORM\Column(name="E_MAIL", type="string", length=100 , nullable=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="ENDERECO_FISICO", type="string", length=100 , nullable=true)
     */
    private $enderecoFisico;

    /**
     * @var blob
     * @ORM\Column(name="CERTIFICADO_PFX", type="blob", length=100 , nullable=true)
     */
    private $certificadoPFX;

    /**
     * @var blob
     * @ORM\Column(name="IMG_ASSINATURA", type="blob", length=100 , nullable=true)
     */
    private $imagemAssinatura;

    /**
     * SieUsuario constructor.
     */
    public function __construct()
    {
        $this->dataAlteracao = new \DateTime();
        $this->dataAlteracaoSenha = new \DateTime();
        $this->dataAlteracaoSenhaWeb = new \DateTime();
        $this->horaAlteracao = new \DateTime();
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
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * @param string $senha
     */
    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    /**
     * @return string
     */
    public function getNomeUsuario()
    {
        return $this->nomeUsuario;
    }

    /**
     * @param string $nomeUsuario
     */
    public function setNomeUsuario($nomeUsuario)
    {
        $this->nomeUsuario = $nomeUsuario;
    }

    /**
     * @return string
     */
    public function getSituacaoUsuario()
    {
        return $this->situacaoUsuario;
    }

    /**
     * @param string $situacaoUsuario
     */
    public function setSituacaoUsuario($situacaoUsuario)
    {
        $this->situacaoUsuario = $situacaoUsuario;
    }

    /**
     * @return string
     */
    public function getIndSuperUsuario()
    {
        return $this->indSuperUsuario;
    }

    /**
     * @param string $indSuperUsuario
     */
    public function setIndSuperUsuario($indSuperUsuario)
    {
        $this->indSuperUsuario = $indSuperUsuario;
    }

    /**
     * @return string
     */
    public function getTipoUsuario()
    {
        return $this->tipoUsuario;
    }

    /**
     * @param string $tipoUsuario
     */
    public function setTipoUsuario($tipoUsuario)
    {
        $this->tipoUsuario = $tipoUsuario;
    }

    /**
     * @return int
     */
    public function getMatriculaServidor()
    {
        return $this->matriculaServidor;
    }

    /**
     * @param int $matriculaServidor
     */
    public function setMatriculaServidor($matriculaServidor)
    {
        $this->matriculaServidor = $matriculaServidor;
    }

    /**
     * @return string
     */
    public function getSenhaWeb()
    {
        return $this->senhaWeb;
    }

    /**
     * @param string $senhaWeb
     */
    public function setSenhaWeb($senhaWeb)
    {
        $this->senhaWeb = $senhaWeb;
    }

    /**
     * @return date
     */
    public function getDataAlteracaoSenha()
    {
        return $this->dataAlteracaoSenha;
    }

    /**
     * @param date $dataAlteracaoSenha
     */
    public function setDataAlteracaoSenha($dataAlteracaoSenha)
    {
        $this->dataAlteracaoSenha = $dataAlteracaoSenha;
    }

    /**
     * @return date
     */
    public function getDataAlteracaoSenhaWeb()
    {
        return $this->dataAlteracaoSenhaWeb;
    }

    /**
     * @param date $dataAlteracaoSenhaWeb
     */
    public function setDataAlteracaoSenhaWeb($dataAlteracaoSenhaWeb)
    {
        $this->dataAlteracaoSenhaWeb = $dataAlteracaoSenhaWeb;
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
    public function getKeyUrl()
    {
        return $this->keyUrl;
    }

    /**
     * @param string $keyUrl
     */
    public function setKeyUrl($keyUrl)
    {
        $this->keyUrl = $keyUrl;
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

    /**
     * @return blob
     */
    public function getCertificadoPFX()
    {
        return $this->certificadoPFX;
    }

    /**
     * @param blob $certificadoPFX
     */
    public function setCertificadoPFX($certificadoPFX)
    {
        $this->certificadoPFX = $certificadoPFX;
    }

    /**
     * @return blob
     */
    public function getImagemAssinatura()
    {
        return $this->imagemAssinatura;
    }

    /**
     * @param blob $imagemAssinatura
     */
    public function setImagemAssinatura($imagemAssinatura)
    {
        $this->imagemAssinatura = $imagemAssinatura;
    }

    public function getArrayAplicacoesAlunos()
    {
        return array(10128, 12744, 12745, 12746, 12747, 12748, 13179, 13183, 13206, 13240);
    }

    public function getArrayAplicacoesProfessores()
    {
        return array(10128, 13160, 13161, 13162, 13163);
    }


    public function constroiUsuarioPorPessoa(PessoaLdap $pessoa, $ip)
    {
        $this->idPessoa = (int) $pessoa->getIdPessoa();
        $this->login = $pessoa->getBrPersonCPF();
        $this->senha = null;
        $this->nomeUsuario = $pessoa->getDisplayName();
        $this->situacaoUsuario = "A";
        $this->email = $pessoa->getMail()[0];
        $this->enderecoFisico = $ip;
    }

}