<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 28/06/16
 * Time: 10:44
 */

namespace UFT\SluBundle\Util;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Ldap\Ldap;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Entity\SieAutorizacoes;
use UFT\SluBundle\Entity\SieEndereco;
use UFT\SluBundle\Entity\SieUsuario;
use UFT\SluBundle\Entity\SieUsuarioGrupo;
use UFT\SluBundle\Entity\SIP\SipUsuario;

class Sincronizacao
{
    protected $em;
    protected $container;
    protected $convertor;
    protected $sipEm;

    public function __construct(EntityManager $entityManager, ContainerInterface $container, Convertores $conversor)
    {
        $this->em = $entityManager;
        $this->container = $container;
        $this->convertor = $conversor;
        $this->sipEm = $this->container->get('doctrine.orm.sip_entity_manager');
    }

    protected function addFlash($type, $message)
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    public function contatoSIE($entity)
    {
        if (($entity->getAluno() != NULL) AND ($entity->getAluno() > 0)) {
            $alunos = $this->em->getRepository('SluBundle:SieAluno')->createQueryBuilder('a')
                ->where('a.cpf = :cpf')
                ->setParameter('cpf', $this->convertor->formataCPF(trim($entity->getBrPersonCPF())))
                ->orderBy('a.dataIngresso', 'DESC')
                ->getQuery()->getResult();
            if (!empty($alunos)) {
                $dadosSie = $alunos[0];
                $entity->SetIdOrigem($dadosSie->getIdAluno());
                $entity->SetTipoOrigemItem(11);
            }
        }

        if (($entity->getFuncionario() != NULL) AND ($entity->getFuncionario() > 0)) {
            $servidores = $this->em->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
                ->where('s.cpf = :cpf')
                ->andWhere('s.idCargo <> :cargo1')
                ->andWhere('s.idCargo <> :cargo2')
                ->andWhere('s.idCargo <> :cargo3')
                ->andWhere('s.idSituacao <> :idSituacao')
                ->setParameter('cpf', $this->convertor->formataCPF(trim($entity->getBrPersonCPF())))
                ->setParameter('cargo1', 61)
                ->setParameter('cargo2', 62)
                ->setParameter('cargo3', 733)
                ->setParameter('idSituacao', 5)
                ->orderBy('s.dataPosse', 'DESC')
                ->getQuery()->getResult();
            if (!empty($servidores)) {
                $dadosSie = $servidores[0];
                $entity->setIdOrigem($dadosSie->getIdPessoa());
                $entity->setTipoOrigemItem(2);
            }
        }

        if (($entity->getProfessor() != NULL) AND ($entity->getProfessor() > 0)) {
            $professores = $this->em->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
                ->where('s.cpf = :cpf')
                ->andWhere('s.idCargo = :cargo1 OR s.idCargo = :cargo2 OR s.idCargo = :cargo3')
                ->andWhere('s.idSituacao <> :idSituacao')
                ->setParameter('cpf', $this->convertor->formataCPF(trim($entity->getBrPersonCPF())))
                ->setParameter('cargo1', 61)
                ->setParameter('cargo2', 62)
                ->setParameter('cargo3', 733)
                ->setParameter('idSituacao', 5)
                ->orderBy('s.dataPosse', 'DESC')
                ->getQuery()->getResult();
            if (!empty($professores)) {
                $dadosSie = $professores[0];
                $entity->setIdOrigem($dadosSie->getIdPessoa());
                $entity->setTipoOrigemItem(2);
            }
        }

        if($entity->getIdOrigem() == NULL){
            return null;
        }
        return $entity;
    }


