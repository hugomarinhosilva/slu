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
use UFT\SluBundle\Entity\DepartamentoLdap;
use UFT\SluBundle\Entity\GrupoLdap;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Form\AlteraSenhaDepartamentoLdapType;
use UFT\SluBundle\Form\BuscarDepartamentoLdapType;
use UFT\SluBundle\Form\DepartamentoLdapType;

/**
 * Conta controller.
 *
 * @Route("/departamento")
 */
class DepartamentoLdapController extends Controller
{
    protected $em;

    /**
     * Lists all SluConta entities.
     *
     * @Route("/", name="lista_departamentos")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU')")
     */
    public function indexAction(Request $request)
    {
        $departamento = new DepartamentoLdap();
        $departamentos = array();
        $this->get('session')->remove('busca');
        $this->get('session')->remove('busca_completa');
        if (!empty(trim($request->query->get('buscar_departamento_ldap')['uid']))) {
//            $this->get('session')->set('query', array('buscar_pessoa_ldap' => $request->query->get('buscar_pessoa_ldap')));
            $uid = trim($request->query->get('buscar_departamento_ldap')['uid']);
            $departamento->setUid($uid);
            $em = $this->get('ldap_entity_manager');

            $filtro = array();
            $filtro['uid'] = '*' . $uid . '*';
            $filtro['Institucional'] = 1;

            $departamentos = $em->getRepository(DepartamentoLdap::class)->findByComplex(array('&' => $filtro), array('searchDn' => $this->getParameter('people_basedn')));
            $form = $this->criarFormBusca($departamento);

//            dump($departamentos);
//            die();

            if (count($departamentos) > 1) {
                return array_merge(array('entities' => $departamentos), array('form' => $form->createView()));
            } else if (count($departamentos) == 1) {
                return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $departamentos[0]->getUid())));
            } else {
                $this->addFlash(
                    'warning',
                    'Nenhum Resultado Encontrado!'
                );
//                $this->get('session')->set('query', '');
                return array(
                    'entities' => $departamentos,
                    'form' => $form->createView(),
                );
            }
        } else {
            $form = $this->criarFormBusca($departamento);
            return array(
                'entities' => $departamentos,
                'form' => $form->createView(),
            );
        }
    }

    /**
     * Mostra dados da grupo
     *
     * @Route("/mostrar/{uid}", name="mostra_departamento_ldap")
     * @Template()
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU') or has_role('ROLE_INSTITUCIONAL')")
     */
    public function mostrarAction($uid = null)
    {
        $em = $this->get('ldap_entity_manager');
        $uid = ($uid == null) ? $this->getUser()->getUsername() : $uid;
        $departamento = $em->getRepository(DepartamentoLdap::class)->findOneByUid($uid);
        $emailCriado = false;
        $emailManager = $this->get('uft.email.manager');
        $emailCriado = $emailManager->isCreated($uid);
        return array(
            'entity' => $departamento,
            'emailCriado' => $emailCriado,
        );
    }

    /**
     * Creates a form to create a SluConta entity.
     *
     * @param DepartamentoLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(DepartamentoLdap $entity)
    {
        $form = $this->createForm(DepartamentoLdapType::class, $entity, array(
            'action' => $this->generateUrl('departamentoLdap_create'),
            'method' => 'POST',
        ));
//     $form->add('submit', 'submit', array('label' => 'Create'));
        return $form;
    }

    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/novo", name="departamentoLdap_nova")
     * @Method("GET")
     * @Template("SluBundle:DepartamentoLdap:criar.html.twig")
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU')")
     */
    public function newAction()
    {
        $entity = new DepartamentoLdap();
        $entity->setMail(array(''));
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new GrupoLdap entity.
     *
     * @Route("/", name="departamentoLdap_create")
     * @Method("POST")
     * @Template("SluBundle:DepartamentoLdap:criar.html.twig")
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU')")
     */
    public function createAction(Request $request)
    {
        $entity = new DepartamentoLdap();
        $entity->setMail(array(""));
        $form = $this->createCreateForm($entity);

        $entity->setMail($request->request->get('departamento_ldap')['mail']);
        $entity->setManager($request->request->get('departamento_ldap')['manager']);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entity->constroiObjetoLdap();
//            $nomeGrupo= $request->request->get('grupo_ldap')['nomeGrupo'];
            $em = $this->get('ldap_entity_manager');
            $managers = array();
            if (!empty($entity->getManager())) {
                foreach ($entity->getManager() as $member) {
                    $person = $em->getRepository(PessoaLdap::class)->findOneByUid($member);
                    $managers[] = $person;
                }

            }
            $entity->setManager($managers);
//            $entity->setCn(array($nomeGrupo));
            //Adciona Cn para criação do email da contra institucional
            if (!is_array($entity->getCn())) {
                $cn = $entity->getCn();
                $cn[0] = "Conta Institucional";
                $entity->setCn($cn);
            }

            $emailManager = $this->get('uft.email.manager');
            $emailManager->criarEmail($entity);
            $em->persist($entity);
//
            return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $entity->getuid())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Remover departamento do ldap
     *
     * @Route("/remover/{uid}", name="remover_departamento")
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU')")
     */
    public function removerAction($uid, $baseDn = null)
    {

        $em = $this->get('ldap_entity_manager');
        $departament = $em->getRepository(DepartamentoLdap::class)->findOneByUid($uid, array(
            'searchDn' => $this->getParameter('ldap_basedn')));
        $result = '';
        if ($departament && !($departament->getTeste())) {
            $emailManager = $this->get('uft.email.manager');
            $result = $emailManager->deletarEmail($departament);
        }
        if (is_string($result)) {
            $this->addFlash(
                'error',
                'Não foi possível remover o usuário ' . $uid . ', este usuário é um alias da conta ' . $result
            );
        } else {
            try {
                $em->deleteByDn($departament->getDn());
                $this->addFlash(
                    'warning',
                    'O departamento ' . $uid . ' foi removido.'
                );
                return $this->redirect($this->generateUrl('lista_departamentos'));
            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao remover o departamento'
                );
            }


        }
        return $this->redirect($this->generateUrl('lista_departamentos'));


    }

    /**
     * Busca
     *
     * @Route("/busca", name="departamentoLdap_busca")
     * @Method({"POST","GET"})
     * @Template("SluBundle:DepartamentoLdap:buscar.html.twig")
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU')")
     */
    public function buscaAction(Request $request)
    {
        $filtro = array();
        $departamento = new DepartamentoLdap();

        if ($request->get('buscar_departamento_ldap') == null || (
                trim($request->get('buscar_departamento_ldap')["uid"]) == '' &&
                trim($request->get('buscar_departamento_ldap')["manager"]) == '' &&
                trim($request->get('buscar_departamento_ldap')["campus"]) == '')
        ) {
            $form = $this->criarFormBusca($departamento, 'completa');
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
                    $departamento->setUid($request->request->get('busca'));
                    $filtro['manager'] = '*' . $request->request->get('busca') . '*';
                    $departamento->setManager($request->request->get('busca'));
                    $filtro['campus'] = '*' . $request->request->get('busca') . '*';
                    $departamento->setCampus($request->request->get('busca'));
                }
                $this->get('session')->set('busca', $request->request->get('busca'));
                $this->get('session')->set('tipoBusca', $request->request->get('tipoBusca'));
            }
            $form = $this->criarFormBusca($departamento, 'completa');

            if ($request->request->count() == 0 && $this->get('session')->get('busca_completa') != null) {
                $request->request->set('buscar_departamento_ldap', $this->get('session')->get('busca_completa')['buscar_departamento_ldap']);
                $form->submit($request->request->get('buscar_departamento_ldap'));
            }
            $form->handleRequest($request);
            $this->get('session')->remove('busca_completa');

            if ($form->isValid()) {
                $this->get('session')->set('busca_completa', array('buscar_departamento_ldap' => $request->request->get('buscar_departamento_ldap')));
            }


            if (empty($request->request->get('tipoBusca')) == 'todosCampos') {
                if (!empty($request->request->get('buscar_departamento_ldap')['uid'])) {
                    $filtro['uid'] = '*' . $departamento->getUid() . '*';
                }
                if (!empty($request->request->get('buscar_departamento_ldap')['manager'])) {
                    $filtro['cn'] = '*' . $departamento->getManager() . '*';
                }
                if (!empty($request->request->get('buscar_departamento_ldap')['campus'])) {
                    $filtro['campus'] = '*' . $departamento->getCampus() . '*';
                }
            }

            $em = $this->get('ldap_entity_manager');
            $filtro['Institucional'] = 1;

            $departamento = $em->getRepository(DepartamentoLdap::class)->findByComplex(array('&' => $filtro), array('searchDn' => $this->getParameter('ldap_basedn')));

            if (count($departamento) > 1) {
                return array('entities' => $departamento, 'form' => $form->createView());
            } else if (count($departamento) == 1) {
//            } else if (count($person) == 1 && empty($filtro['departmentNumber'])) {
                return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $departamento[0]->getUid())));
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
     * Creates a form to search a DepartamentoSLU entity.
     *
     * @param DepartamentoLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function criarFormBusca(DepartamentoLdap $entity, $tipo = 'simples')
    {
        if ($tipo == 'simples') {
            $form = $form = $this->get('form.factory')->createNamedBuilder('buscar_departamento_ldap', FormType::class, $entity)
                ->setMethod('GET')
                ->setAction($this->generateUrl('lista_departamentos'))
                ->add('uid', TextType::class, array(
                    'label' => 'Login:',
                    'attr' => array(
                        'style' => 'text-transform:lowercase',
                        'autocomplete' => "off"),
                    'required' => false))
                ->getForm();
            return $form;
        } else {
            $form = $this->createForm(BuscarDepartamentoLdapType::class, $entity, array(
                'action' => $this->generateUrl('departamentoLdap_busca'),
                'method' => 'POST',
            ));
            return $form;
        }

    }


    /**
     * Displays a form to edit an existing GrupoLdap entity.
     *
     * @Route("/edicao/{uid}/editar", name="edita_departamento_ldap")
     * @Method({"GET"})
     * @Template("SluBundle:DepartamentoLdap:editar.html.twig")
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU')")
     */
    public function editaDepartamentoLdapAction($uid)
    {

        $em = $this->get('ldap_entity_manager');

        $departamento = $em->getRepository(DepartamentoLdap::class)->findOneByUid($uid);
        $departamento->ordenaMail();
        if (!$departamento) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $editForm = $this->createEditForm($departamento);

        return array(
            'entity' => $departamento,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Creates a form to edit a SluConta entity.
     *
     * @param GrupoLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(DepartamentoLdap $entity)
    {
        $form = $this->createForm(DepartamentoLdapType::class, $entity, array(
            'action' => $this->generateUrl('departamentoLdap_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Update a GrupoLdap entity.
     *
     * @Route("/update/{uid}", name="departamentoLdap_update")
     * @Method("POST")
     * @Template("SluBundle:DepartamentoLdap:editar.html.twig")
     * @Security("has_role('ROLE_SUPER_ADMINISTRADOR_SLU')")
     */
    public function updateAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');

        $entity = $em->getRepository(DepartamentoLdap::class)->findOneByUid($uid);

        $antiga = $entity->getObjectClass();

        $uidAntiga = $entity->getuid();

        $nomeAntigo = $entity->getCn()[0];
        $sobrenomeAntigo = $entity->getSn()[0];


        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $editForm = $this->createEditForm($entity);

//        $post = $request->request->get('departamento_ldap');

        $entity->setMail($request->request->get('departamento_ldap')['mail']);
        $entity->setManager($request->request->get('departamento_ldap')['manager']);
        $editForm->handleRequest($request);


        if ($editForm->isValid()) {
            $oldDn = '';
            $newDn = '';
            $entity->constroiObjetoLdap();
            $nova = $entity->getNovasObjectClass();
            $uidNova = $entity->getuid();
            $isPadraoAntigo = array_diff($nova, $antiga);
            $isPadraoAntigo2 = array_diff($antiga, $nova);


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
            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao atualizar a conta antiga'
                );
            }


            try {
                if (($nomeNovo != $nomeAntigo) || ($sobrenomeNovo != $sobrenomeAntigo)) {
                    $emailManager = $this->get('uft.email.manager');
                    $emailManager->editarNome($uidNova, $nomeNovo, $sobrenomeNovo);
                }

            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao alterar nome da conta'
                );
            }

            try {
                $managers = array();
                if (!empty($entity->getManager())) {
                    foreach ($entity->getManager() as $member) {
                        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($member);
                        $managers[] = $person;
                    }

                }
                $entity->setManager($managers);

                $em->persist($entity);

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
                    $person = $em->getRepository(DepartamentoLdap::class)->findOneByUid($uidAntiga, array(
                        'searchDn' => 'ou=Desativados,' . $this->getParameter('ldap_basedn')));
                    $em->deleteByDn($person->getDn());
                    return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $uidNova)));
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
                        $emailManager = $this->get('uft.email.manager');
                        $emailManager->editarEmail($uidNova, $uidAntiga);
                    }
                } catch (ContextErrorException $e) {
                    $this->addFlash(
                        'error',
                        'Erro ao renomear e-mail'
                    );
                    return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $uid)));
                }
            }
            $this->addFlash(
                'success',
                'Conta atualizada com sucesso!'
            );
            return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $uid)));

        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Recadastrar usuario antigo.
     *
     * @Route("/recadastramento", name="departamentoLdap_recadastrar")
     * @Method({"GET"})
     * @Template("SluBundle:DepartamentoLdap:recadastrar.html.twig")
     * @Security("has_role('ROLE_DEPARTAMENTO_RECADASTRAR')")
     */
    public function recadastraDepartamentoLdapAction(Request $request)
    {
        $em = $this->get('ldap_entity_manager');

        $entity = $em->getRepository(DepartamentoLdap::class)->findOneByUid($this->getUser()->getUserName());
        if (empty($entity)) {
            $this->addFlash(
                'warning',
                'Usuario sem conta no LDAP.'
            );
            return $this->forward('SluBundle:Default:index');
        }


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
    private function createRecadastrarForm(DepartamentoLdap $entity)
    {
        $em = $this->get('ldap_entity_manager');
        $form = $this->createForm(DepartamentoLdapType::class, $entity, array(
            'action' => $this->generateUrl('recadastramento_departamento_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
            'recadastrar' => true
        ));

        return $form;
    }

    /**
     * Atualiza os dados da conta para recadastramento.
     *
     * @Route("/reupdate/{uid}", name="recadastramento_departamento_update")
     * @Method("POST")
     * @Template("SluBundle:DepartamentoLdap:recadastrar.html.twig")
     * @Security("has_role('ROLE_DEPARTAMENTO_RECADASTRAR')")
     */
    public function updateRecadastramentoAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $emMysql = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(DepartamentoLdap::class)->findOneByUid($uid);
        $user = $this->getUser();


        $antiga = $entity->getObjectClass();

        $uidAntiga = $entity->getuid();


        $nomeAntigo = $entity->getCn()[0];
        $sobrenomeAntigo = $entity->getSn();


        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }


        $editForm = $this->createRecadastrarForm($entity);
        $post = $request->request->get('departamento_ldap');

        foreach ($post['mail'] as $chave => $email) {
            if (empty($email)) {
                unset($post['mail'][$chave]);
            }
        }
        $entity->setMail($post['mail']);

        $editForm->handleRequest($request);
        $entity->setUid($uidAntiga);
        if ($editForm->isValid()) {
            $oldDn = '';
            $newDn = '';
            $entity->constroiObjetoLdap();

            $nova = $entity->getNovasObjectClass();
            $uidNova = $entity->getUid();
            $isPadraoAntigo = array_diff($nova, $antiga);
            $isPadraoAntigo2 = array_diff($antiga, $nova);
            // alterando dados do usuário
            $user->setUsername($entity->getUid());
            $user->setUsernameCanonical($entity->getUid());
            $user->setEmail($entity->getMail()[0]);
            $user->setEmailCanonical($entity->getMail()[0]);


            $nomeNovo = $entity->getCn()[0];
            $sobrenomeNovo = $entity->getSn();


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
            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao inativar a conta antiga'
                );
            }

            try {
                $entity->setMemberOf(null);
                $entity->setCreateTimestamp(null);
                $entity->setModifyTimestamp(null);
                $managers = array();
                if (!empty($entity->getManager())) {
                    foreach ($entity->getManager() as $member) {
                        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($member);
                        $managers[] = $person;
                    }

                }
                $entity->setManager($managers);

                $em->persist($entity);
//                $this->get('uft.grupo_email.manager')->atualizarGruposDeUsuario($entity);

                $emMysql->persist($user);
                $emMysql->flush();


                try {
                    if (($nomeNovo != $nomeAntigo) || ($sobrenomeNovo != $sobrenomeAntigo)) {
                        $emailManager = $this->get('uft.email.manager');
                        $emailManager->editarNome($uidNova, $nomeNovo, $sobrenomeNovo);
                    }

                } catch (ContextErrorException $e) {
                    $this->addFlash(
                        'error',
                        'Erro ao alterar nome da conta'
                    );
                }


                if ($uidNova != $uidAntiga || (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2))) {
                    if ($uidNova != $uidAntiga) {
                        try {
                            if (!($entity->getTeste())) {
                                $emailManager = $this->get('uft.email.manager');
                                $emailManager->editarEmail($uidNova, $uidAntiga);
                            }
                        } catch (ContextErrorException $e) {
                            $this->addFlash(
                                'error',
                                'Erro ao renomear e-mail'
                            );
                        }
                    }
                    try {
                        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uidAntiga, array(
                            'searchDn' => 'ou=Desativados,' . $this->getParameter('ldap_basedn')));
                        $em->deleteByDn($person->getDn());
                        return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $uidNova)));
                    } catch (ContextErrorException $e) {
                        $this->addFlash(
                            'error',
                            'Erro ao remover conta antiga com o mesmo login'
                        );
                    }
                }

                $this->addFlash(
                    'success',
                    'Conta atualizada com sucesso!'
                );
                return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $uid)));
            } catch (ContextErrorException $e) {
                $position = strpos($oldDn, ",");
                $newRdn = substr($oldDn, 0, $position);
                $newParent = substr($oldDn, $position + 1);
                $em->rename($newDn, $newRdn, $newParent);
                $this->addFlash(
                    'error',
                    'Erro ao atualizar a conta'
                );
//                dump($e);die();
            }
        }
        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Exibe formulario para atualiza senha do usuario.
     *
     * @Route("/senha/alterar", name="alterar_senha_departamento")
     * @Method({"GET"})
     * @Template("SluBundle:PessoaLdap:alterar_senha.html.twig")
     * @Security("has_role('ROLE_USUARIO_ALTERAR_SENHA')")
     */
    public function alteraSenhaLdapAction()
    {

        $em = $this->get('ldap_entity_manager');
        $uid = $this->getUser()->getUserName();

        $entity = $em->getRepository(DepartamentoLdap::class)->findOneByUid($uid);


        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }


        $editForm = $this->createForm(AlteraSenhaDepartamentoLdapType::class, $entity, array(
            'action' => $this->generateUrl('senhaDepartamento_update', array('uid' => $entity->getUid())),
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
     * @Route("/atualiza/{uid}", name="senhaDepartamento_update")
     * @Method({"POST"})
     * @Template("SluBundle:DepartamentoLdap:alterar_senha.html.twig")
     * @Security("has_role('ROLE_USUARIO_ALTERAR_SENHA')")
     */
    public function atualizaSenhaLdapAction(Request $request, $uid)
    {
        $em = $this->get('ldap_entity_manager');
        $entity = $em->getRepository(DepartamentoLdap::class)->findOneByUid($uid);
        $senhaAntiga = $request->request->get('altera_senha_departamento_ldap')['senhaAntiga'];
        $senhaNova = $request->request->get('altera_senha_departamento_ldap')['userPassword'];

        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $editForm = $this->createForm(AlteraSenhaDepartamentoLdapType::class, $entity, array(
            'action' => $this->generateUrl('senhaDepartamento_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $ldapManager = $this->get('uft.ldap.manager');
            $emailManager = $this->get('uft.email.manager');
            $dn = $ldapManager->dnBuilder(array('uid' => $entity->getUid()), 'ou=People,' . $this->getParameter('ldap_basedn'));
            if ($ldapManager->bind($dn, $senhaAntiga)) {
                if ($senhaAntiga == $senhaNova['first']) {
                    $this->addFlash(
                        'error',
                        'A nova senha não pode ser igual a atual.'
                    );
                } else {
                    $entity->setUserPassword('{CRYPT}' . crypt($entity->getUserPassword(), null));

                    $em->persist($entity);
//                    $this->get('uft.grupo_email.manager')->atualizarGruposDeUsuario($entity);
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
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/criar_email_manual/{username}", name="criar_email_departamento_manual")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMINISTRADOR_SLU')")
     * @param Request $request
     * @param $username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createEmailUserAction(Request $request, $username)
    {
        $emailManager = $this->get('uft.email.manager');
        $em = $this->get('ldap_entity_manager');

        if($emailManager->isCreated($username) == true){
            $this->addFlash(
                'warning',
                'Erro e-mail já ativo.'
            );
            return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $username)));
        }

        $usuarioLdap = $em->getRepository(DepartamentoLdap::class)->findOneByUid($username);


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
            return $this->redirect($this->generateUrl('mostra_departamento_ldap', array('uid' => $username)));

        }

        $this->addFlash(
            'error',
            'Conta do Ldap não encontrada.'
        );
        return $this->redirect($this->generateUrl('homepage', array('uid' => $username)));

    }
}