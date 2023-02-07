<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Form\NovaContaLdapType;
use UFT\SluBundle\Form\PreRegistroType;

/**
 * Registrar controller.
 *
 * @Route("/registrar")
 */
class RegistrarController extends Controller
{
    /**
     * @Route("/auto_verificacao", name="registro_auto_verificacao")
     */
    public function autoVerificacaoAction(Request $request)
    {
        $pessoa = new PessoaLdap();
        $emDB2 = $this->getDoctrine()->getManager('db2');
        $convertor = $this->get('uft.convertores');
        if(empty($request->query->get("pre_registro_matricula"))
        or empty($request->query->get("pre_registro_cpf"))
        or empty($request->query->get("pre_registro_dataNascimento"))
        or empty($request->query->get("pre_registro_nome"))
        or empty($request->query->get("pre_registro_mae"))
        ) {
            return $this->redirect($this->generateUrl('registro_verificacao',array("pre_registro_matricula"=>$request->query->get("pre_registro_matricula"))));

        }
        $matricula = $request->query->get("pre_registro_matricula");
        $cpf = $request->query->get("pre_registro_cpf");
        $dataNascimento = $request->query->get("pre_registro_dataNascimento");
        $nome = $request->query->get("pre_registro_nome");
        $mae = $request->query->get("pre_registro_mae");
        $pessoa->setAluno(1);
        $em = $this->get('ldap_entity_manager');
        $filtro = array();
        $cpf = $convertor->formataCPF($cpf);
        $filtro['brPersonCPF'] = preg_replace("/[^0-9]/", "",$cpf);
        $filtro['cpf'] = $filtro['brPersonCPF'];

        $person = $em->getRepository(PessoaLdap::class)->findByComplex(array('|' => $filtro), array('searchDn' => $this->getParameter('ldap_basedn')));

        if (count($person) > 0) {
            $resultado = 'Este CPF já está em uso! Recupere sua conta aqui.';
            $this->addFlash('warning', $resultado);
            return $this->forward('SluBundle:Reset:request');
        }
        $date = \DateTime::createFromFormat('d/m/Y',$dataNascimento);

        $pessoa->setBrPersonCPF($cpf);

            $entities = $emDB2->getRepository('SluBundle:SieAluno')->createQueryBuilder('a')
                ->select('a')
                ->where('a.cpf = :cpf')
                ->andWhere('a.dataNascimento = :dataNascimento')
                ->andWhere('a.matricula = :matricula')
                ->andWhere('upper(a.nomeSemAcento) like upper(tiraAcento(:nome))')
                ->andWhere('upper(a.nomeMae) like upper(tiraAcento(:nomeMae))')
                ->setParameter('cpf', $cpf)
                ->setParameter('dataNascimento', $date->format('Y-m-d'))
                ->setParameter('matricula', $matricula)
                ->setParameter('nome', $nome . "%")
                ->setParameter('nomeMae', $mae . "%")
                ->getQuery()->getResult();

            if (empty($entities)) {
                $message = 'Preencha os dados restantes para criar sua conta!';
                $this->addFlash('info', $message);
            } else {

                $pessoa->setMatricula(array($matricula));
                $pessoa = $this->buscaInformacoesSie($pessoa);

                return $this->forward('SluBundle:Registrar:new', array('entity' => $pessoa));
            }
        return $this->redirect($this->generateUrl('registro_verificacao',array("pre_registro_matricula"=>$matricula)));
    }