    public function atualizaContato($entity)
    {
        if ($entity != null) {
            $repoEnderecos = $this->em->getRepository('SluBundle:SieEndereco')->createQueryBuilder('e');
            $convertor = $this->container->get('uft.convertores');
            $telefones = array();

            foreach ($entity->getTelephoneNumber() as $item) {
                $telefones[] = $convertor->explodeTelefone($item);
//                dump($telefones);
            }
            $enderecos = $repoEnderecos
                ->andWhere('e.idOrigem = :id')
                ->andWhere('e.tipoOrigemItem = :tipo')
                ->andWhere('e.tipoEndItem = :tipoEnd')
                ->setParameter('id', $entity->getIdOrigem())
                ->setParameter('tipo', $entity->getTipoOrigemItem())
                ->setParameter('tipoEnd', 5)
                ->getQuery()
                ->getResult();
            $emailsOrdenados = $this->ordenaEmail($entity);

            if (empty($enderecos)) {
                $endereco = new SieEndereco();
                $endereco->setIdOrigem($entity->getIdOrigem());
                $endereco->setTipoOrigemItem($entity->getTipoOrigemItem());
                $endereco->setEmailInstitucional($emailsOrdenados[0]);
                $endereco->setEmailPessoal($emailsOrdenados[1]);
                if (!empty($telefones)) {
                    if (isset($telefones[0]['ddi'])) {
                        $endereco->setDdiCelular($telefones[0]['ddi']);
                    }
                    $endereco->setDddCelular($telefones[0]['ddd']);
                    $endereco->setFoneCelular($telefones[0]['fone']);
                }
                if (count($telefones) > 1) {
                    if (isset($telefones[1]['ddi'])) {
                        $endereco->setDdiCelular($telefones[1]['ddi']);
                    }
                    $endereco->setDddResidencial($telefones[1]['ddd']);
                    $endereco->setFoneResidencial($telefones[1]['fone']);
                }
                $this->em->persist($endereco);
                $this->em->flush();
            } else {
                $endereco = $enderecos[0];
                $endereco->setEmailInstitucional($emailsOrdenados[0]);
                $endereco->setEmailPessoal($emailsOrdenados[1]);
                if (!empty($telefones)) {
                    if (isset($telefones[0]['ddi'])) {
                        $endereco->setDdiCelular($telefones[0]['ddi']);
                    }
                    $endereco->setDddCelular($telefones[0]['ddd']);
                    $endereco->setFoneCelular($telefones[0]['fone']);
                }
                if (count($telefones) > 1) {
                    if (isset($telefones[1]['ddi'])) {
                        $endereco->setDdiCelular($telefones[1]['ddi']);
                    }
                    $endereco->setDddResidencial($telefones[1]['ddd']);
                    $endereco->setFoneResidencial($telefones[1]['fone']);
                }

                $endereco->setConcorrencia($endereco->getConcorrencia() + 1);
                $this->em->persist($endereco);
                $this->em->flush();
            }
        }
    }

    public function ordenaEmail($person){

        $emailsOrdenado[] = [];
        $emailsOrdenado[0] = $person->getMail()[0];
        $emailsOrdenado[1] = $person->getPostalAddress();

        return $emailsOrdenado;
    }

    public function getUFTEmail($emails){

        $lastEmail = '';
        foreach ($emails as $email){
            if (strpos($email,'@uft.edu.br') !== false ) {
                return $email;
            }
            $lastEmail = $email;
        }
        return $lastEmail;
    }

