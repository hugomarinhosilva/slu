<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use UFT\SluBundle\Entity\NewGrupoLdap;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Form\AlteraSenhaLdapType;
use UFT\SluBundle\Form\BuscarPessoaLdapType;
use UFT\SluBundle\Form\InsereEmailExternoType;
use UFT\SluBundle\Form\PessoaLdapType;
use UFT\UserBundle\Entity\Usuario;
use UFT\UserBundle\Entity\UsuarioImpersonate;

/**
 * Conta controller.
 *
 * @Route("/pessoa")
 */
class PessoaLdapController extends Controller
{
    protected $em;
    protected $segundoEmail;

    /**
     * Creates a form to create a SluConta entity.
     *
     * @param PessoaLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function criarFormBusca(PessoaLdap $entity, $tipo = 'simples')
    {
        if ($tipo == 'simples') {
            $form = $form = $this->get('form.factory')->createNamedBuilder('buscar_pessoa_ldap', FormType::class, $entity)
                ->setMethod('GET')
                ->setAction($this->generateUrl('lista_pessoas'))
                ->add('uid', TextType::class, array(
                    'label' => 'Login:',
                    'attr' => array(
                        'style' => 'text-transform:lowercase',
                        'minlength' => 3,
                        'autocomplete' => "off"),
                    'required' => false))
                ->getForm();
            return $form;
        } else {
            $form = $this->createForm(BuscarPessoaLdapType::class, $entity, array(
                'action' => $this->generateUrl('pessoaLdap_busca'),
                'method' => 'POST',
            ));
            return $form;
        }

    }

    /**
     * Lists all SluConta entities.
     *
     * @Route("/", name="lista_pessoas")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_SLU_USUARIO_MOSTRAR')")
     */
    public function indexAction(Request $request)
    {
        $pessoa = new PessoaLdap();
        $person = array();
        $this->get('session')->remove('busca');
        $this->get('session')->remove('busca_completa');
        if (!empty(trim($request->query->get('buscar_pessoa_ldap')['uid']))) {
//            $this->get('session')->set('query', array('buscar_pessoa_ldap' => $request->query->get('buscar_pessoa_ldap')));
            $uid = trim($request->query->get('buscar_pessoa_ldap')['uid']);
            $pessoa->setUid($uid);
            $em = $this->get('ldap_entity_manager');

            $filtro = array();
            foreach ($this->getUser()->getGroups()->toArray() as $group) {
                foreach ($group->getFiltros()->toArray() as $filter) {
                    $filtro['departmentNumber'][] = rtrim($filter->getCodEstruturado(), '.00') . '*';
                }
            }
            $filtro['uid'] = '*' . $uid . '*';
            $filtro['!'] = array('Institucional' => 1);

            $person = $em->getRepository(PessoaLdap::class)->findByComplex(array('&' => $filtro), array('searchDn' => $this->getParameter('ldap_basedn')));
            $form = $this->criarFormBusca($pessoa);

            if (count($person) > 1) {
                return array_merge(array('entities' => $person), array('form' => $form->createView()));
            } else if (count($person) == 1) {
                if (strpos($person[0]->getDn(), 'ou=Desativados') !== false) {
                    return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $person[0]->getUid(), 'status' => 'inativo')));
                } else {
                    return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $person[0]->getUid())));
                }
            } else {
                $this->addFlash(
                    'warning',
                    'Nenhum Resultado Encontrado!'
                );
//                $this->get('session')->set('query', '');
                return array(
                    'entities' => $person,
                    'form' => $form->createView(),
                );
            }
        } else {
            $form = $this->criarFormBusca($pessoa);
            return array(
                'entities' => $person,
                'form' => $form->createView(),
            );
        }


    }

    /**
     * Creates a new PessoaLdap entity.
     *
     * @Route("/", name="pessoaLdap_create")
     * @Method("POST")
     * @Template("SluBundle:PessoaLdap:criar.html.twig")
     * @Security("has_role('ROLE_SLU_USUARIO_CRIAR')")
     */
    public function createAction(Request $request)
    {
        $entity = new PessoaLdap();
        if (!empty($request->request->get('pessoa_ldap')['departmentNumber'])) {
            $entity->setDepartmentNumber($request->request->get('pessoa_ldap')['departmentNumber']);
        }

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($entity->getMail()[0] == $entity->getPostalAddress()) {
            $this->addFlash(
                'error',
                'Não é possivel inserir e-mail duplicado.'
            );
        } else if ($form->isValid()) {
            $em = $this->get('ldap_entity_manager');
            $grupos = (isset($request->request->get('pessoa_ldap')['grupo']) ? $request->request->get('pessoa_ldap')['grupo'] : array());

            $entity->constroiObjetoLdap();
            //cria uma senha aleatória
            $entity->setCryptPassword(rand(9999999, 99999999));

            //Seta o Filtro de Unidade
            $filtroManager = $this->getDoctrine()->getManager()->getRepository('UserBundle:FiltroUnidade');
            $departmentNumber = null;
            if (strcmp($entity->getCampus()[0], 'Reitoria') === 0) {
                $departmentNumber = $filtroManager->findByNomeUnidade('Reitoria');
            } else {
                $campusDepartamento = is_array($entity->getCampus()) ? $entity->getCampus()[0] : $entity->getCampus();
                $departmentNumber = $filtroManager->findByNomeUnidade('Campus Universitário de ' . $campusDepartamento);
            }
            if (!empty($departmentNumber)) {
                $entity->setDepartmentNumber($departmentNumber[0]->getCodEstruturado());
            } else {
                $entity->setDepartmentNumber(1);
            }

            try {
// verificação de conta teste para inserção do e-mail
                if (!($entity->getTeste())) {
                    $emailManager = $this->get('uft.email.manager');
                    $emailManager->criarEmail($entity);
                    $this->get('gearman')->doBackgroundJob('UFTSluBundleServicesGSuitWorkerService~atualizarGruposDeUsuarioGSuit', json_encode(array(
                        'uid' => $entity->getUid(),
                    )));
                }
                $em->persist($entity);
//refatorar incluindo nova verificação de status (TRY-CATCH)


//Envia um e-mail para criar a senha da conta
                $emMysql = $this->getDoctrine()->getManager();
                $username = $entity->getUsername();
                $mail = $entity->getPostalAddress();
                if (null == $entity) {
                    return $this->render('@Slu/Reset/request.html.twig', array(
                        'invalid_username' => $username
                    ));
                }
                //REMOVIDO PARA MUDANCA DE CAMPO MAIL PARA APENAS INSTITUCIONAL
//                foreach ($entity->getMail() as $email) {
//                    if ((strpos($email, $username) === false) || (strpos($email, '@uft') === false && strpos($email, '@mail.uft') === false)) {
//                        $mail = $email;
//
//                    }
//                }
                if (null === $mail) {
                    return $this->render('@Slu/Reset/request.html.twig', array(
                        'invalid_email' => $username
                    ));
                }
                $user = $emMysql->getRepository('UserBundle:Usuario')->findOneByUsername($entity->getUid());
                if ($user === null) {
                    $user = new Usuario();
                    $user->setUsername($entity->getUid());
                    $user->setUsernameCanonical($entity->getUid());
                    $user->setEmail($entity->getMail()[0]);
                    $user->setInstitucional(0);
                    $user->setEnabled(true);
                    $user->setDepartmentNumber($entity->getDepartmentNumber());
                    if (count($entity->getMail()) > 1) {
                        $user->setEmailCanonical($entity->getMail()[1]);
                    } else {
                        $user->setEmailCanonical($entity->getMail()[0]);
                    }
                    $user->setPassword("");
                }

                if (null === $user->getConfirmationToken() || $user->getConfirmationToken() == '') {
                    /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
                    $tokenGenerator = $this->get('fos_user.util.token_generator');
                    $user->setConfirmationToken($tokenGenerator->generateToken());
                }
                $this->get('fos_user.mailer')->sendResettingEmailMessage($user, $mail, 'create_password.template');
                $emMysql->persist($user);
                $emMysql->flush($user);


//                if (!empty($grupos)) {
//                    foreach ($grupos as $grupo) {
//                        $auxiliar = $em->getRepository(GrupoLdap::class)->findOneByCn($grupo);
//                        if(!empty($auxiliar)){
//                            $auxiliar->addMember($entity);
//                            $em->persist($auxiliar);
//                        }
//                    }
//                }
                /** Removido : todos tem acesso ao slu, esse grupo está sem uso*/
//                $auxiliar = $em->getRepository(GrupoLdap::class)->findOneByCn($this->getParameter('cn_grupo_sistema'));
//                $auxiliar->addMember($entity);
//                $em->persist($auxiliar);

                $ldapUtil = $this->get('uft.ldap.manager');
                $ldapUtil->populeGroupMember($entity, $em);

                $this->addFlash(
                    'success',
                    'Conta criada com sucesso! Um e-mail para criar a senha da conta foi enviado para: <strong>' . $mail . '<br /> LEMBRE-SE DE VERIFICAR O SPAM!</strong>'
                );
                return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $entity->getUid())));
            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao tentar inserir a pessoa'
                );
            }
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new PessoaLdap entity.
     *
     * @Route("/nova", name="pessoaLdap_prenova")
     * @Method({"POST","GET"})
     * @Template("SluBundle:PessoaLdap:criar.html.twig")
     * @Security("has_role('ROLE_SLU_USUARIO_CRIAR')")
     */
    public function preCreateAction(Request $request)
    {
        $entity = new PessoaLdap();
        $emMySql = $this->getDoctrine()->getManager();
        $emDB2 = $this->getDoctrine()->getManager('db2');
        $emLDAP = $this->get('ldap_entity_manager');

        $msg = false;

        $convertor = $this->get('uft.convertores');

        $form = $this->createFormBuilder($entity)
            ->add('brPersonCPF', TextType::class, array('label' => 'CPF:', 'attr' => array('class' => 'cpf')))
            ->setAction($this->generateUrl('pessoaLdap_prenova'))
            ->setMethod('POST')
            ->setAttribute('name', 'form_pessoa')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $cpf = preg_replace("/[^0-9]/", "", trim($entity->getBrPersonCPF()));
            if (strlen($cpf) == 11) {

                $filtroLDAP = array();
                $filtroLDAP['brPersonCPF'] = preg_replace("/[^0-9]/", "", trim($entity->getBrPersonCPF()));
                $filtroLDAP['cpf'] = $filtroLDAP['brPersonCPF'];

                $person = $emLDAP->getRepository(PessoaLdap::class)->findByComplex(array('|' => $filtroLDAP));
                if (!empty($person)) {
                    $this->addFlash(
                        'warning',
                        'Já existe uma conta ativa para este CPF.'
                    );
                    return array(
                        'entity' => $entity,
                        'form' => $form->createView(),
                    );
                }
                $usuario = $emMySql->getRepository('UserBundle:Usuario')->createQueryBuilder('u')
                    ->select('u', 'g')
                    ->leftJoin('u.groups', 'g')
                    ->where('u.username = :username')
                    ->setParameter('username', $this->getUser()->getUsername())
                    ->getQuery()->getResult();

                if (empty($usuario[0]->getGroups()->toArray())) {
                    $filtroSql = '';
                } else {
                    $filtroSql = '(';
                    foreach ($usuario[0]->getGroups()->toArray() as $grupos) {
                        foreach ($grupos->getFiltros()->toArray() as $filtro) {
                            $filtroSql .= "table.codEstruturadoExercicio LIKE '" . rtrim($filtro->getCodEstruturado(), '.00') . "%' OR ";
                        }

                    }
                    if ($filtroSql != '(') {
                        $filtroSql = substr($filtroSql, 0, -3) . ')';
                    } else {
                        $filtroSql = '';
                    }
                }

                $convertor = $this->get('uft.convertores');
                $repoAlunos = $emDB2->getRepository('SluBundle:SieAluno')->createQueryBuilder('table');

                if (!empty($usuario[0]->getGroups()->toArray()) && $filtroSql != '') {
                    $repoAlunos->andWhere($filtroSql);
                }

                $alunos = $repoAlunos->andWhere('table.cpf = :cpf')
                    ->setParameter('cpf', $convertor->formataCPF(trim($entity->getBrPersonCPF())))
                    ->orderBy('table.dataIngresso', 'DESC')
                    ->getQuery()
                    ->getResult();

                $entity->setMail(array());
                if (!empty($alunos)) {
                    $entity = $alunos[0]->getPessoaLdap($entity);
                    $telefone = $convertor->formataTelefoneInternacional($alunos[0]->getTelefone());
                    if ($telefone != null) {
                        $entity->setTelephoneNumber(array($telefone));
                    }
                    if ($alunos[0]->getEmail() != null && (strpos($alunos[0]->getEmail(), '@uft') === false && strpos($alunos[0]->getEmail(), '@mail.uft') === false) && ($entity->getMail() == null || !in_array($alunos[0]->getEmail(), $entity->getMail()))) {
                        $entity->addMail(   str_replace('@mail.uft', '@uft', $alunos[0]->getEmail()));
                    }
                    $msg = true;
                }
                $repoProfessores = $emDB2->getRepository('SluBundle:SieServidor')->createQueryBuilder('table');

                if (!empty($usuario[0]->getGroups()->toArray()) && $filtroSql != '') {
                    $repoProfessores->andWhere($filtroSql);
                }
                $professores = $repoProfessores
                    ->andWhere('table.cpf = :cpf')
                    ->andWhere('table.idCargo = :cargo1 OR table.idCargo = :cargo2 OR table.idCargo = :cargo3')
                    ->andWhere('table.idSituacao <> :idSituacao')
                    ->setParameter('cpf', $convertor->formataCPF(trim($entity->getBrPersonCPF())))
                    ->setParameter('cargo1', 61)
                    ->setParameter('cargo2', 62)
                    ->setParameter('cargo3', 733)
                    ->setParameter('idSituacao', 5)
                    ->orderBy('table.dataPosse', 'DESC')
                    ->getQuery()->getResult();

//                dump($professores);
//                die();
                if (!empty($professores)) {
                    $entity = $professores[0]->getPessoaLdap($entity);
                    $telefone = $convertor->formataTelefoneInternacional($professores[0]->getTelefone());
                    if ($telefone != null) {
                        $entity->setTelephoneNumber(array($telefone));
                    }

                    if ($professores[0]->getEmail() != null && (strpos($professores[0]->getEmail(), '@uft') === false && strpos($professores[0]->getEmail(), '@mail.uft') === false) && ($entity->getMail() == null || !in_array($professores[0]->getEmail(), $entity->getMail()))) {
                        $entity->addMail(   str_replace('@mail.uft', '@uft', $professores[0]->getEmail()));
                    }
                    $msg = true;
                }
                $repoServidores = $emDB2->getRepository('SluBundle:SieServidor')->createQueryBuilder('table');

                if (!empty($usuario[0]->getGroups()->toArray()) && $filtroSql != '') {
                    $repoServidores->andWhere($filtroSql);
                }
                $servidores = $repoServidores
                    ->andWhere('table.cpf = :cpf')
                    ->andWhere('table.idCargo <> :cargo1')
                    ->andWhere('table.idCargo <> :cargo2')
                    ->andWhere('table.idCargo <> :cargo3')
                    ->andWhere('table.idSituacao <> :idSituacao')
                    ->setParameter('cpf', $convertor->formataCPF(trim($entity->getBrPersonCPF())))
                    ->setParameter('cargo1', 61)
                    ->setParameter('cargo2', 62)
                    ->setParameter('cargo3', 733)
                    ->setParameter('idSituacao', 5)
                    ->orderBy('table.dataPosse', 'DESC')
                    ->getQuery()->getResult();
                if (!empty($servidores)) {
                    $entity = $servidores[0]->getPessoaLdap($entity);
                    $telefone = $convertor->formataTelefoneInternacional($servidores[0]->getTelefone());
                    if ($telefone != null) {
                        $entity->setTelephoneNumber(array($telefone));
                    }
                    if ($servidores[0]->getEmail() != null && (strpos($servidores[0]->getEmail(), '@uft') === false && strpos($servidores[0]->getEmail(), '@mail.uft') === false) && ($entity->getMail() == null || !in_array($servidores[0]->getEmail(), $entity->getMail()))) {
                        $entity->addMail(   str_replace('@mail.uft', '@uft', $servidores[0]->getEmail()));
                    }
                    $msg = true;
                }


                if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRADOR_SLU') && (empty($professores) && empty($servidores) && empty($alunos))) {
                    $this->addFlash(
                        'info',
                        "Cadastro de usuário não encontrado no SIE."
                    );
                    return $this->forward('SluBundle:PessoaLdap:new', array(
                        'entity' => $entity,
                    ));
                } elseif (empty($professores) && empty($servidores) && empty($alunos)) {
                    $this->addFlash(
                        'error',
                        "Algum dos seguintes erros aconteceu: <strong><ul style='text-align: left'> <li>1 - Os dados do servidro não foram encontrados no SIE. </li> <li>2 - Este usuário não possui permissão para acessar os dados do servidor pesquisado.</li></ul></strong> "
                    );
                } else {
                    if ($msg) {
                        $this->addFlash(
                            'success',
                            'Dados importados do SIE.'
                        );
                    }
                    return $this->forward('SluBundle:PessoaLdap:new', array(
                        'entity' => $entity,
                    ));
                }
            } else {
                $this->addFlash(
                    'error',
                    'O CPF deve ser preechido com 11 dígitos.'
                );

            }

        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a SluConta entity.
     *
     * @param PessoaLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PessoaLdap $entity)
    {
        $form = $this->createForm(PessoaLdapType::class, $entity, array(
            'action' => $this->generateUrl('pessoaLdap_create'),
            'method' => 'POST',
        ));
//     $form->add('submit', 'submit', array('label' => 'Create'));
        return $form;
    }

    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/novo", name="pessoaLdap_nova")
     * @Method("GET")
     * @Template("SluBundle:PessoaLdap:criar.html.twig")
     * @Security("has_role('ROLE_SLU_USUARIO_CRIAR')")
     */
    public function newAction($entity = null)
    {
        if ($entity == null) {
            $entity = new PessoaLdap();
        }
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Mostra dados da pessoa
     *
     * @Route("/mostrar/{uid}", name="mostra_pessoa")
     * @Route("/mostrar/{uid}/{status}", name="mostra_pessoa")
     * @Template()
     * @Security("has_role('ROLE_SLU_USUARIO_MOSTRAR')")
     */
    public function mostrarAction($uid, $status = 'ativo')
    {

        $emMysql = $this->getDoctrine()->getManager();
        $usuarioClone = $emMysql->getRepository(UsuarioImpersonate::class)->findUsuarioImpersonateAtivo($uid);


        $recadastrado = true;
        $em = $this->get('ldap_entity_manager');
        if ($status == 'ativo') {
            $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uid, array(
                'searchDn' => 'ou=People,' . $this->getParameter('ldap_basedn')));
        } else {
            $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uid, array(
                'searchDn' => 'ou=Desativados,' . $this->getParameter('ldap_basedn')));
        }

        if (!empty($person)) {
            $antiga = $person->getObjectClass();
            $nova = $person->getNovasObjectClass();
            $isPadraoAntigo = array_diff($nova, $antiga);
            $isPadraoAntigo2 = array_diff($antiga, $nova);
            if (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) {
                $recadastrado = false;
                $this->addFlash(
                    'warning',
                    'Usuário ainda não realizou o recadratramento!'
                );
            }
        } else {
            $this->addFlash(
                'warning',
                'Usuário sem conta no LDAP.'
            );
        }
        $emailCriado = false;
        $suspenso = false;
        $emailManager = $this->get('uft.email.manager');
        $emailCriado = $emailManager->isCreated($uid);
        if($person) $suspenso = $emailManager->isSuspenso($person->getUid());

        return array(
            'entity' => $person,
            'recadastrado' => $recadastrado,
            'flag' => $usuarioClone ? true : false,
            'emailCriado' => $emailCriado,
            'suspenso' => $suspenso,

        );
    }

    /**
     * Displays a form to edit an existing PessoaLdap entity.
     *
     * @Route("/edicao/{uid}/editar", name="edita_pessoa_ldap")
     * @Method({"GET"})
     * @Template("SluBundle:PessoaLdap:editar.html.twig")
     * @Security("has_role('ROLE_SLU_USUARIO_EDITAR_BASICO')")
     */
    public function editaPessoaLdapAction($uid)
    {

        $em = $this->get('ldap_entity_manager');

        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        if(count($entity->getMail()) >= 2){
            $entity->ordenaMail();
        }

        $antiga = $entity->getObjectClass();
        $nova = $entity->getNovasObjectClass();
        if (empty($entity->getTelephoneNumber())) {
            $entity->setTelephoneNumber(array('0'));
        }

        $isPadraoAntigo = array_diff($nova, $antiga);
        $isPadraoAntigo2 = array_diff($antiga, $nova);


        if (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) {
            if (!empty($entity->getCpf()) && empty($entity->getBrPersonCPF()))
                $entity->setBrPersonCPF(trim($entity->getCpf()));
            $this->addFlash(
                'warning',
                'Atualização de contas antigas!'
            );
        }

        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        if ($entity->getSchacDateOfBirth() != null) {
            $entity->setSchacDateOfBirth(\DateTime::createFromFormat('dmY', $entity->getSchacDateOfBirth()));
        }
        if (is_array($entity->getCampus())) {
            $entity->setCampus($entity->getCampus()[0]);
        }
        $editForm = $this->createEditForm($entity);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Prepara o formulário para adicionar um e-mail secundário
     *
     * @Route("/edicao/{uid}/emailExterno", name="pre_insere_email_externo")
     * @Method({"GET"})
     * @Template("SluBundle:PessoaLdap:emailExterno.html.twig")
     * @Security("has_role('ROLE_SLU_USUARIO_EDITAR_BASICO')")
     */
    public function preInsereEmailExternoAction($uid)
    {
        $em = $this->get('ldap_entity_manager');
        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        if(count($entity->getMail()) > 1 ){
            $entity->ordenaMail();
            $em->persist($entity);
        }

        $data['uid'] = $entity->getUid();
        $data['email'] = $entity->getPostalAddress();
//        if (!empty($entity->getMail()) && (count($entity->getMail()) > 0)) {
//            foreach ($entity->getMail() as $mail) {
//                if (strpos($mail, 'uft.edu') === false) {
//                    $data['email'] = $mail;
//                    break;
//                }
//            }
//        }

        $insereEmailForm = $this->createForm(InsereEmailExternoType::class, $data, array(
            'action' => $this->generateUrl('insere_email_externo', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));

        return array(
            'entity' => $entity,
            'insere_email_form' => $insereEmailForm->createView()
        );
    }

    /**
     * Adiciona um e-mail secundário no LDAP
     *
     * @Route("/edicao/{uid}/addEmailExterno", name="insere_email_externo")
     * @Method({"POST"})
     * @Template("SluBundle:PessoaLdap:emailExterno.html.twig")
     * @Security("has_role('ROLE_SLU_USUARIO_EDITAR_BASICO')")
     */
    public function insereEmailExternoAction(Request $request)
    {
        $dados = $request->request->get('insere_email_externo');
        $uid = $dados['uid'];

//        $em = $this->get('uft.ldap.manager');
        $em = $this->get('ldap_entity_manager');

        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);


        if (strpos($dados['mailExterno'], 'uft.edu') === false) { // entra nesta condicao se o email for externo
            try {
                $ldapUtil = $this->get('uft.ldap.manager');
//                if (!empty($entity->getMail()) && (count($entity->getMail()) > 1)) { //guarda o e-mail institucional para alterar o e-mail externo
//                    foreach ($entity->getMail() as $mail) {
//                        if (strpos($mail, 'uft.edu') !== false) {
//                            $array_mail[0] = $mail;
//                            break;
//                        }
//                    }
//                    $array_mail[1] = $dados['mailExterno'];
//                    if (!$ldapUtil->update($entity->getDn(), array('mail' => $array_mail), false)) {
//                        $ldapUtil->showError();
//                        $this->addFlash(
//                            'error',
//                            'Erro ao alterar email.'
//                        );
//
//                    }
//                } else { //inserindo o primeiro e-mail externo
//                    $entity->addMail($dados['mailExterno']);
//                    $ldapUtil->addMail($uid, $dados['mailExterno']);
//                }
                $entity->setPostalAddress($dados['mailExterno']);
                $ldapUtil->addMail($uid, $dados['mailExterno']);
                $emSincronizacao = $this->get('uft.sincronizacao.manager');
                $emSincronizacao->atualizaContato($emSincronizacao->contatoSIE($entity));

            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao adicionar o e-mail externo.'
                );
            }
            $this->addFlash(
                'success',
                'E-mail de recuperação adicionado com sucesso!'
            );
            return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $uid)));
        } else {
            $this->addFlash(
                'error',
                'O e-mail secundário não pode ser um e-mail da UFT.'
            );

        }
    }

    /**
     * Creates a form to edit a SluConta entity.
     *
     * @param PessoaLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(PessoaLdap $entity)
    {
        $form = $this->createForm(PessoaLdapType::class, $entity, array(
            'action' => $this->generateUrl('pessoaLdap_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Update a PessoaLdap entity.
     *
     * @Route("/update/{uid}", name="pessoaLdap_update")
     * @Method("POST")
     * @Template("SluBundle:PessoaLdap:editar.html.twig")
     * @Security("has_role('ROLE_SLU_USUARIO_EDITAR_BASICO')")
     */
    public function updateAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $senhaAntiga = is_array($entity->getUserPassword()) ? $entity->getUserPassword()[0] : $entity->getUserPassword();
        $gruposAntigos = array();
        foreach ($entity->getMemberOf() as $aux) {
            $gruposAntigos[] = $aux->getCn()[0];
        }
        $antiga = $entity->getObjectClass();
        $gruposNovos = (isset($request->request->get('pessoa_ldap')['grupo']) ? $request->request->get('pessoa_ldap')['grupo'] : array());

        $telefones = $entity->getTelephoneNumber();
        $emails = $entity->getMail();
        $emailSecundario = $entity->getPostalAddress();


        $uidAntiga = $entity->getuid();
        $nomeAntigo = $entity->getCn()[0];
        $sobrenomeAntigo = $entity->getSn()[0];
        if ($entity->getSchacDateOfBirth() != null) {
            $entity->setSchacDateOfBirth(\DateTime::createFromFormat('dmY', $entity->getSchacDateOfBirth()));
        }
        $editForm = $this->createEditForm($entity);
        $post = $request->request->get('pessoa_ldap');
        $entity->setMail($post['mail']);
        /*Seta a senha antiga quando não for alterada na edição pelo administrador*/

        if (isset($post['alteraSenha']) && !$post['alteraSenha']) {
            $post['userPassword']['first'] = $entity->getUserPassword();
            $post['userPassword']['second'] = $entity->getUserPassword();
            $request->request->set('pessoa_ldap', $post);
        }
        $editForm->handleRequest($request);

        // VERIFICA SE EXISTE ALGUMA ALTERAÇÃO DOS CONTATOS
        $diffTel = array_diff(is_null($telefones) ? array() : $telefones, $entity->getTelephoneNumber());
        $diffTel2 = array_diff($entity->getTelephoneNumber(), is_null($telefones) ? array() : $telefones);
        $diffMail = array_diff($entity->getMail(), $emails);
        $diffMail2 = array_diff($emails, $entity->getMail());//FIM DA VERIFICAÇÃO
        $oldSecondMail = $entity->getPostalAddress();

        if ($editForm->isValid()) {
            $oldDn = '';
            $newDn = '';
            $entity->constroiObjetoLdap();

            $nova = $entity->getNovasObjectClass();
            $uidNova = $entity->getuid();
            $isPadraoAntigo = array_diff($nova, $antiga);
            $isPadraoAntigo2 = array_diff($antiga, $nova);
            $grupoExcluir = array_diff($gruposAntigos, $gruposNovos);
            $grupoAdicionar = array_diff($gruposNovos, $gruposAntigos);
            $nomeNovo = $entity->getCn()[0];
            $sobrenomeNovo = $entity->getSn()[0];
            try {
                if (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2) || $uidNova != $uidAntiga || $emailSecundario != $entity->getPostalAddress()) {
                    $oldDn = $entity->getDn();
                    $position = strpos($entity->getDn(), ",");
                    $newRdn = substr($entity->getDn(), 0, $position);
                    $newParent = 'ou=Desativados,' . $this->getParameter('ldap_basedn');
                    $newDn = $newRdn . ',' . $newParent;
                    $em->rename($oldDn, $newRdn, $newParent);
                    $entity->setDn(null);

                }
            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao atualizar a conta antiga'
                );
            }

            $departmentNumber = null;
            $filtroManager = $this->getDoctrine()->getManager()->getRepository('UserBundle:FiltroUnidade');
            if (strcmp($entity->getCampus()[0], 'Reitoria') === 0) {
                $departmentNumber = $filtroManager->findByNomeUnidade('Reitoria');
            } else {
//                $departmentNumber = $filtroManager->findByNomeUnidade('Campus Universitário de ' . iconv('UTF-8','ISO-8859-1',  $entity->getCampus()[0]));
                $departmentNumber = $filtroManager->findByNomeUnidade('Campus Universitário de ' . $entity->getCampus()[0]);
            }
            if (!empty($departmentNumber)) {
                if ($entity->getDepartmentNumber() == null || strpos($entity->getDepartmentNumber()[0], rtrim($departmentNumber[0]->getCodEstruturado(), '.00')) === false) {
                    $entity->setDepartmentNumber($departmentNumber[0]->getCodEstruturado());
                }
            }

            try {
                if ($entity->getUserPassword() == null) {
                    $entity->setUserPassword($senhaAntiga);
                } elseif (is_array($entity->getUserPassword())) {
                    $entity->setUserPassword($entity->getUserPassword()[0]);
                }

                $em->persist($entity);

                //ALTERA DADOS DE CONTATOS NO SIE
                if (!empty($diffTel) || (!empty($diffTel2)) || (!empty($diffMail)) || (!empty($diffMail2)) || $oldSecondMail!= $entity->getPostalAddress()) {
                    $emSincronizacao = $this->get('uft.sincronizacao.manager');
                    $emSincronizacao->atualizaContato($emSincronizacao->contatoSIE($entity));
                }


//                if (!empty($grupoExcluir)) {
//                    foreach ($grupoExcluir as $grupo) {
//                        $auxiliar = $em->getRepository(GrupoLdap::class)->findOneByCn($grupo);
//                        $auxiliar->removeMember($entity);
//                        $em->persist($auxiliar);
//                    }
//                }

                if (!empty($grupoAdicionar)) {
                    foreach ($grupoAdicionar as $grupo) {
                        $auxiliar = $em->getRepository(NewGrupoLdap::class)->findOneByCn($grupo);
                        $auxiliar->addMember($entity);
                        $em->persist($auxiliar);
                    }
                }
            } catch (ContextErrorException $e) {
                $position = strpos($oldDn, ",");
                $newRdn = substr($oldDn, 0, $position);
                $newParent = substr($oldDn, $position + 1);
                $em->rename($newDn, $newRdn, $newParent);
                $this->addFlash(
                    'error',
                    'Erro ao atualizar a conta'
                );
            }


            if ($uidNova == $uidAntiga && (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2))) {
                try {
                    $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uidAntiga, array(
                        'searchDn' => 'ou=Desativados,' . $this->getParameter('ldap_basedn')));
                    $em->deleteByDn($person->getDn());
                    return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $uid)));
                } catch (ContextErrorException $e) {
                    $this->addFlash(
                        'error',
                        'Erro ao remover conta antiga com o mesmo login'
                    );
                }
            } else if ($uidNova != $uidAntiga) {
                $uid = $uidNova;
                try {
                    if (!($entity->getTeste())) {
                        $this->get('uft.email.manager')->editarEmail($uidNova, $uidAntiga);
//                        $this->get('uft.moodle.manager')->renomearUsuario($uidNova, $uidAntiga);
                        $this->get('uft.intranet.manager')->renomearUsuario($uidNova, $uidAntiga);
                    }
                } catch (ContextErrorException $e) {
                    $this->addFlash(
                        'error',
                        'Erro ao renomear e-mail'
                    );
                    return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $uid)));
                }
            }

            $emailManager = $this->get('uft.email.manager');
            if($emailManager->isSuspenso($entity->getUid())){
                try{
                    $emailManager->reativarEmail($entity->getUid());
                }catch (\Exception $e)
                {
                    $this->addFlash(
                        'error',
                        'Falha na conexão com o google.'
                    );

                }
            }

            try {
                if (($nomeNovo != $nomeAntigo) || ($sobrenomeNovo != $sobrenomeAntigo)) {
                    $emailManager = $this->get('uft.email.manager');
                    $emailManager->editarNome($entity->getUid(), $nomeNovo, $sobrenomeNovo);
                }

            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao alterar nome da conta'
                );
            }
            if (!($entity->getTeste())) {
                $this->get('gearman')->doBackgroundJob('UFTSluBundleServicesGSuitWorkerService~atualizarGruposDeUsuarioGSuit', json_encode(array(
                    'uid' => $entity->getUid(),
                )));
            }
            $this->addFlash(
                'success',
                'Conta atualizada com sucesso!'
            );
            return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $uid)));

        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Remover conta da pessoa do ldap
     *
     * @Route("/remover/{uid}", name="remover_pessoa")
     * @Route("/remover/{uid}/{status}", name="remover_pessoa")
     * @Security("has_role('ROLE_SLU_USUARIO_DELETAR')")
     */
    public function removerAction(Request $request, $uid, $status = 'ativo')
    {
        $em = $this->get('ldap_entity_manager');
        $result = false;
        $pessoas = $em->getRepository(PessoaLdap::class)->findByUid($uid, array(
            'searchDn' => $this->getParameter('ldap_basedn')));
        $count = count($pessoas);
        if ($status == 'ativo') {
            $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uid, array(
                'searchDn' => $this->getParameter('ldap_basedn')));
        } else {
            $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uid, array(
                'searchDn' => 'ou=Desativados,' . $this->getParameter('ldap_basedn')));
        }
        if (!($person->getTeste()) && $count < 2) {
            $emailManager = $this->get('uft.email.manager');
            $result = $emailManager->deletarEmail($person);
        }
        if (is_string($result)) {
            $this->addFlash(
                'error',
                'Não foi possível remover o usuário ' . $uid . ', este usuário é um alias da conta ' . $result
            );
        } else {
            $em->deleteByDn($person->getDn());
            $this->addFlash(
                'warning',
                'O usuário ' . $uid . ' foi removido.'
            );
        }

        if (strpos($request->server->get('HTTP_REFERER'), 'mostrar')) {
            return $this->redirectToRoute('lista_pessoas');
        } else {
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

    }

    /**
     * Mostra dados da pessoa
     *
     * @Route("/suspender/{uid}", name="suspender_pessoa")
     * @Template()
     * @Security("has_role('ROLE_SLU_USUARIO_SITUACAO')")
     */
    public function suspenderContaAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        $dn = explode(",", $person->getDn());
        $oldDn = $person->getDn();
        $newRdn = $dn[0];
        $position = strpos($person->getDn(), "dc=uft");
        $newParent = substr($person->getDn(), $position);
        $newParent = 'ou=Desativados,' . $newParent;
        $em->rename($oldDn, $newRdn, $newParent);
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

    /**
     * Ativar Conta
     *
     * @Route("/ativar/{uid}", name="ativar_pessoa")
     * @Template()
     * @Security("has_role('ROLE_SLU_USUARIO_SITUACAO')")
     */
    public function ativarContaAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uid, array(
            'searchDn' => $this->getParameter('ldap_basedn')));
        $dn = explode(",", $person->getDn());
        $oldDn = $person->getDn();
        $newRdn = $dn[0];
        $position = strpos($person->getDn(), "dc=uft");
        $newParent = substr($person->getDn(), $position);
        $newParent = 'ou=People,' . $newParent;
        $em->rename($oldDn, $newRdn, $newParent);
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

    /**
     * Busca
     *
     * @Route("/busca", name="pessoaLdap_busca")
     * @Method({"POST","GET"})
     * @Template("SluBundle:PessoaLdap:buscar.html.twig")
     * @Security("has_role('ROLE_SLU_USUARIO_MOSTRAR')")
     */
    public function buscaAction(Request $request)
    {
        $filtro2 = array();
        $filtro = array();
        $pessoa = new PessoaLdap();
        foreach ($this->getUser()->getGroups()->toArray() as $group) {
            foreach ($group->getFiltros()->toArray() as $filter) {
                $filtro2['departmentNumber'][] = rtrim($filter->getCodEstruturado(), '.00') . '*';
            }
        }
        if ($request->get('busca') != null && trim($request->get('busca')) === '') {
            $form = $this->criarFormBusca($pessoa, 'completa');
            return array('form' => $form->createView());
        } else {

            if ($request->request->count() == 0 && $this->get('session')->get('busca') != null) {
                $request->request->set('busca', $this->get('session')->get('busca'));
                $request->request->set('tipoBusca', $this->get('session')->get('tipoBusca'));
                $this->get('session')->remove('busca');
                $this->get('session')->remove('tipoBusca');
            }

            if (!empty($request->request->get('tipoBusca')) == 'todosCampos') {
                if (!empty($request->request->get('busca'))) {
                    $filtro['uid'] = '*' . $request->request->get('busca') . '*';
                    $pessoa->setUid($request->request->get('busca'));
                    $filtro['cn'] = '*' . $request->request->get('busca') . '*';
                    $pessoa->setDisplayName($request->request->get('busca'));
                    $filtro['mail'] = '*' . $request->request->get('busca') . '*';
                    $pessoa->setMail($request->request->get('busca'));
                    $filtro['matricula'] = $request->request->get('busca');
                    $pessoa->setMatricula($request->request->get('busca'));
                    $filtro['brPersonCPF'] = $request->request->get('busca');
                    $pessoa->setBrPersonCPF($request->request->get('busca'));
                    $filtro['cpf'] = $request->request->get('busca');
                }
                $this->get('session')->set('busca', $request->request->get('busca'));
                $this->get('session')->set('tipoBusca', $request->request->get('tipoBusca'));
            }
            $form = $this->criarFormBusca($pessoa, 'completa');

            if ($request->request->count() == 0 && $this->get('session')->get('busca_completa') != null) {
                $request->request->set('buscar_pessoa_ldap', $this->get('session')->get('busca_completa')['buscar_pessoa_ldap']);
                $form->submit($request->request->get('buscar_pessoa_ldap'));
            }
            $form->handleRequest($request);
            $this->get('session')->remove('busca_completa');

            if ($form->isValid()) {
                $this->get('session')->set('busca_completa', array('buscar_pessoa_ldap' => $request->request->get('buscar_pessoa_ldap')));
            }


            if (empty($request->request->get('tipoBusca')) == 'todosCampos') {
                if (!empty($request->request->get('buscar_pessoa_ldap')['uid'])) {
                    $filtro['uid'] = '*' . $pessoa->getUid() . '*';
                }
                if (!empty($request->request->get('buscar_pessoa_ldap')['displayName'])) {
                    $filtro['cn'] = '*' . $pessoa->getDisplayName() . '*';
                }
                if (!empty($request->request->get('buscar_pessoa_ldap')['mail'])) {
                    $filtro['mail'] = '*' . $pessoa->getMail() . '*';
                }
                if (!empty($request->request->get('buscar_pessoa_ldap')['Matricula'])) {
                    $filtro['matricula'] = $pessoa->getMatricula();
                }
                if (!empty($request->request->get('buscar_pessoa_ldap')['brPersonCPF'])) {
                    $filtro['brPersonCPF'] = preg_replace("/[^0-9]/", "", $pessoa->getBrPersonCPF());
                    $filtro['cpf'] = preg_replace("/[^0-9]/", "", $pessoa->getBrPersonCPF());
                }
            }

            $em = $this->get('ldap_entity_manager');
            $filtro2['|'] = $filtro;
            $filtro2['!'] = array('Institucional' => 1);
            $person = $em->getRepository(PessoaLdap::class)->findByComplex(array('&' => $filtro2), array('searchDn' => $this->getParameter('ldap_basedn')));

            if (count($person) > 1) {
                return array('entities' => $person, 'form' => $form->createView());
            } else if (count($person) == 1) {
                $this->get('session')->remove('busca_completa');
//            } else if (count($person) == 1 && empty($filtro['departmentNumber'])) {
                if (strpos($person[0]->getDn(), 'ou=Desativados') !== false) {
                    return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $person[0]->getUid(), 'status' => 'inativo')));
                } else {
                    return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $person[0]->getUid())));
                }
            } else {
                if (!empty($this->get('session')->get('busca_completa')) || !empty($this->get('session')->get('busca'))) {
                    $this->addFlash(
                        'warning',
                        'Nenhum Resultado Encontrado!'
                    );
                }
                return array('form' => $form->createView());
            }
        }
    }

    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/sincronizar/{uid}", name="sincronizar_pessoa")
     * @Method("GET")
     * @Security("has_role('ROLE_SLU_USUARIO_SINCRONIZAR')")
     */
    public function sincronizarPessoaAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $em = $this->get('ldap_entity_manager');
        $dados = array();
        $convertor = $this->get('uft.convertores');
        $em2 = $this->getDoctrine()->getManager('db2');
        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        $normatizeMatriculas = $person->getMatricula();
        foreach ( $normatizeMatriculas as $key => $value){
            $normatizeMatriculas[$key] = trim($value);
        }
        $person->setMatricula(array_values(array_unique($normatizeMatriculas)));
        $ldapUtil = $this->get('uft.ldap.manager');

        if ($person->getFuncionario() == 4) {
            $estagiario = 4;
//            $grupo = $em->getRepository(NewGrupoLdap::class)->findOneByCn('Estagiários');
//            if(!in_array(strtolower(trim($person->getDn())), $grupo->getMember())) {
//                $grupo->addMember($person);
//                $em->persist($grupo);
//                $em->flush();
//            }
            $ldapUtil->verifyGroupMember($person, $em, "Estagiários");


        }
        elseif ($person->getFuncionario() > 0) {
//            $grupo = $em->getRepository(NewGrupoLdap::class)->findOneByCn('Técnicos');
//
//            if(!in_array(strtolower(trim($person->getDn())), $grupo->getMember())){
//                $grupo->addMember($person);
//                $em->persist($grupo);
//                $em->flush();
//            }
            $ldapUtil->verifyGroupMember($person, $em, "Técnicos");

        } elseif ($person->getProfessor() > 0) {
//            $grupo = $em->getRepository(NewGrupoLdap::class)->findOneByCn('Professores');
//            if(!in_array(strtolower(trim($person->getDn())), $grupo->getMember())) {
//                $grupo->addMember($person);
//                $em->persist($grupo);
//                $em->flush();
//            }
            $ldapUtil->verifyGroupMember($person, $em, "Professores");

        }
        if (!in_array('brPerson', $person->getObjectClass())) {
            $this->addFlash(
                'error',
                'Conta Antiga. Necessário fazer o recadastramento para acessar os sistemas.'
            );
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        $alunos = $em2->getRepository('SluBundle:SieAluno')->createQueryBuilder('a')
            ->where('a.cpf = :cpf')
            ->setParameter('cpf', $convertor->formataCPF(trim($person->getBrPersonCPF())))
            ->orderBy('a.dataIngresso', 'DESC')
            ->getQuery()->getResult();

        $professores = $em2->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
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

        $servidores = $em2->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
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
            $this->addFlash(
                'error',
                'Nenhum vínculo encontrado para o CPF desta conta.'
            );
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }
//        $this->segundoEmail = '';
        $alunos = array_filter($alunos, function ($element){
            return $element->getIdSituacao() == 1;
        });
        if (!empty($alunos)) {

            usort ( $alunos , function ($a, $b) {
                if ($a->getMatricula() == $b->getMatricula()) {
                    return 0;
                }
                return ($a->getMatricula() > $b->getMatricula()) ? -1 : 1;
            }
            );
            $aluno = array_shift($alunos);
            $dados['sexo'] = $aluno->getSexo();
            $person->setAluno(1);
            $person = $this->setObjetoSincronizacao($aluno, $person);
            foreach ($alunos as $onePerson){
                $person->addMatricula($onePerson->getMatricula());
            }
        }
        $professores = array_filter($professores, function ($element){
            return $element->getIdSituacao() == 1;
        });

        if (!empty($professores)) {
            usort ( $professores , function ($a, $b) {
                if ($a->getDataPosse() == $b->getDataPosse()) {
                    return 0;
                }
                return ($a->getDataPosse() > $b->getDataPosse()) ? -1 : 1;
            }
            );
            $professor = array_shift($professores);

            $dados['sexo'] = $professor->getSexo();
            $person->setProfessor(1);
            $person->setIdDocente($professor->getIdDocente());
            $person = $this->setObjetoSincronizacao($professor, $person);
            foreach ($professores as $onePerson){
                $person->addMatricula($onePerson->getMatricula());
            }

        }
        $servidores = array_filter($servidores, function ($element){
            return $element->getIdSituacao() == 1;
        });
        if (!empty($servidores)) {
            usort ( $servidores , function ($a, $b) {
                if ($a->getDataPosse() == $b->getDataPosse()) {
                    return 0;
                }
                return ($a->getDataPosse() > $b->getDataPosse()) ? -1 : 1;
            }
            );
            $servidor = array_shift($servidores);
            $dados['sexo'] = $servidor->getSexo();
            $person->setFuncionario(1);

            if (strpos(trim($servidor->getUnidadeOficial()), ' ') === FALSE) {
                $person->setCampus(trim($servidor->getUnidadeOficial()));
            }
            $person = $this->setObjetoSincronizacao($servidor, $person);
            foreach ($servidores as $onePerson){
                $person->addMatricula($onePerson->getMatricula());
            }
        }
