<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\Tests\Fixture\DumbFoo;
use UFT\SluBundle\Entity\DepartamentoLdap;
use UFT\SluBundle\Entity\GrupoLdap;
use UFT\SluBundle\Entity\NewGrupoLdap;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Form\AlteraDadosUsuarioType;
use UFT\SluBundle\Form\AlteraSenhaLdapType;
use UFT\SluBundle\Form\RecadastrarType;


/**
 * Usuario controller.
 *
 * @Route("/usuario")
 */
class UsuarioController extends Controller
{


    /**
     * Mostra dados da pessoa
     *
     * @Route("/mostrar", name="mostra_usuario")
     * @Template()
     * @Security("has_role('ROLE_USUARIO_MOSTRAR')")
     */
    public function mostrarAction()
    {
        $recadastrar = false;
        $em = $this->get('ldap_entity_manager');
        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($this->getUser()->getUsername());
        $departamento = $em->getRepository(DepartamentoLdap::class)->findOneByUid($this->getUser()->getUsername());
        if(!empty($person) && $person->getProfessor()==NULL && $person->getAluno()==NULL && $person->getFuncionario()==NULL && !empty($departamento) && $departamento->getInstitucional() == 1){
            $antiga = $departamento->getObjectClass();
            $nova = $departamento->getNovasObjectClass();
        }elseif(!empty($person)){
            $antiga = $person->getObjectClass();
            $nova = $person->getNovasObjectClass();
        }else{
            $this->addFlash(
                'warning',
                'Usuário sem conta no LDAP.'
            );
            return $this->forward('SluBundle:Registrar:verificacao');
        }
        $isPadraoAntigo = array_diff($nova, $antiga);
        $isPadraoAntigo2 = array_diff($antiga, $nova);
        if ((!empty($person) || !empty($departamento)) && (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) ) {
            $recadastrar = true;
            $this->addFlash(
                'warning',
                'Conta Antiga. Necessário fazer o recadastramento primeiro.'
            );
        }

        if($recadastrar){
            return $this->render('SluBundle:Default:index.html.twig', array('recadastrar' => $recadastrar));
        }else{
            return array(
                'entity' => $person
            );
        }
    }