    /**
     * O parâmetro $chamadaPorComando é utilizado para defirnir se a função está sendo chamada por um comando, evitando
     * assim possível falha ao buscar o service request. Por padrão o parâmetro é setado como falso.
     *
     * @param PessoaLdap $person
     * @param bool $chamadaPorComando
     */
    public function sincronizar(PessoaLdap $person, $chamadaPorComando = false)
    {
        $em = $this->container->get('ldap_entity_manager');
        $dados = array();
        $convertor = $this->container->get('uft.convertores');
        $logger = $this->container->get('logger');

        if (!in_array('brPerson', $person->getObjectClass())) {
            $logger->info('UID: ' . $person->getUid() . ' Conta Antiga. Necessário fazer o recadastramento primeiro.');
            return false;
        }
        $alunos = $this->em->getRepository('SluBundle:SieAluno')->createQueryBuilder('a')
            ->where('a.cpf = :cpf')
            ->setParameter('cpf', $convertor->formataCPF(trim($person->getBrPersonCPF())))
            ->orderBy('a.dataIngresso', 'DESC')
            ->getQuery()->getResult();
        $professores = $this->em->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
            ->where('s.cpf = :cpf')
            ->andWhere('s.idCargo = :cargo1 OR s.idCargo = :cargo2 OR s.idCargo = :cargo3')
            ->andWhere('s.idSituacao <> :idSituacao')
            ->setParameter('cpf', $convertor->formataCPF(trim($person->getBrPersonCPF())))
            ->setParameter('cargo1', 61)
            ->setParameter('cargo2', 62)
            ->setParameter('cargo3', 733)
            ->setParameter('idSituacao', 5)
            ->orderBy('s.dataPosse', 'DESC')
            ->getQuery()->getResult();
        $servidores = $this->em->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
            ->where('s.cpf = :cpf')
            ->andWhere('s.idCargo <> :cargo1')
            ->andWhere('s.idCargo <> :cargo2')
            ->andWhere('s.idCargo <> :cargo3')
            ->andWhere('s.idSituacao <> :idSituacao')
            ->setParameter('cpf', $convertor->formataCPF(trim($person->getBrPersonCPF())))
            ->setParameter('cargo1', 61)
            ->setParameter('cargo2', 62)
            ->setParameter('cargo3', 733)
            ->setParameter('idSituacao', 5)
            ->orderBy('s.dataPosse', 'DESC')
            ->getQuery()->getResult();

        if (empty($alunos) && empty($professores) && empty($servidores)) {
            $logger->info('UID: ' . $person->getUid() . ' Nenhum vínculo encontrado para o CPF desta conta.');
            return false;
        }
        if (!empty($alunos)) {
            $aluno = $alunos[0];
            $dados['sexo'] = $aluno->getSexo();
            $person->setAluno(1);
            $person = $this->setObjetoSincronizacao($aluno, $person);
        }
        if (!empty($professores)) {
            $professor = $professores[0];
            $dados['sexo'] = $professor->getSexo();
            $person->setProfessor(1);
            $person->setIdDocente($professor->getIdDocente());
            $person = $this->setObjetoSincronizacao($professor, $person);
        }
        if (!empty($servidores)) {
            $servidor = $servidores[0];
            $dados['sexo'] = $servidor->getSexo();
            $person->setFuncionario(1);

            if (strpos(trim($servidor->getUnidadeOficial()), ' ') === FALSE) {
                $person->setCampus(trim($servidor->getUnidadeOficial()));
            }
            $person = $this->setObjetoSincronizacao($servidor, $person);
        }
        if ($dados['sexo'] == 'M') {
            $person->setSchacGender(1);
        } elseif ($dados['sexo'] == 'F') {
            $person->setSchacGender(2);
        } else {
            $person->setSchacGender(9);
        }

        $person->ordenaMail();

        try {
            if ($chamadaPorComando){
                $em->persist($person, true, false);
            }else{
                $em->persist($person);
            }
            $em->flush();
            //export samba4
            $this->exportSamba4($person);
            return true;
        } catch (ContextErrorException $e) {
            $logger->error('UID: ' . $person->getUid() . ' Erro ao sincronizar a conta com o SIE.');
            return false;
        }

    }

    public function setObjetoSincronizacao($origem, $destino)
    {
        $convertor = $this->container->get('uft.convertores');
        $telefone = $convertor->formataTelefoneInternacional($origem->getTelefone());
        if ($telefone != null && ($destino->getTelephoneNumber() == null || !in_array($telefone, $destino->getTelephoneNumber()))) {
            $destino->addTelephoneNumber($telefone);
        }
        $destino->setIdPessoa($origem->getIdPessoa());
        $destino->setSchacDateOfBirth($origem->getDatanascimento()->format('dmY'));
        if ($destino->getMatricula() == null || !in_array(trim($origem->getMatricula()), $destino->getMatricula())) {
            $destino->addMatricula(trim($origem->getMatricula()));
        }
        $origem->setNome(trim($origem->getNome()));
        $pos_espaco = strpos($origem->getNome(), ' ');// perceba que há um espaço aqui
        $primeiro_nome = substr($origem->getNome(), 0, $pos_espaco);
        if (!$pos_espaco) {
            $resto_nome = $origem->getNome();

        } else {
            $resto_nome = substr($origem->getNome(), $pos_espaco + 1, strlen($origem->getNome()));
        }

        $destino->setCn(array($primeiro_nome, $origem->getNome()));
        $destino->setSn(array($resto_nome));
        $destino->setGecos($origem->getNome());
        $destino->setDepartmentNumber(array(trim($origem->getCodEstruturadoExercicio())));

        return $destino;
    }