    /**
     * @Route("/verificacao", name="registro_verificacao")
     */
    public function verificacaoAction(Request $request)
    {
        $pessoa = new PessoaLdap();
        $emDB2 = $this->getDoctrine()->getManager('db2');
        $convertor = $this->get('uft.convertores');
        //        $em = $this->getDoctrine()->getManager();

        if(!empty($request->query->get("pre_registro_matricula"))){
            $pessoa->setMatricula($request->query->get("pre_registro_matricula"));
            $pessoa->setAluno(1);
        }
        $form = $this->createForm(PreRegistroType::class, $pessoa, array(
            'action' => $this->generateUrl('registro_verificacao'),
            'method' => 'POST'));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $post = $request->request->get('pre_registro');

            if(empty($post['vinculo']) || empty($post['displayName']) || empty($post['BrPersonCPF']) || empty($post['matricula']) || empty($post['nomeMae']) || empty($post['schacDateOfBirth']))
            {
                $this->addFlash('error', 'Todos os dados são Obrigatórios! Preencha todos os campos.');
                return $this->render('@Slu/Registrar/registrar.html.twig', array(
                    'data' => $pessoa,
                    'form' => $form->createView(),
                    'titulo' => 'Primeiro Acesso',
                    'rota_cancelar' => 'homepage',
                ));
            }
            $em = $this->get('ldap_entity_manager');
            $filtro = array();
            $post['BrPersonCPF'] = $convertor->formataCPF($post['BrPersonCPF']);
            $filtro['brPersonCPF'] = preg_replace("/[^0-9]/", "", $post['BrPersonCPF']);
            $filtro['cpf'] = $filtro['brPersonCPF'];

            $person = $em->getRepository(PessoaLdap::class)->findByComplex(array('|' => $filtro), array('searchDn' => $this->getParameter('ldap_basedn')));

            if (count($person) > 0) {
                $resultado = 'Este CPF já está em uso! Recupere sua conta aqui.';
                $this->addFlash('warning', $resultado);
                return $this->forward('SluBundle:Reset:request');
            }
            $date = \DateTime::createFromFormat('d/m/Y', $post['schacDateOfBirth']);
            if ($date === false) {
                $this->addFlash('error', 'Formato de Data inválido! Use: <strong>dd/mm/aaaa</strong>');
                return $this->redirect($this->generateUrl('registro_verificacao'));
            }

            $pessoa->setBrPersonCPF($convertor->formataCPF($pessoa->getBrPersonCPF()));

            if ($post['vinculo'] == 1) {
                $entities = $emDB2->getRepository('SluBundle:SieAluno')->createQueryBuilder('a')
                    ->select('a')
                    ->where('a.cpf = :cpf')
                    ->andWhere('a.dataNascimento = :dataNascimento')
                    ->andWhere('a.matricula = :matricula')
                    ->andWhere('upper(a.nomeSemAcento) like upper(tiraAcento(:nome)) OR upper(a.nomePessoa) like upper(tiraAcento(:nome))')
                    ->andWhere('upper(a.nomeMae) like upper(tiraAcento(:nomeMae))')
                    ->setParameter('cpf', $post['BrPersonCPF'])
                    ->setParameter('dataNascimento', $date->format('Y-m-d'))
                    ->setParameter('matricula', $post['matricula'])
                    ->setParameter('nome', $post['displayName'] . "%")
                    ->setParameter('nomeMae', $post['nomeMae'] . "%")
                    ->getQuery()->getResult();

                if (empty($entities)) {
                    $message = 'Os dados não conferem!';
                    $this->addFlash('error', $message);
                } else {

                    $pessoa->setMatricula(array($pessoa->getMatricula()));
                    $pessoa = $this->buscaInformacoesSie($pessoa);

                    return $this->forward('SluBundle:Registrar:new', array('entity' => $pessoa));
                }
            } else {

                $entities = $emDB2->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
                    ->select('s')
                    ->where('s.cpf = :cpf')
                    ->andWhere('s.dataNascimento = :dataNascimento')
                    ->andWhere('s.matricula = :matricula')
                    ->andWhere('upper(s.nomeSemAcento) like upper(tiraAcento(:nome)) OR upper(s.nomePessoa) like upper(tiraAcento(:nome))')
                    ->andWhere('upper(s.nomeMae) like upper(tiraAcento(:nomeMae))')
                    ->setParameter('cpf', $post['BrPersonCPF'])
                    ->setParameter('dataNascimento', $date->format('Y-m-d'))
                    ->setParameter('matricula', $post['matricula'])
                    ->setParameter('nome', $post['displayName'] . "%")
                    ->setParameter('nomeMae', $post['nomeMae'] . "%")
                    ->getQuery()->getResult();
                if (empty($entities)) {
                    $message = 'Os dados não conferem!';
                    $this->addFlash('error', $message);
                } else {
                    $message = 'Criar nova conta Servidor!';
                    $this->addFlash('info', $message);
                    $pessoa->setMatricula(array($pessoa->getMatricula()));
                    $pessoa = $this->buscaInformacoesSie($pessoa);
                    return $this->forward('SluBundle:Registrar:new', array('entity' => $pessoa));

                }
            }
        }
        return $this->render('@Slu/Registrar/registrar.html.twig', array(
            'data' => $pessoa,
            'form' => $form->createView(),
            'titulo' => 'Primeiro Acesso',
            'rota_cancelar' => 'homepage',
        ));
    }

    public function buscaInformacoesSie($entity)
    {
        $convertor = $this->get('uft.convertores');
        $em2 = $this->getDoctrine()->getManager('db2');
        $msg = false;

        $alunos = $em2->getRepository('SluBundle:SieAluno')->createQueryBuilder('a')
            ->where('a.cpf = :cpf')
            ->setParameter('cpf', $convertor->formataCPF(trim($entity->getBrPersonCPF())))
            ->orderBy('a.dataIngresso', 'DESC')
            ->getQuery()->getResult();
        $entity->setMail(array(0 => ''));
        if (!empty($alunos)) {
            $entity = $alunos[0]->getPessoaLdap($entity);
            $telefone = $convertor->formataTelefoneInternacional($alunos[0]->getTelefone());
            if ($telefone != null && ($entity->getTelephoneNumber() == null || !in_array($telefone, $entity->getTelephoneNumber()))) {
                $entity->addTelephoneNumber($telefone);
            }
            if ($alunos[0]->getEmail() != null && (strpos($alunos[0]->getEmail(), '@uft') === false && strpos($alunos[0]->getEmail(), '@mail.uft') === false) && ($entity->getMail() == null || !in_array($alunos[0]->getEmail(), $entity->getMail()))) {
                $entity->addMail(   str_replace('@mail.uft', '@uft', $alunos[0]->getEmail()));
            }
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
            if ($professores[0]->getEmail() != null && (strpos($professores[0]->getEmail(), '@uft') === false && strpos($professores[0]->getEmail(), '@mail.uft') === false) && ($entity->getMail() == null || !in_array($professores[0]->getEmail(), $entity->getMail()))) {
                $entity->addMail(   str_replace('@mail.uft', '@uft', $professores[0]->getEmail()));

            }
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
            if ($servidores[0]->getEmail() != null && (strpos($servidores[0]->getEmail(), '@uft') === false && strpos($servidores[0]->getEmail(), '@mail.uft') === false) && ($entity->getMail() == null || !in_array($servidores[0]->getEmail(), $entity->getMail()))) {
                $entity->addMail(   str_replace('@mail.uft', '@uft', $servidores[0]->getEmail()));
            }
            $msg = true;
        }

        if (count($entity->getMail()) < 2) {
            $entity->addMail("");
        }

        if ($msg) {
            $this->addFlash(
                'info',
                'Dados importados do SIE.'
            );
        }
        return $entity;
    }

    /**
     * Criar formulario para nova conta de usuario.
     *
     * @param PessoaLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PessoaLdap $entity)
    {

        $convertor = $this->get('uft.convertores');
        $emUtil = $this->get('uft.ldap.manager');
        $emLdap = $this->get('ldap_entity_manager');
        $form = $this->createForm(NovaContaLdapType::class, $entity, array(
            'action' => $this->generateUrl('contaLdap_create'),
            'method' => 'POST',
            'convertor' => $convertor,
            'emUtil' => $emUtil,
            'emLdap' => $emLdap
        ));
        return $form;
    }


    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/nova", name="novaConta_Ldap")
     * @Method("GET")
     * @Template("SluBundle:Registrar:nova.html.twig")
     */
    public function newAction($entity = null)
    {

        if ($entity == null) {
            $entity = new PessoaLdap();
        } else if (empty($entity->getTelephoneNumber())) {
            $entity->setTelephoneNumber(array('0'));
        }
        $form = $this->createCreateForm($entity);
        $this->addFlash(
            'success',
            'Confira seus dados e escolha seu login e senha.'
        );
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new PessoaLdap entity.
     *
     * @Route("/", name="contaLdap_create")
     * @Method("POST")
     * @Template("SluBundle:Registrar:nova.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new PessoaLdap();
        $entity->setDisplayName($request->request->get('nova_conta_ldap')['displayName']);
        $entity->setUid($request->request->get('nova_conta_ldap')['uid']);
        $entity->setDepartmentNumber($request->request->get('nova_conta_ldap')['departmentNumber']);
//        $entity->setMail($request->request->get('nova_conta_ldap')['mail']);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $validacao = true;
        if (!empty($this->get('ldap_entity_manager')->getRepository(PessoaLdap::class)->findByComplex(array('uid' => $request->request->get('nova_conta_ldap')['uid']), array('searchDn' => $this->getParameter('ldap_basedn'))))){
            $validacao = false;
            $this->addFlash(
                'error',
                'O e-mail que você escolheu já está em uso, favor escolher outro e-mail'
            );
        }
        if ($request->request->get('nova_conta_ldap')['postalAddress'] == '') {
            $validacao = false;
            $this->addFlash(
                'error',
                'Você deve inserir um e-mail secundário!'
            );
        }
        if ($request->request->get('nova_conta_ldap')['uid'] == ''){
            $validacao = false;
            $this->addFlash(
                'error',
                'Você deve escolher um login!'
            );
        }
        $copia = array_unique($request->request->get('nova_conta_ldap')['mail']);
        if (count($copia) != count($request->request->get('nova_conta_ldap')['mail'])) {
            $validacao = false;
            $this->addFlash(
                'error',
                'Não é possivel inserir e-mail duplicado.'
            );
        }
        if (!$validacao){
            return $this->redirect($this->generateUrl('registro_verificacao'));
        }

        if ($form->isValid()) {
            $entity->constroiObjetoLdap();
            $logger = $this->get('logger');

            try {
                // verificação de conta teste para inserção do e-mail

                $em = $this->get('ldap_entity_manager');
                $em->persist($entity);
                $em->flush();
                $entity = $em->getRepository(PessoaLdap::class)->findOneByuid($entity->getUid());

//              Alteração dos contatos no SIE
                $emSincronizacao = $this->get('uft.sincronizacao.manager');
                $emSincronizacao->atualizaContato($emSincronizacao->contatoSIE($entity));

//              Verificação de Usuários dos Portais (Aluno e Professor)
                $emSincronizacao->verificarUsuarioSie($entity);
                $ldapUtil = $this->get('uft.ldap.manager');
                $ldapUtil->populeGroupMember($entity, $em);
                /** Removido : todos tem acesso ao slu, esse grupo está sem uso*/
//                $auxiliar = $em->getRepository(GrupoLdap::class)->findOneByCn($this->getParameter('cn_grupo_sistema'));
//                $auxiliar->addMember($entity);
//                $em->persist($auxiliar);
//                if ($entity->getAluno()) {
//                    $auxiliar = $em->getRepository(NewGrupoLdap::class)->findOneByCn("Alunos");
//                    if (!empty($auxiliar) && $auxiliar->hasMember($entity->getDn())) {
//                        if (!$ldapUtil->addMember("Alunos", $entity->getDn())) {
//                            $ldapUtil->showError();
//                        }
//                    }
//                }
////
//                if ($entity->getProfessor()) {
//                    $auxiliar = $em->getRepository(NewGrupoLdap::class)->findOneByCn("Professores");
//                    if (!empty($auxiliar) && $auxiliar->hasMember($entity->getDn())) {
//                        if (!$ldapUtil->addMember("Professores", $entity->getDn())) {
//                            $ldapUtil->showError();
//                        }
//                    }
//                }
//                if ($entity->getFuncionario()) {
//                    $auxiliar = $em->getRepository(NewGrupoLdap::class)->findOneByCn("Técnicos");
//                    if (!empty($auxiliar) && $auxiliar->hasMember($entity->getDn())) {
//                        if (!$ldapUtil->addMember("Técnicos", $entity->getDn())) {
//                            $ldapUtil->showError();
//                        }
//                    }
//                }
////
//                $auxiliarCampus = $em->getRepository(NewGrupoLdap::class)->findOneByCn($entity->getCampus());
//                if (!empty($auxiliarCampus) && $auxiliarCampus->hasMember($entity->getDn())) {
//                    if (!$ldapUtil->addMember($entity->getCampus(), $entity->getDn())) {
//                        $ldapUtil->showError();
//                    }
//                }
                if (!($entity->getTeste())) {
                    $this->get('gearman')->doBackgroundJob('UFTSluBundleServicesGSuitWorkerService~atualizarGruposDeUsuarioGSuit', json_encode(array(
                        'uid' => $entity->getUid(),
                    )));
                }
                $this->addFlash(
                    'success',
                    'Conta criada com sucesso!'
                );
            } catch (ContextErrorException $e) {
                $logger->error('Registry Error: UID: '.$entity->getUid().' : '.$e->getMessage());

                $this->addFlash(
                    'error',
                    'Erro ao tentar inserir a pessoa'
                );
            }

            if (!($entity->getTeste())) {
                $emailManager = $this->get('uft.email.manager');
                try {
                    $emailManager->criarEmail($entity);
                } catch (\Exception $exception){
                    $logger->error('Email Error: UID'.$entity->getUid().' : '.$exception->getMessage());
                    $this->addFlash(
                        'info',
                        'Faça login para ativar sua conta de e-mail.'
                    );
                }
            }

            return $this->redirect($this->generateUrl('homepage', array('uid' => $entity->getUid(),'doLogin'=> true)));

        }
        return array(
            'data' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/verificar_email", name="verificar_lista")
     */
    public function verificarListaAction(Request $request)
    {
        $pessoa = new PessoaLdap();

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('verificar_lista'))
            ->setMethod('POST')
            ->add('email', EmailType::class, array('label' => 'E-mail'))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {

            $email = $request->request->get('form')['email'];
            $em = $this->get('ldap_entity_manager');
            $pos =  strpos($email, '@');
            $login = substr($email,0,$pos);

            $array = ['&'=>
                         [
                         'title'=>'1',
                         '!'=>[
                             'institucional'=>1
                         ],
                         'uid' => $login
                         ]
            ];
            $entity = $em->getRepository(PessoaLdap::class)->findByComplex($array, array('searchDn' => $this->getParameter('ldap_basedn')));
            if(empty($entity) || strpos($email, 'fapto') !==false ){
                $this->addFlash(
                    'success',
                    'Usuário não encontra-se na lista de recadastramento!'
                );

            }else{
                $person = $entity[0];
                $antiga = $person->getObjectClass();
                $nova = $person->getNovasObjectClass();
                $isPadraoAntigo = array_diff($nova, $antiga);
                $isPadraoAntigo2 = array_diff($antiga, $nova);
                if (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) {
                    $recadastrado = false;
                    $this->addFlash(
                        'warning',
                        'Usuário na lista de pendência! Faça login e realize o recadastramento.'
                    );
                }
                return $this->forward('SluBundle:Default:index' );
            }




        }
        return $this->render('@Slu/Registrar/registrar.html.twig', array(
            'data' => $pessoa,
            'form' => $form->createView(),
            'titulo' => 'Verifique seu e-mail',
            'rota_cancelar' => 'homepage',
        ));
    }

}