    /**
     * Recadastrar usuario antigo.
     *
     * @Route("/recadastramento", name="pessoaLdap_recadastrar")
     * @Method({"GET"})
     * @Template("SluBundle:Usuario:recadastrar.html.twig")
     * @Security("has_role('ROLE_USUARIO_RECADASTRAR')")
     */
    public function recadastraPessoaLdapAction(Request $request)
    {
        $em = $this->get('ldap_entity_manager');

        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($this->getUser()->getUserName());

        if (empty($person) || !($person instanceof PessoaLdap) ) {
            $this->addFlash(
                'error',
                'Usuario não encontrado! Tente novamente e se o erro persistir, procure o Secretaria Acadêmica ou GDH para regularização!'
            );
            return array(
                'entity' => $person,
            );
        }

        if (empty($person->getCpf()) && empty($person->getBrPersonCPF())) {
            $this->addFlash(
                'error',
                'Não encontramos seu CPF cadastrado, procure o GDH para regularização!'
            );

            return $this->forward('SluBundle:Default:index');
        }
        if (empty($person)) {
            $this->addFlash(
                'warning',
                'Usuario sem conta no LDAP.'
            );
            return array(
                'entity' => $person,
            );
        }

        if ($person->getBrPersonCPF() == null) {
            $person->setBrPersonCPF($person->getCPF());
        }

        //mudando campus de array para unico resigtro
        $person->setCampus(is_array($person->getCampus()) ? $person->getCampus()[0] : $person->getCampus());
        $entity = $this->buscaDadosSie($person);
        //removendo emails duplicados
        $entity->setMail(array_unique($trimmed_array = array_map('trim', $entity->getMail())));
        $entity->ordenaMail();
        if (empty($entity->getTelephoneNumber())) {
            $entity->setTelephoneNumber(array('0'));
        }
        $antiga = $entity->getObjectClass();
        $nova = $entity->getNovasObjectClass();
        $isPadraoAntigo = array_diff($nova, $antiga);
        $isPadraoAntigo2 = array_diff($antiga, $nova);


        if (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) {
            $this->addFlash(
                'warning',
                'Atualização de contas antigas!'
            );
        }

        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        if ($entity->getSchacDateOfBirth() != null && !($entity->getSchacDateOfBirth() instanceof \DateTime)) {
            $entity->setSchacDateOfBirth(\DateTime::createFromFormat('dmY', $entity->getSchacDateOfBirth()));
        }

        if ($entity->getMatricula() != null && in_array('0', $entity->getMatricula())) {
            $entity->removeMatricula('0');
        }

        $editForm = $this->createRecadastrarForm($entity);


        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Criar formulario de recadastramento.
     * @param PessoaLdap $entity The entity
     * @return \Symfony\Component\Form\Form The form
     *
     */
    private function createRecadastrarForm(PessoaLdap $entity)
    {
        $em = $this->get('ldap_entity_manager');
        $convertor = $this->get('uft.convertores');
        $emUtil = $this->get('uft.ldap.manager');
        $form = $this->createForm(RecadastrarType::class, $entity, array(
            'action' => $this->generateUrl('recadastramento_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
            'em' => $em,
            'convertor' => $convertor,
            'emUtil' => $emUtil
        ));

        return $form;
    }

    private function verificaEmail($origem, $destino)
    {
        $destino->ordenaMail();
        $possuiEmailInstitucional = !empty($destino->getMail());

        //origem SIE Destino = LDAP
        if ($origem->getEmail() != null) {
            if(!$possuiEmailInstitucional
                && strpos($origem->getEmail(), 'uft.edu') !== false
                && strpos($origem->getEmail(), '@') !== false ){
                $destino->addMail($origem->getEmail());
                $possuiEmailInstitucional = true;
            } elseif (strpos($origem->getEmail(), 'uft.edu') === false
                && strpos($origem->getEmail(), '@') !== false ) {
                $destino->setPostalAddress($origem->getEmail());
            }
        }
        if ($origem->getEmail2() != null) {
            if(!$possuiEmailInstitucional
                && strpos($origem->getEmail2(), 'uft.edu') !== false
                && strpos($origem->getEmail2(), '@') !== false ){
                $destino->addMail($origem->getEmail2());
            } elseif (strpos($origem->getEmail2(), 'uft.edu') === false
                && strpos($origem->getEmail2(), '@') !== false ) {
                $destino->setPostalAddress($origem->getEmail2());
            }
        }

        return $destino;
    }

    private function buscaDadosSie($entity)
    {
        $msg = false;


        $convertor = $this->get('uft.convertores');
        $em2 = $this->getDoctrine()->getManager('db2');

        $alunos = $em2->getRepository('SluBundle:SieAluno')->createQueryBuilder('a')
            ->where('a.cpf = :cpf')
            ->setParameter('cpf', $convertor->formataCPF(trim($entity->getBrPersonCPF())))
            ->orderBy('a.dataIngresso', 'DESC')
            ->getQuery()->getResult();
        if (!empty($alunos)) {
            $entity = $alunos[0]->getPessoaLdap($entity);
            $telefone = $convertor->formataTelefoneInternacional($alunos[0]->getTelefone());
            if ($telefone != null && ($entity->getTelephoneNumber() == null || !in_array($telefone, $entity->getTelephoneNumber()))) {
                $entity->addTelephoneNumber($telefone);
            }
            $entity = $this->verificaEmail($alunos[0], $entity);

            $msg = true;
        }
        $professores = $em2->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
            ->where('s.cpf = :cpf')
            ->andWhere('s.idCargo = :cargo1 OR s.idCargo = :cargo2 OR s.idCargo = :cargo3')
            ->andWhere('s.idSituacao <> :idSituacao')
            ->setParameter('cpf', $convertor->formataCPF(trim($entity->getBrPersonCPF())))
            ->setParameter('cargo1', 61)
            ->setParameter('cargo2', 62)
            ->setParameter('cargo3', 733)
            ->setParameter('idSituacao', 5)
            ->orderBy('s.dataPosse', 'DESC')
            ->getQuery()->getResult();
        if (!empty($professores)) {
            $entity = $professores[0]->getPessoaLdap($entity);
            $telefone = $convertor->formataTelefoneInternacional($professores[0]->getTelefone());
            if ($telefone != null && ($entity->getTelephoneNumber() == null || !in_array($telefone, $entity->getTelephoneNumber()))) {
                $entity->addTelephoneNumber($telefone);
            }

            $entity = $this->verificaEmail($professores[0], $entity);
            $msg = true;
        }
        $servidores = $em2->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
            ->where('s.cpf = :cpf')
            ->andWhere('s.idCargo <> :cargo1')
            ->andWhere('s.idCargo <> :cargo2')
            ->andWhere('s.idCargo <> :cargo3')
            ->andWhere('s.idSituacao <> :idSituacao')
            ->setParameter('cpf', $convertor->formataCPF(trim($entity->getBrPersonCPF())))
            ->setParameter('cargo1', 61)
            ->setParameter('cargo2', 62)
            ->setParameter('cargo3', 733)
            ->setParameter('idSituacao', 5)
            ->orderBy('s.dataPosse', 'DESC')
            ->getQuery()->getResult();
        if (!empty($servidores)) {
            $entity = $servidores[0]->getPessoaLdap($entity);
            $telefone = $convertor->formataTelefoneInternacional($servidores[0]->getTelefone());
            if ($telefone != null && ($entity->getTelephoneNumber() == null || !in_array($telefone, $entity->getTelephoneNumber()))) {
                $entity->addTelephoneNumber($telefone);
            }

            $entity = $this->verificaEmail($servidores[0], $entity);
            $msg = true;
        }


        if ($msg) {
            $this->addFlash(
                'success',
                'Dados importados do SIE.'
            );
        }
        return $entity;
    }

    /**
     * Atualiza os dados da conta para recadastramento.
     *
     * @Route("/update/{uid}", name="recadastramento_update")
     * @Method("POST")
     * @Template("SluBundle:Usuario:recadastrar.html.twig")
     * @Security("has_role('ROLE_USUARIO_RECADASTRAR')")
     */
    public function updateAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $emMysql = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        $user = $this->getUser();
        $logger = $this->get('logger');

        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }

        if ($entity->getFuncionario() == 4) {
            $funcionario = 4;
        }
        $antiga = $entity->getObjectClass();

        $uidAntiga = $entity->getuid();
        $nomeAntigo = $entity->getCn()[0];
        $sobrenomeAntigo = $entity->getSn()[0];

        if ($entity->getSchacDateOfBirth() != null) {
            $entity->setSchacDateOfBirth(\DateTime::createFromFormat('dmY', $entity->getSchacDateOfBirth()));
        }
        $editForm = $this->createRecadastrarForm($entity);
        $post = $request->request->get('recadastrar');

        $emailSecundario = true;
        foreach ($post['mail'] as $chave => $email) {
            if (empty($email)) {
                unset($post['mail'][$chave]);
                $emailSecundario = false;

                $this->addFlash(
                    'error',
                    'Você deve inserir um e-mail secundário para concluir o recadastramento.'
                );
            }
        }
        $entity->setmail($post['mail']);
        foreach ($post['telephoneNumber'] as $chave => $fone) {
            if (empty($fone)) {
                unset($post['telephoneNumber'][$chave]);
            }
        }
        $entity->setTelephoneNumber($post['telephoneNumber']);

        if ($request->request->get('recadastrar')['uids'] != "" && $request->request->get('recadastrar')['uid'] != "") {
            $uids = $request->request->get('recadastrar');
            unset($uids['uids']);
            $request->request->set('recadastrar', $uids);
        }
        $editForm->handleRequest($request);
        if ($editForm->isValid() && $emailSecundario) {

            $oldDn = '';
            $newDn = '';
            $emSincronizacao = $this->get('uft.sincronizacao.manager');

            $emSincronizacao->exportSamba4($entity);
            $entity->constroiObjetoLdap();
            $entity->ordenaMail();

            $nova = $entity->getNovasObjectClass();
            $uidNova = $entity->getUid();
            $isPadraoAntigo = array_diff($nova, $antiga);
            $isPadraoAntigo2 = array_diff($antiga, $nova);
            // alterando dados do usuário
            $user->setUsername($entity->getUid());
            $user->setUsernameCanonical($entity->getUid());
            $user->setEmail($entity->getMail()[0]);
            $user->setEmailCanonical($entity->getMail()[0]);
            $user->setDepartmentNumber($entity->getDepartmentNumber()[0]);
            $nomeNovo = $entity->getCn()[0];
            $sobrenomeNovo = $entity->getSn()[0];

            try {
                if (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2) || $uidNova != $uidAntiga) {

                    $oldDn = $entity->getDn();
                    $position = strpos($entity->getDn(), ",");
                    $newRdn = substr($entity->getDn(), 0, $position);
                    $newParent = 'ou=Desativados,' . $this->getParameter('ldap_basedn');
                    $newDn = $newRdn . ',' . $newParent;
                    $em->rename($oldDn, $newRdn, $newParent);
                    $entity->setDn(null);

                }
            } catch (\Exception $e) {
                $logger->error('RC01 Error: UID'.$entity->getUid().' : '.$e->getMessage());

                $this->addFlash(
                    'error',
                    'Erro ao atualizar a conta antiga: Código RC01'
                );
            }

            try {
                $entity->setMemberOf(null);
                $entity->setCreateTimestamp(null);
                $entity->setModifyTimestamp(null);

                if (isset($funcionario)) {
                    $entity->setFuncionario($funcionario);
                }

                $em->persist($entity);
                if (!($entity->getTeste())) {
                    $this->get('gearman')->doBackgroundJob('UFTSluBundleServicesGSuitWorkerService~atualizarGruposDeUsuarioGSuit', json_encode(array(
                        'uid' => $entity->getUid(),
                    )));
                }


                /* Cadastro de e-mail no DB2*/
                $emSincronizacao = $this->get('uft.sincronizacao.manager');
                $emSincronizacao->atualizaContato($emSincronizacao->contatoSIE($entity));

                /*CADASTRO DO USUARIO NO MYSQL*/
                $emMysql->persist($user);
                $emMysql->flush();


                if ($uidNova != $uidAntiga || (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2))) {
                    if ($uidNova != $uidAntiga) {
                        try {
                            if (!($entity->getTeste())) {
                                $this->get('uft.email.manager')->editarEmail($uidNova, $uidAntiga);
//                                $this->get('uft.moodle.manager')->renomearUsuario($uidNova, $uidAntiga);
                                $this->get('uft.intranet.manager')->renomearUsuario($uidNova, $uidAntiga);
                            }
                        } catch (ContextErrorException $e) {
                            $logger->error('RC02 Error: UID'.$entity->getUid().' : '.$e->getMessage());

                            $this->addFlash(
                                'error',
                                'Erro ao renomear e-mail: Código RC02'
                            );
                        }
                    }
                    try {
                        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uidAntiga, array(
                            'searchDn' => 'ou=Desativados,' . $this->getParameter('ldap_basedn')));
                        $em->deleteByDn($person->getDn());
                        return $this->redirect($this->generateUrl('mostra_usuario', array('uid' => $uidNova)));
                    } catch (ContextErrorException $e) {
                        $logger->error('RC03 Error: UID'.$entity->getUid().' : '.$e->getMessage());

                        $this->addFlash(
                            'error',
                            'Erro ao remover conta antiga com o mesmo login: Código RC03'
                        );
                    }
                }