    /**
     * O parâmetro $chamadaPorComando é utilizado para defirnir se a função está sendo chamada por um comando, evitando
     * assim possível falha ao buscar o service request. Por padrão o parâmetro é setado como falso.
     *
     * Caso a permissão tiver sido criada a função retorná 1, caso seja um caso de update a função 2, se nada for feito
     * a função retornará 0.
     * O retorno é utilizado para estatística ao utilizar o comando uft:ldap:sincronizar.
     *
     * @param PessoaLdap $entidade
     * @param bool $chamadaPorComando
     *
     * @return integer
     */
    public function verificarUsuarioSie(PessoaLdap $entidade, $chamadaPorComando = false)
    {
        $this->em->clear();
        $conn = $this->em->getConnection();
        $usuarios = $this->em->getRepository('SluBundle:SieUsuario')->createQueryBuilder('s')
            ->where('s.idPessoa = :idpessoa')
            ->andWhere('trim(s.login) = :cpf')
            ->andWhere('s.situacaoUsuario = \'A\'')
            ->setParameter('cpf', trim($entidade->getBrPersonCPF()))
            ->setParameter('idpessoa', $entidade->getIdPessoa())
            ->getQuery()->getResult();
        $retorno = 0;
        if (empty($usuarios) || $usuarios == null) { // se não houver usuário cadastrado
            $conn->beginTransaction();
            try {
                $usuario = new SieUsuario();
                $usuario->constroiUsuarioPorPessoa($entidade, $this->container->get('request')->getClientIp());
                $this->em->persist($usuario);
                $this->em->flush();

                $aplicacoesAluno = $usuario->getArrayAplicacoesAlunos();
                $aplicacoesProfessor = $usuario->getArrayAplicacoesProfessores();
                if ($entidade->getAluno() == 1) {
                    $grupo = new SieUsuarioGrupo();
                    $grupo->setIdUsuario($usuario->getIdUsuario());
                    $grupo->setIdgrupo(93); //grupo portal do aluno
                    $grupo->setDataAlteracao(new \DateTime());
                    $grupo->setHoraAlteracao(new \DateTime());
                    $grupo->setEnderecoFisico($this->container->get('request')->getClientIp());
                    $this->em->persist($grupo);
                    $this->em->flush();

                    foreach ($aplicacoesAluno as $aplicacaoAluno) {
                        $autorizacao = new SieAutorizacoes();
                        $autorizacao->setIdUsuario($usuario->getIdUsuario());
                        $autorizacao->setIdAplicacao($aplicacaoAluno);
                        $autorizacao->setDataAlteracao(new \DateTime());
                        $autorizacao->setHoraAlteracao(new \DateTime());
                        $autorizacao->setEnderecoFisico($this->container->get('request')->getClientIp());
                        $this->em->persist($autorizacao);
                    }
                    $aplicacoesProfessor = array_diff($aplicacoesProfessor,$aplicacoesAluno);
                    $this->em->flush();
                }
                if ($entidade->getProfessor() == 1) {
                    $grupo = new SieUsuarioGrupo();
                    $grupo->setIdUsuario($usuario->getIdUsuario());
                    $grupo->setIdgrupo(223); //grupo portal do professor
                    $grupo->setDataAlteracao(new \DateTime());
                    $grupo->setHoraAlteracao(new \DateTime());
                    $grupo->setEnderecoFisico($this->container->get('request')->getClientIp());
                    $this->em->persist($grupo);
                    $this->em->flush();
                    foreach ($aplicacoesProfessor as $aplicacaoProfessor) {
                        $autorizacao = new SieAutorizacoes();
                        $autorizacao->setIdUsuario($usuario->getIdUsuario());
                        $autorizacao->setIdAplicacao($aplicacaoProfessor);
                        $autorizacao->setDataAlteracao(new \DateTime());
                        $autorizacao->setHoraAlteracao(new \DateTime());
                        $autorizacao->setEnderecoFisico($this->container->get('request')->getClientIp());
                        $this->em->persist($autorizacao);
                    }
                    $this->em->flush();
                }
                $conn->commit();
                $retorno = 1;
            } catch (\Exception $e) {
                $conn->rollBack();
            }
        } else { // Caso já exista usuário cadastrado no sistema
            try {

                $conn->beginTransaction();
                if ($entidade->getAluno() == 1) {
                    $grupoUsuarioPortalAluno = $this->em->getRepository('SluBundle:SieUsuarioGrupo')->createQueryBuilder('g')
                        ->where('g.idUsuario = :idUsuario')
                        ->andWhere('g.idgrupo = 93')
                        ->setParameter('idUsuario', $usuarios[0]->getIdUsuario())
                        ->getQuery()->getResult();

                    if (empty($grupoUsuarioPortalAluno)) {
                        $grupo = new SieUsuarioGrupo();
                        $grupo->setIdUsuario($usuarios[0]->getIdUsuario());
                        $grupo->setIdgrupo(93); //grupo portal do aluno
                        $grupo->setDataAlteracao(new \DateTime());
                        $grupo->setHoraAlteracao(new \DateTime());
                        if (!$chamadaPorComando){
                            $grupo->setEnderecoFisico($this->container->get('request')->getClientIp());
                        }
                        $this->em->persist($grupo);
                        $this->em->flush();
                        $retorno = 2;
                    }


                    $autorizacoesPortalAluno = $this->em->getRepository('SluBundle:SieAutorizacoes')->createQueryBuilder('a')
                        ->select('a.idAplicacao')
                        ->where('a.idUsuario = :idUsuario')
                        ->andWhere('a.idAplicacao IN (:array)')
                        ->setParameter('idUsuario', $usuarios[0]->getIdUsuario())
                        ->setParameter('array', $usuarios[0]->getArrayAplicacoesAlunos())
                        ->getQuery()->getResult();

                    if (!empty($autorizacoesPortalAluno)) {
                        $idaplicacoes = array_column($autorizacoesPortalAluno, "idAplicacao");
                    } else {
                        $idaplicacoes = [];
                    }
                    $idAplicacoesPendentes = array_diff($usuarios[0]->getArrayAplicacoesAlunos(), $idaplicacoes);
                    foreach ($idAplicacoesPendentes as $aplicacaoAluno) {
                        $autorizacao = new SieAutorizacoes();
                        $autorizacao->setIdUsuario($usuarios[0]->getIdUsuario());
                        $autorizacao->setIdAplicacao($aplicacaoAluno);
                        $autorizacao->setDataAlteracao(new \DateTime());
                        $autorizacao->setHoraAlteracao(new \DateTime());
                        if (!$chamadaPorComando){
                            $autorizacao->setEnderecoFisico($this->container->get('request')->getClientIp());
                        }
                        $this->em->persist($autorizacao);
                    }
                    $this->em->flush();
                    $retorno = 2;
                }

                if ($entidade->getProfessor() == 1) {
                    $grupoUsuarioPortalProfessor = $this->em->getRepository('SluBundle:SieUsuarioGrupo')->createQueryBuilder('g')
                        ->where('g.idUsuario = :idUsuario')
                        ->andWhere('g.idgrupo = 223')
                        ->setParameter('idUsuario', $usuarios[0]->getIdUsuario())
                        ->getQuery()->getResult();


                    if (empty($grupoUsuarioPortalProfessor)) {

                        $grupo = new SieUsuarioGrupo();
                        $grupo->setIdUsuario($usuarios[0]->getIdUsuario());
                        $grupo->setIdgrupo(223); //grupo portal do aluno
                        $grupo->setDataAlteracao(new \DateTime());
                        $grupo->setHoraAlteracao(new \DateTime());
                        if (!$chamadaPorComando){
                            $grupo->setEnderecoFisico($this->container->get('request')->getClientIp());
                        }
                        $this->em->persist($grupo);
                        $this->em->flush();
                        $retorno = 2;
                    }

                    $autorizacoesPortalProfessor = $this->em->getRepository('SluBundle:SieAutorizacoes')->createQueryBuilder('a')
                        ->select('a.idAplicacao')
                        ->where('a.idUsuario = :idUsuario')
                        ->andWhere('a.idAplicacao IN (:array)')
                        ->setParameter('idUsuario', $usuarios[0]->getIdUsuario())
                        ->setParameter('array', $usuarios[0]->getArrayAplicacoesProfessores())
                        ->getQuery()->getResult();

                    if (!empty($autorizacoesPortalProfessor)) {
                        $idaplicacoes = array_column($autorizacoesPortalProfessor, "idAplicacao");
                    } else {
                        $idaplicacoes = [];
                    }
                    $idAplicacoesPendentes = array_diff($usuarios[0]->getArrayAplicacoesProfessores(), $idaplicacoes);
                    foreach ($idAplicacoesPendentes as $aplicacaoAluno) {
                        $autorizacao = new SieAutorizacoes();
                        $autorizacao->setIdUsuario($usuarios[0]->getIdUsuario());
                        $autorizacao->setIdAplicacao($aplicacaoAluno);
                        $autorizacao->setDataAlteracao(new \DateTime());
                        $autorizacao->setHoraAlteracao(new \DateTime());
                        if (!$chamadaPorComando){
                            $autorizacao->setEnderecoFisico($this->container->get('request')->getClientIp());
                        }
                        $this->em->persist($autorizacao);
                    }
                    $this->em->flush();
                    $retorno = 2;
                }
                $conn->commit();
            } catch (\Exception $e) {
                $conn->rollBack();
                echo ($e->getMessage());
                die();
            }
        }//final do ELSE
        return $retorno;
    }