//        if ((strpos($this->segundoEmail, '@uft') === false || strpos($this->segundoEmail, '@mail.uft') === false) && count($person->getMail()) == 1) {
//            $person->addMail($this->segundoEmail);
//        }
        if ($dados['sexo'] == 'M') {
            $person->setSchacGender(1);
        } elseif ($dados['sexo'] == 'F') {
            $person->setSchacGender(2);
        } else {
            $person->setSchacGender(9);
        }

        try {
            if (isset($funcionario)) {
                $person->setFuncionario($estagiario);
            }
            $person->ordenaMail();

            $em->persist($person);
            if (!($person->getTeste())) {
                $this->get('gearman')->doBackgroundJob('UFTSluBundleServicesGSuitWorkerService~atualizarGruposDeUsuarioGSuit', json_encode(array(
                    'uid' => $person->getUid(),
                )));
            }
            $em->flush();


            $emSincronizacao = $this->get('uft.sincronizacao.manager');

            $emSincronizacao->verificarUsuarioSie($person);

            $emMysql = $this->getDoctrine()->getManager();
            $user = $emMysql->getRepository('UserBundle:Usuario')->findOneByUsername($person->getUid());
            if (empty($user)) {

                $this->addFlash(
                    'info',
                    'Advertência: Conta sincronizada. Porém, código do departamento não pode ser atualizado pois o usuário ainda não logou no sistema.'
                );

            } else {
                $numeroDepartamento = 0;
                if (empty($person->getDepartmentNumber())) {
                    $this->addFlash(
                        'info',
                        'Advertência: Código do departamento não encontrado para este usuário.'
                    );
                    $numeroDepartamento = $person->getDepartmentNumber();
                }
                $user->setDepartmentNumber($numeroDepartamento);
                $emMysql->persist($user);
                $emMysql->flush();
                $this->addFlash(
                    'success',
                    'Conta sincronizada.'
                );
            }

            $emSincronizacao->exportSamba4($person);
            return $this->atualizaContato($person);
        } catch (ContextErrorException $e) {
            $this->addFlash(
                'error',
                'Erro ao sincronizar a conta com o SIE.'
            );

            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

    }

    public function atualizaContato($person)
    {
        $emSincronizacao = $this->get('uft.sincronizacao.manager');
        $emSincronizacao->atualizaContato($emSincronizacao->contatoSIE($person));
        return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $person->getUid())));
    }

    public function setObjetoSincronizacao($origem, $destino)
    {
        $possuiEmailInstitucional = false;
        foreach ($destino->getMail() as $mail) {
            if (strpos($mail, 'uft.edu'))
                $possuiEmailInstitucional = true;
        }
        //origem SIE Destino = LDAP
        if ($origem->getEmail() != null) {
            if ($destino->getMail() != null) {
                if ((strpos($origem->getEmail(), '@') !== false) && !in_array($origem->getEmail(), $destino->getMail())) {
                    if ((strpos($origem->getEmail(), 'uft.edu') === false || $possuiEmailInstitucional === false)) {
                        $destino->addMail($origem->getEmail());
                    }
                }
            } else {
                $destino->addMail($origem->getEmail());
            }
        }
        if ($origem->getEmail2() != null) {
            if ($destino->getMail() != null) {
                if ((strpos($origem->getEmail(), '@') !== false) && !in_array($origem->getEmail2(), $destino->getMail())) {
                    if ((strpos($origem->getEmail(), 'uft.edu') === false || $possuiEmailInstitucional === false)) {
                        $destino->addMail($origem->getEmail2());
                    }
                }
            } else {
                $destino->addMail($origem->getEmail2());
            }
        }

//        if ($destino->getMail() != null && count($destino->getMail()) == 1 && (strpos($origem->getEmail(), '@uft') === false || strpos($origem->getEmail(), '@mail.uft') === false)) {
//            $destino->addMail($origem->getEmail());
//        } elseif ($destino->getMail() == null && (strpos($origem->getEmail(), '@uft') !== false || strpos($origem->getEmail(), '@mail.uft') !== false)) {
//            $destino->addMail($origem->getEmail());
//        } elseif ($destino->getMail() == null) {
//            $this->segundoEmail = $origem->getEmail();
//        }
//        //verifica segundo e-mail
//        if ($destino->getMail() != null && count($destino->getMail()) == 1 && (strpos($origem->getEmail2(), '@uft') === false || strpos($origem->getEmail2(), '@mail.uft') === false)) {
//            $destino->addMail($origem->getEmail2());
//        } elseif ($destino->getMail() == null && (strpos($origem->getEmail2(), '@uft') !== false || strpos($origem->getEmail2(), '@mail.uft') !== false)) {
//            $destino->addMail($origem->getEmail2());
//        } elseif ($destino->getMail() == null) {
//            $this->segundoEmail = $origem->getEmail2();
//        }
        $convertor = $this->get('uft.convertores');
        $telefone = $convertor->formataTelefoneInternacional($origem->getTelefone());
        if ($telefone != null && ($destino->getTelephoneNumber() == null)) {
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
     * Displays a form to edit password of an existing PessoaLdap entity.
     *
     * @Route("/senha/alterar", name="alterar_senha_ldap")
     * @Method({"GET"})
     * @Template("SluBundle:PessoaLdap:alterar_senha.html.twig")
     * @Security("has_role('ROLE_USUARIO_ALTERAR_SENHA')")
     */
    public function alteraSenhaLdapAction()
    {

        $em = $this->get('ldap_entity_manager');
        $uid = $this->getUser()->getUserName();

        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);


        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }


        $editForm = $this->createForm(AlteraSenhaLdapType::class, $entity, array(
            'action' => $this->generateUrl('senhaLdap_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Displays a form to edit password of an existing PessoaLdap entity.
     *
     * @Route("/atualiza/{uid}", name="senhaLdap_update")
     * @Method({"POST"})
     * @Template("SluBundle:PessoaLdap:alterar_senha.html.twig")
     * @Security("has_role('ROLE_USUARIO_ALTERAR_SENHA')")
     */
    public function atualizaSenhaLdapAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        $senhaAntiga = $request->request->get('altera_senha_ldap')['senhaAntiga'];
        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $editForm = $this->createForm(AlteraSenhaLdapType::class, $entity, array(
            'action' => $this->generateUrl('senhaLdap_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $ldapManager = $this->get('uft.ldap.manager');
            $dn = $ldapManager->dnBuilder(array('uid' => $entity->getUid()), 'ou=People,' . $this->getParameter('ldap_basedn'));
            if ($ldapManager->bind($dn, $senhaAntiga)) {
                $emSincronizacao = $this->get('uft.sincronizacao.manager');
                $result=$emSincronizacao->exportSamba4($entity);

                $entity->setUserPassword('{CRYPT}' . crypt($entity->getUserPassword(), null));
                $em->persist($entity);
                $em->flush();
                $this->addFlash(
                    'success',
                    'Senha atualizada com sucesso.'
                );
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
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/assumir/{uid}", name="assumir_usuario")
     * @Method("GET")
     * @Security("has_role('ROLE_DESENVOLVEDOR')")
     */
    public function assumirAction($uid)
    {
        $em = $this->get('ldap_entity_manager');
        $emMysql = $this->getDoctrine()->getManager();
        $usuarioOriginal = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        if (empty($usuarioOriginal)) {
            $this->addFlash(
                'error',
                'Nenhum usuário ativo encontrado com esse login.'
            );
            return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $uid, 'status' => 'inativo')));
        }
        $usuarioClone = $emMysql->getRepository(UsuarioImpersonate::class)->findUsuarioImpersonateAtivo($uid);

        if (empty($usuarioClone)) {
            $usuarioClone = new UsuarioImpersonate($usuarioOriginal);
            $uidAuditoria = $this->getUser()->getUserName();
            $usuarioClone->setUidAuditoria($uidAuditoria);
            $usuarioOriginal->setUserPassword('{CRYPT}' . crypt('12345678', null));
            $msg = 'Senha do usuário foi modificada para padrão de administração!';
        } else {
            $usuarioOriginal->setUserPassword($usuarioClone->getSenha());
            $usuarioClone->setFlag(1);
            $msg = 'Senha original do usuário restaurada!';
        }
        try {
            $emMysql->persist($usuarioClone);
            $emMysql->flush();


            if (empty($usuarioOriginal->getSn())) {
                if (!empty($usuarioOriginal->getDescription())) {
                    $usuarioOriginal->setSn($usuarioOriginal->getDescription());
                }
            }


            $em->persist($usuarioOriginal);

            $this->addFlash(
                'success',
                $msg
            );
            return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $uid)));
        } catch (ContextErrorException $e) {
            $this->addFlash(
                'error',
                'Erro clonar usuário no SLU'
            );
            return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $uid)));

        }