                try {
                    if (($nomeNovo != $nomeAntigo) || ($sobrenomeNovo != $sobrenomeAntigo)) {
                        $emailManager = $this->get('uft.email.manager');
                        $emailManager->editarNome($entity->getUid(), $nomeNovo, $sobrenomeNovo);
                    }

                } catch (ContextErrorException $e) {
                    $logger->error('RC05 Error: UID'.$entity->getUid().' : '.$e->getMessage());

                    $this->addFlash(
                        'error',
                        'Erro ao alterar nome da conta: Código RC05'
                    );
                }

                if ($entity->getAluno()) {
                    $auxiliar = $em->getRepository(NewGrupoLdap::class)->findOneByCn("Alunos");
                    $auxiliar->addMember($entity);
                    $em->persist($auxiliar);
                }
                if ($entity->getProfessor()) {
                    $auxiliar = $em->getRepository(NewGrupoLdap::class)->findOneByCn("Professores");
                    $auxiliar->addMember($entity);
                    $em->persist($auxiliar);
                }
                if ($entity->getFuncionario()) {
                    $auxiliar = $em->getRepository(NewGrupoLdap::class)->findOneByCn("Técnicos");
                    $auxiliar->addMember($entity);
                    $em->persist($auxiliar);
                }

                $auxiliarCampus = $em->getRepository(NewGrupoLdap::class)->findOneByCn($entity->getCampus());