    /**
     * Funções forenecidas por jonhleno para exportar registro do SLU para o SAMBA4
     * @param $pessoaLdap
     * @return bool
     *
     */
    public function exportSamba4($pessoaLdap)
    {
        $this->exportNewAd($pessoaLdap);
        if(!$pessoaLdap->getAlteraSenha()){
            return false;
        }


        $vinculo = 0;
        if( $pessoaLdap->getAluno()!= 0){
            $vinculo = 'aluno';
        }
        if( $pessoaLdap->getFuncionario()!= 0){
            if($pessoaLdap->getFuncionario() == 4){
                $vinculo = "estagiario";
            }else{
                $vinculo = 'tecnico';
            }
        }
        if( $pessoaLdap->getProfessor()!= 0){
            $vinculo = 'professor';
        }


        $info['objectClass'][0] = "user";
        $info['objectClass'][1] = "top";
        $info['objectClass'][2] = "person";
        $info['objectClass'][3] = "organizationalPerson";
        $info['objectClass'][4] = "inetOrgPerson";
        $info["cn"] = $pessoaLdap->getUid();
        $info["sAMAccountName"] = $pessoaLdap->getUid();
        $displayName = explode(" ", $pessoaLdap->getGecos());
        $info["displayName"] =  $displayName[0];
        $info["userAccountControl"] =  512;
        $info["title"] =  $vinculo;
        $info["givenName"] = $pessoaLdap->getGecos();
        $info["campus"] = is_array($pessoaLdap->getCampus())?$pessoaLdap->getCampus()[0]:$pessoaLdap->getCampus();
        $info["userPrincipalName"] = $pessoaLdap->getUid().'@uft.edu.br';
        $info["mail"] = $pessoaLdap->getUid().'@uft.edu.br';
        $info["unicodePwd"] =  is_array($pessoaLdap->getUserPassword())?$pessoaLdap->getUserPassword()[0]:$pessoaLdap->getUserPassword();
        return $this->sync($info);

    }