//        die();
    }

    /**
     * Transforma em institucional
     *
     * @Route("/departamentalizar/{uid}", name="departamentalizar_usuario")
     * @Method("GET")
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU')")
     */
    public function departamentalizarAction($uid)
    {
        $ldapManager = $this->get('uft.ldap.manager');
        $em = $this->get('ldap_entity_manager');
        $emMysql = $this->getDoctrine()->getManager();
        $usuarioOriginal = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);
        $usuarioBanco = $emMysql->getRepository(Usuario::class)->findOneByUsername($uid);

        if (empty($usuarioOriginal)) {
            $this->addFlash(
                'error',
                'Usuário não foi encontrado!'
            );
        } else {
            try {
                if (!empty($usuarioBanco)) {
                    $usuarioBanco->setInstitucional(1);
                    $emMysql->persist($usuarioBanco);
                    $emMysql->flush();
                }

                $dn = $ldapManager->dnBuilder(
                    array('uid' => $usuarioOriginal->getUid()),
                    'ou=People,o=uft,dc=edu,dc=br'
                );
                if (!$ldapManager->save($dn, ['Institucional' => 1], false)) {
                    $ldapManager->showError();
                }
                $this->addFlash(
                    'success',
                    'A conna agora é departamental!'
                );
                return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $uid)));
            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao tornar conta departamental'
                );
            }
        }
        return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $uid)));
    }

    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/criar_email_manual", name="criar_email_manual")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     */
    public function createEmailUserAction()
    {
        $username = $this->getUser()->getUsername();
        $emailManager = $this->get('uft.email.manager');

        if($emailManager->isCreated($username) == true){
            $this->addFlash(
                'warning',
                'Erro e-mail já ativo.'
            );
            return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $username)));
        }

        $usuarioLdap = $this->get('ldap_entity_manager')
            ->getRepository(PessoaLdap::class)
            ->findOneByUid($username);

        if(!is_null($usuarioLdap)){
            $retorno = $emailManager->criarEmail($usuarioLdap);
            if($retorno){
                $this->addFlash(
                    'success',
                    'E-mail foi ativado.'
                );
            }else{
                $this->addFlash(
                    'error',
                    'E-mail não pode ser ativado.'
                );
            }
            return $this->redirect($this->generateUrl('homepage'));

        }

        $this->addFlash(
            'error',
            'Conta do Ldap não encontrada.'
        );
        return $this->redirect($this->generateUrl('homepage'));

    }

    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/adm_criar_email_manual/{username}", name="criar_email_pessoal_manual")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMINISTRADOR_SLU')")
     * @param Request $request
     * @param $username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function admCreateEmailUserAction(Request $request, $username)
    {
        $emailManager = $this->get('uft.email.manager');
        $em = $this->get('ldap_entity_manager');

        if($emailManager->isCreated($username) == true){
            $this->addFlash(
                'warning',
                'Erro e-mail já ativo.'
            );
            return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $username)));
        }

        $usuarioLdap = $em->getRepository(PessoaLdap::class)->findOneByUid($username);


        if(!is_null($usuarioLdap)){
            $retorno = $emailManager->criarEmail($usuarioLdap);
            if($retorno){
                $this->addFlash(
                    'success',
                    'E-mail foi ativado.'
                );
            }else{
                $this->addFlash(
                    'error',
                    'E-mail não pode ser ativado.'
                );
            }
            return $this->redirect($this->generateUrl('mostra_pessoa', array('uid' => $username)));

        }

        $this->addFlash(
            'error',
            'Conta do Ldap não encontrada.'
        );
        return $this->redirect($this->generateUrl('homepage', array('uid' => $username)));

    }

}