                if (!empty($auxiliarCampus)) {
                    $auxiliarCampus->addMember($entity);
                    $em->persist($auxiliarCampus);
                }

                $this->addFlash(
                    'success',
                    'Conta atualizada com sucesso!'
                );

            } catch (ContextErrorException $e) {
                $position = strpos($oldDn, ",");
                $newRdn = substr($oldDn, 0, $position);
                $newParent = substr($oldDn, $position + 1);
                $em->rename($newDn, $newRdn, $newParent);
                $logger->error('RC04 Error: UID'.$entity->getUid().' : '.$e->getMessage());

                $this->addFlash(
                    'error',
                    'Erro ao atualizar a conta: Código RC04'
                );
            }
            $emailManager = $this->get('uft.email.manager');

            if (!($entity->getTeste()) and !$emailManager->isCreated($entity->getUid())) {
                try {
                    $emailManager->criarEmail($entity);die;
                } catch (\Exception $exception){
                    $logger->error('Email Error: UID'.$entity->getUid().' : '.$exception->getMessage());
                }
            }
            return $this->redirect($this->generateUrl('mostra_usuario', array('uid' => $uid)));

        }
        if(!$emailSecundario){
            if (count($entity->getMail()) < 2) {
                $entity->addMail(' ');
            }
            $entity->ordenaMail();
            $editForm = $this->createRecadastrarForm($entity);
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Exibe formulario para atualiza senha do usuario.
     *
     * @Route("/senha/alterar", name="alterar_senha_usuario")
     * @Method({"GET"})
     * @Template("SluBundle:PessoaLdap:alterar_senha.html.twig")
     * @Security("has_role('ROLE_USUARIO_ALTERAR_SENHA')")
     */
    public function alteraSenhaLdapAction()
    {

        $em = $this->get('ldap_entity_manager');
        $uid = $this->getUser()->getUserName();

        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        if(!empty($entity)){
            $antiga = $entity->getObjectClass();
            $nova = $entity->getNovasObjectClass();
        }

        $isPadraoAntigo = array_diff($nova, $antiga);
        $isPadraoAntigo2 = array_diff($antiga, $nova);
        if ((!empty($entity) || !empty($departamento)) && (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) ) {
            return $this->redirectToRoute('index');

        }
        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }


        $editForm = $this->createForm(AlteraSenhaLdapType::class, $entity, array(
            'action' => $this->generateUrl('senhaUsuario_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Atualiza senha do usuario.
     *
     * @Route("/atualiza/{uid}", name="senhaUsuario_update")
     * @Method({"POST"})
     * @Template("SluBundle:PessoaLdap:alterar_senha.html.twig")
     * @Security("has_role('ROLE_USUARIO_ALTERAR_SENHA')")
     */
    public function atualizaSenhaLdapAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        $senhaAntiga = $request->request->get('altera_senha_ldap')['senhaAntiga'];
        $senhaNova = $request->request->get('altera_senha_ldap')['userPassword'];

        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $editForm = $this->createForm(AlteraSenhaLdapType::class, $entity, array(
            'action' => $this->generateUrl('senhaUsuario_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $ldapManager = $this->get('uft.ldap.manager');
            $emSincronizacao = $this->get('uft.sincronizacao.manager');
            $emailManager = $this->get('uft.email.manager');


            $dn = $ldapManager->dnBuilder(array('uid' => $entity->getUid()), 'ou=People,' . $this->getParameter('ldap_basedn'));

            if ($ldapManager->bind($dn, $senhaAntiga)) {

                if ($senhaAntiga == $senhaNova['first']) {
                    $this->addFlash(
                        'error',
                        'A nova senha não pode ser igual a atual.'
                    );
                } else {
                    $alterarSenha = $entity->getAlteraSenha();
                    $entity->setAlteraSenha(1);
                    $result=$emSincronizacao->exportSamba4($entity);
                    $entity->setAlteraSenha($alterarSenha);
                    $entity->setUserPassword('{CRYPT}' . crypt($entity->getUserPassword(), null));
                    $em->persist($entity);
                    $em->flush();
//                    if($emailManager->isSuspenso($entity->getUid())){
//                        try{
//                            $emailManager->reativarEmail($entity->getUid());
//                        }catch (\Exception $e)
//                        {
//                            $this->addFlash(
//                                'error',
//                                'Falha na conexão com o google.'
//                            );
//
//                        }
//                    }

                    $this->addFlash(
                        'success',
                        'Senha atualizada com sucesso.'
                    );
                    $url = $this->generateUrl('mostra_usuario', array('uid' => $entity->getUid()));
                    return new RedirectResponse($url);
//                    return $this->redirect($this->generateUrl('mostra_usuario', array('uid' => $entity->getUid())));
                }

            } else {
                $this->addFlash(
                    'error',
                    'Senha atual incorreta.'
                );
            }
        }
        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Exibe formulario para alteração de dados de contato do usuário.
     *
     * @Route("/edicao/{uid}/editar", name="edita_dados_usuario")
     * @Method({"GET"})
     * @Template("SluBundle:Usuario:editar.html.twig")
     * @Security("has_role('ROLE_USUARIO_EDITAR_BASICO')")
     */
    public function editaDadosAction($uid)
    {

        $em = $this->get('ldap_entity_manager');

        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        $entity->ordenaMail();


        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }

        if (empty($entity->getTelephoneNumber())) {
            $entity->setTelephoneNumber(array(' '));
        }

        $editForm = $this->criarFormularioEdicaoDados($entity);

        return array(
            'entity' => $entity,
            'form' => $editForm->createView()
        );
    }

    /**
     * Cria formulario de edição de dados de contato do usuario
     *
     * @param PessoaLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function criarFormularioEdicaoDados(PessoaLdap $entity)
    {
        $form = $this->createForm(AlteraDadosUsuarioType::class, $entity, array(
            'action' => $this->generateUrl('atualiza_contato_usuario', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Atualiza dados de contato do usuário
     *
     * @Route("/atualiza_dados/{uid}", name="atualiza_contato_usuario")
     * @Method("POST")
     * @Template("SluBundle:Usuario:editar.html.twig")
     * @Security("has_role('ROLE_USUARIO_EDITAR_BASICO')")
     */
    public function atualizaContatoAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');

        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        $entity->ordenaMail();
        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }

        $editForm = $this->criarFormularioEdicaoDados($entity);
        $post = $request->request->get('altera_dados_usuario');
        $entity->setPostalAddress($post['postalAddress']);

        foreach ($post['telephoneNumber'] as $chave => $fone) {
            if (empty($fone)) {
                unset($post['telephoneNumber'][$chave]);
            }
        }
        $entity->setTelephoneNumber($post['telephoneNumber']);

        $editForm->handleRequest($request);


        if ($editForm->isValid()) {
            $ldapManager = $this->get('uft.ldap.manager');
            $dn = $ldapManager->dnBuilder(array('uid' => $entity->getUid()), 'ou=People,' . $this->getParameter('ldap_basedn'));
            if ($ldapManager->bind($dn, $post['verificarSenha'])) {
                $arrayFone = null;
                foreach ($entity->getTelephoneNumber() as $telefone) {
                    $arrayFone[] = '+' . preg_replace("/[^0-9]/", "", $telefone);
                }
                $entity->setTelephoneNumber($arrayFone);
                try {
                    $em->persist($entity);
                    if (!($entity->getTeste())) {
                        $this->get('gearman')->doBackgroundJob('UFTSluBundleServicesGSuitWorkerService~atualizarGruposDeUsuarioGSuit', json_encode(array(
                            'uid' => $entity->getUid(),
                        )));
                    }
                    $em->flush();
                    $emSincronizacao = $this->get('uft.sincronizacao.manager');
                    $emSincronizacao->atualizaContato($emSincronizacao->contatoSIE($entity));
                    $this->addFlash(
                        'success',
                        'Dados atualizados com sucesso.'
                    );
                } catch (ContextErrorException $e) {
                    $this->addFlash(
                        'error',
                        'Erro ao atualizar dados de contato.'
                    );
                }

            } else {
                $this->addFlash(
                    'error',
                    'Senha atual incorreta.'
                );
            }


        }

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
        );
    }
}