    /**
     * Funções forenecidas por jonhleno para exportar registro do SLU para o novo AD
     * @param $pessoaLdap
     * @return bool
     */
    public function exportNewAd($pessoaLdap)
    {

        $ldap = "ldaps://ad.uft.edu.br";
        $port = 636;
        $usr = "cn=appslu,cn=Users,dc=ad,dc=uft,dc=edu,dc=br";
        $pwd = "S#22#11@XyQOiRTMa#10";
        $ds = ldap_connect($ldap,$port);
        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
        $ldapbind = false;

        if(ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3))
            if(ldap_set_option($ds, LDAP_OPT_REFERRALS, 0))
                        $ldapbind = @ldap_bind($ds, $usr, $pwd);

        if($ldapbind){

            $info = array();

            $info['objectClass'][0] = "user";
            $info['objectClass'][1] = "top";
            $info['objectClass'][2] = "person";
            $info['objectClass'][3] = "organizationalPerson";
            $info["instanceType"] = 4;
            $info["objectCategory"] = "CN=Person,CN=Schema,CN=Configuration,DC=ad,DC=uft,DC=edu,DC=br";

            $info["cn"] = $pessoaLdap->getUid();
            $info["sn"] = $pessoaLdap->getSn();
            $info["sAMAccountName"] = $pessoaLdap->getUid();
            $displayName = explode(" ", $pessoaLdap->getGecos());
            $info["displayName"] =  $displayName[0];
            $info["givenName"] = $pessoaLdap->getGecos();

            $info["lockoutTime"] = "0";
            $info["userAccountControl"] = 66048;
            $info["userPrincipalName"] = $pessoaLdap->getUid().'@ad.uft.edu.br';

            $pass_enc =  is_array($pessoaLdap->getUserPassword())?$pessoaLdap->getUserPassword()[0]:$pessoaLdap->getUserPassword();

            $newPassw = iconv("UTF-8", "UTF-16LE", "\"".$pass_enc."\"");
            $info['unicodePwd'] = $newPassw;

            $dn = "CN=Users,DC=ad,DC=uft,DC=edu,DC=br";

            $filter="(userPrincipalName={$info["userPrincipalName"]}*)";
            $justthese = array("cn", "sn", "givenName");

            $sr=@ldap_search($ds, $dn, $filter, $justthese);

            $ldapEntry = @ldap_get_entries($ds, $sr);
            $user = 'CN='.$info["cn"].',CN=Users,DC=ad,DC=uft,DC=edu,DC=br';

            if($ldapEntry["count"]) {

                $modify = array();
                $modify['unicodePwd'] = $info['unicodePwd'];
                if($ldapbind = ldap_modify($ds, $user, $modify)){
                    echo '{"sucesso":{"text":"Usuário modificado com sucesso ao AD!"}}';
                    return true;

                }else{
                    echo '{"erro":{"text":"Erro ao sincronizar usuário!"}, "info": "AD01"}';
                }

            } else {

                if($ldapbind = @ldap_add($ds, $user, $info)){
                    echo '{"sucesso":{"text":"Usuário inserido com sucesso ao AD!"}}';

                    //caso queira adicionar o usuário a um grupo
                    //$group = 'CN=remoto,CN=Users,DC=uftnet,DC=uft,DC=edu,DC=br';
                    //$entry['member'] = $user;
                    //if(@ldap_mod_add($ds, $group, $entry)){
                    //   echo '{"sucesso":{"text":"Usuário inserido no grupo com sucesso!"}}';
                    //}else{
                    //    echo '{"erro":{"text":"Erro ao inserir usuário no grupo!"}}';
                    //}
                    return true;

                }else{
                    echo '{"erro":{"text":"Erro ao sincronizar usuário!"}, "info": "AD02"}';
                }

            }

            @ldap_close($ds);
        }
        else{
            echo '{"erro":{"text":"AD03 - '.$ldapbind.'"}}';
        }

        return false;

    }

    /**
     * Funções forenecidas por jonhleno para exportar registro do SLU para o SAMBA4
     * @param $info
     * @return bool
     *
     */
    function sync($info) {
        $certificado = $this->container->get('kernel')->getRootDir().'/../src/UFT/UserBundle/Util/Ldap/dominio.pem';

        $campus = $info['campus'];
        unset($info['campus']);
        $result = false;
        if(!file_exists($certificado)){
            return $result;
        }
        putenv("LDAPTLS_CACERT={$certificado}"); //pode usar o export do próprio SO para não precisar fazer essa chamada toda vez
        $ldaphost = 'ldap://sambaad.uftnet.uft.edu.br';
        $ldapport = 389;

        $ldaprdn  = 'CN=Administrator,CN=Users,DC=uftnet,DC=uft,DC=edu,DC=br';
        $ldappass = '53!!4b0w.u.0';
        $ldapbind = false;

        $ldapconn = ldap_connect($ldaphost, $ldapport);

        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
        if(ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3))
            if(ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0))
                if(ldap_start_tls($ldapconn))
                    $ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);


        if($ldapbind){
            $pass = "\"" . $info['unicodePwd'] . "\"";
            $newPassw = mb_convert_encoding($pass, "UTF-16LE");
            $info['unicodePwd'] = $newPassw;

            $baseDn = 'dc=uftnet,dc=uft,dc=edu,dc=br';
            $user = 'cn='.$info["cn"].',ou=Users,ou='.$campus.',dc=uftnet,dc=uft,dc=edu,dc=br';

            $person =  $info["cn"];
            $filter="(cn=$person*)";
            $sr=ldap_search($ldapconn, $baseDn, $filter);
            $entry = ldap_get_entries($ldapconn, $sr);


            if($entry['count']==0){
                if($ldapbind = @ldap_add($ldapconn, $user, $info)){
                    echo '{"sucesso":{"text":"Usuário inserido com sucesso!"}}';
                    $result = true;
                }
                else{
                    echo '{"erro":{"text":"Erro ao inserir usuário!"}}';
                }
            }else{
                $modify['unicodePwd'] = $info['unicodePwd'];
                if($ldapbind = ldap_mod_replace($ldapconn, $user, $modify)){
                    echo '{"sucesso":{"text":"Usuário atualizado com sucesso!"}}';
                    $result = true;
                }
                else{
                    echo '{"erro":{"text":"Erro ao atualizar usuário!"}}';
                }
            }

            @ldap_close($ldapconn);
        }
        else{
            echo '{"erro":{"text":"'.$ldapbind.'"}}';
        }

        return $result;
    }


    public function rollBackSipUsuario(SipUsuario $usuario) {
        $connection = $this->sipEm->getConnection();
        $tableClass = $this->sipEm->getClassMetadata(get_class($usuario));
        $tableName = $tableClass->getTableName();
        $updateIdsql = "UPDATE  infra_sequencia SET num_atual = num_atual-1  where nome_tabela = '$tableName'";
        $stmt2 = $connection->prepare($updateIdsql);
        $stmt2->execute();
    }

    public function getSipMatricula($matriculas){
        if(!is_array($matriculas)){
            return $matriculas;
        }
        $soFuncionario = array_filter($matriculas, function ($mat) { return strlen($mat) == 7; });
        if(empty($matriculas)){
            return end($matriculas);
        }
        return end($soFuncionario);
    }

    public function temUsuarioSei(PessoaLdap $person) {
        $sipUser = $this->sipEm->getRepository(SipUsuario::class)->createQueryBuilder('u')
            ->where('u.sigla = :username')
            ->setParameter('username',$person->getUid())
            ->getQuery()->getOneOrNullResult();
        return $sipUser != null;
    }

    /**
     * O parâmetro $chamadaPorComando é utilizado para defirnir se a função está sendo chamada por um comando, evitando
     * assim possível falha ao buscar o service request. Por padrão o parâmetro é setado como falso.
     *
     * @param PessoaLdap $person
     * @param bool $chamadaPorComando
     * @return false|void
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function criarSei(PessoaLdap $person, $chamadaPorComando = false) {
        $logger = $this->container->get('logger');

        if($this->temUsuarioSei($person)){
            $message = 'UID: ' . $person->getUid() . ' já cadastrado no SEI.';
            $logger->info($message);
            if (!$chamadaPorComando) $this->addFlash('info', $message);
            return false;
        }

        $usuario = new SipUsuario();

        try {
            $servidor = $this->em->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
                ->where('s.cpf = :cpf')
                ->andWhere('s.idSituacao <> :idSituacao')
                ->setParameter('cpf', $this->convertor->formataCPF(trim($person->getBrPersonCPF())))
                ->setParameter('idSituacao', 5)
                ->setMaxResults(1)
                ->orderBy('s.dataPosse', 'DESC')
                ->getQuery()->getOneOrNullResult();

            if(!is_null($servidor)){
                $usuario->setNome(trim($servidor->getNome()));
                $usuario->setNomeRegistroCivil(trim($servidor->getNomePessoa()));
//                $usuario->setNomeSocial(trim($servidor->getNome()));
                $usuario->setIdOrigem(trim($servidor->getMatricula()));
            } else {
                $usuario->setNome($person->getDisplayName());
                $usuario->setNomeRegistroCivil($person->getDisplayName());
//                $usuario->setNomeSocial($person->getDisplayName());
                $usuario->setIdOrigem($this->getSipMatricula($person->getMatricula()));
            }
        } catch (\Exception $exception){
            $error = 'Falha! Houve um problema ao consultar dos dados no SIE. Tente novamente mais tarde.';
            $logger->error($error);
            $logger->error($exception->getMessage());
            if (!$chamadaPorComando) $this->addFlash('error', $error);
        }
        $usuario->setSigla($person->getUid());
        $usuario->setCpf($person->getBrPersonCPF());
        $usuario->setEmail($this->getUFTEmail($person->getMail()));

        try {
            if ($chamadaPorComando){
                $this->sipEm->persist($usuario, true, false);
            }else{
                $this->sipEm->persist($usuario);
            }
            $this->sipEm->flush();
        } catch (\Exception $exception) {

            if($exception->getPrevious()->getCode() != '23000') {
                $this->rollBackSipUsuario($usuario);
            }
            $error = 'Falha! Houve um problema ao criar seu usuário no SEI. Tente novamente mais tarde.';
            $logger->error($error);
            $logger->error($exception->getMessage());

            if (!$chamadaPorComando) $this->addFlash('error', $error);
            return false;
        }
        return true;
    }

}