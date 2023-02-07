<?php

namespace UFT\UserBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use UFT\UserBundle\Entity\Grupo;
use UFT\UserBundle\Entity\Usuario;
use UFT\UserBundle\Form\GrupoType;


/**
 * Grupo controller.
 *
 * @Route("/admin/grupo")
 */
class GrupoController extends Controller
{

    protected $filtroSql = '';

    /**
     * Lists all SluProprietario entities.
     *
     * @Route("/", name="lista_usergrupo")
     * @Method("GET")
     * @Template("UserBundle:Grupo:index.html.twig")
     * @Security("has_role('ROLE_GRUPO_MOSTRAR')")
     */
    public function listaGrupoAction()
    {
        $em = $this->getDoctrine()->getManager();
        $nivel = 99;
        if ($this->getUser() != null) {
            foreach ($this->getUser()->getRoles() as $role) {
                if ($role->getId() != null && $role->getNivel() < $nivel) {
                    $nivel = $role->getNivel();
                }
            }
        }
        $entities = $em->getRepository('UserBundle:Grupo')
            ->createQueryBuilder('g')
            ->leftJoin('g.roles', 'r')
            ->where('r.nivel >=:nivel')
            ->setParameter('nivel', $nivel)
            ->orderBy('r.id')->getQuery()->getResult();
        return array(
            'entities' => $entities,
        );
    }

    /**
     * Lists all SluProprietario entities.
     *
     * @Route("/meus_grupos/", name="lista_meusgrupos")
     * @Method("GET")
     * @Template("UserBundle:MeusGrupos:index.html.twig")
     * @Security("has_role('ROLE_GRUPO_USUARIO_MOSTRAR')")
     */
    public function listaMeusGrupoAction()
    {
        $em = $this->getDoctrine()->getManager();
        $nivel = 99;
        if ($this->getUser() != null) {
            foreach ($this->getUser()->getMeusGrupos() as $grupo) {
                foreach ($grupo->getRoles() as $role) {
                    if ($role->getId() != null && $role->getNivel() < $nivel) {
                        $nivel = $role->getNivel();
                    }
                }
            }
        }
        $entities = $em->getRepository('UserBundle:Grupo')
            ->createQueryBuilder('g')
            ->leftJoin('g.roles', 'r')
            ->leftJoin('g.chefes', 'c')
            ->where('r.nivel >=:nivel')
            ->andWhere('c.id in (:chefe)')
            ->setParameter('nivel', $nivel)
            ->setParameter('chefe', $this->getUser()->getId())
            ->orderBy('g.id')->getQuery()->getResult();
        return array(
            'entities' => $entities,
        );
    }

    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/novo", name="novo_usergrupo")
     * @Method("GET")
     * @Template("UserBundle:Grupo:criar.html.twig")
     * @Security("has_role('ROLE_GRUPO_CRIAR')")
     */
    public function novaAction(Request $request)
    {
        $entity = new Grupo();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a SluConta entity.
     *
     * @param Grupo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Grupo $entity)
    {
        $form = $this->createForm(GrupoType::class, $entity, array(
            'action' => $this->generateUrl('criar_usergrupo'),
            'method' => 'POST',
            'user' => $this->getUser(),
        ));
        return $form;
    }

    /**
     * Creates a new GrupoLdap entity.
     *
     * @Route("/new", name="criar_usergrupo")
     * @Method("POST")
     * @Template("UserBundle:Grupo:criar.html.twig")
     * @Security("has_role('ROLE_GRUPO_CRIAR')")
     */
    public function criarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = new Grupo($request->request->get('slu_rest_form')['name']);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            if (!empty($entity->getUsers())) {
                foreach ($entity->getUsers() as $user) {
                    $user->addGroup($entity);
                    $em->persist($user);
                }
            }
            $em->flush();
            $this->addFlash(
                'success',
                'Grupo criado com sucesso!'
            );
            return $this->redirect($this->generateUrl('mostra_usergrupo', array('id' => $entity->getId())));
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit a new Grupo entity.
     *
     * @Route("/editar/{id}", name="edita_usergrupo")
     * @Method("GET")
     * @Template("UserBundle:Grupo:editar.html.twig")
     * @Security("has_role('ROLE_GRUPO_EDITAR')")
     */
    public function editarAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $grupo = $em->getRepository(Grupo::class)->findOneById($id);
        if (!$grupo) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }

        $form = $this->createEditForm($grupo);

        return array(
            'entity' => $grupo,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit a new Grupo entity.
     *
     * @Route("/editar_meus_grupos/{id}", name="edita_meusgrupos")
     * @Method("GET")
     * @Template("UserBundle:MeusGrupos:editar.html.twig")
     * @Security("has_role('ROLE_GRUPO_USUARIO_EDITAR')")
     */
    public function editarMeusGruposAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $grupo = $em->getRepository(Grupo::class)->findOneById($id);
        if (!$grupo) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $form = $this->createEditMeusGruposForm($grupo);

        $filtro = null;
        if(count($grupo->getFiltros()->toArray())>0){
            $filtro = rtrim($grupo->getFiltros()->toArray()[0]->getCodEstruturado(), '.00');
        }

        return array(
            'entity' => $grupo,
            'filtro' => $filtro,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a SluConta entity.
     *
     * @param Grupo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Grupo $entity)
    {
        $form = $this->createForm(GrupoType::class, $entity, array(
            'action' => $this->generateUrl('update_usergroup', array('id' => $entity->getId())),
            'method' => 'POST',
            'user' => $this->getUser(),
        ));
        return $form;
    }

    /**
     * Creates a form to create a SluConta entity.
     *
     * @param Grupo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditMeusGruposForm(Grupo $entity)
    {
        if (!empty($entity->getFiltros()->toArray())) {
//            foreach ($entity->getFiltros()->toArray() as $filtro) {
//                $this->filtroSql .= "c.departmentNumber LIKE '" . rtrim($filtro->getCodEstruturado(), '.00') . "%' OR ";
//            }
//            $this->filtroSql = substr($this->filtroSql, 0, -3) . '';
//
//            $this->filtroSql = strlen($this->filtroSql)>0?$this->filtroSql.' AND ':$this->filtroSql;
            if(count($entity->getUsers()->toArray())>0){
                $this->filtroSql .= "c.id in (";
                foreach ($entity->getUsers()->toArray() as $membros) {
                    $this->filtroSql .= " " . $membros->getId() . ",";
                }
                foreach ($entity->getChefes()->toArray() as $membros) {
                    $this->filtroSql .= " " . $membros->getId() . ",";
                }
                $this->filtroSql = substr($this->filtroSql, 0, -1) . ')';
            }

            $form = $this->createFormBuilder($entity)
                ->add('name', TextType::class, array('label' => 'Nome do Grupo:', 'attr' => array('readonly' => "true")))
                ->add('users', EntityType::class, array(
                    'label' => 'Membro(s):',
                    'class' => 'UFT\UserBundle\Entity\Usuario',
                    'attr' => array('multiselect' => 'true', 'class' => 'col-md-2'),
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->where($this->filtroSql)->orderBy('c.id');
                    },
                    'required' => false,
                    'multiple' => true,
                    'choices_as_values' => true
                ))
//                ->add('users')
                ->add('chefes', EntityType::class, array(
                    'label' => 'Gerente(s):',
                    'class' => 'UFT\UserBundle\Entity\Usuario',
                    'attr' => array('multiselect' => 'true', 'class' => 'col-md-2'),
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->where($this->filtroSql)->orderBy('c.id');
                    },
                    'required' => false,
                    'multiple' => true,
                    'choices_as_values' => true
                ))
                ->setAction($this->generateUrl('update_meugrupo', array('id' => $entity->getId())))
                ->setMethod('POST')
                ->setAttribute('name', 'form_editarMeusGrupos')
                ->getForm();
        } else {
            $form = $this->createFormBuilder($entity)
                ->add('name', TextType::class, array('label' => 'Nome do Grupo:', 'attr' => array('readonly' => "true")))
                ->add('users', EntityType::class, array(
                    'label' => 'Membro(s):',
                    'class' => 'UFT\UserBundle\Entity\Usuario',
                    'attr' => array('multiselect' => 'true', 'class' => 'col-md-2'),
                    'required' => false,
                    'multiple' => true,
                    'choices_as_values' => true
                ))
                ->add('chefes', EntityType::class, array(
                    'label' => 'Gerente(s):',
                    'class' => 'UFT\UserBundle\Entity\Usuario',
                    'attr' => array('multiselect' => 'true', 'class' => 'col-md-2'),
                    'required' => false,
                    'multiple' => true,
                    'choices_as_values' => true
                ))
                ->setAction($this->generateUrl('update_meugrupo', array('id' => $entity->getId())))
                ->setMethod('POST')
                ->setAttribute('name', 'form_editarMeusGrupos')
                ->getForm();
        }
        return $form;
    }

    /**
     * Update a Grupo entity.
     *
     * @Route("/update/{id}", name="update_usergroup")
     * @Method("POST")
     * @Template("UserBundle:Grupo:editar.html.twig")
     * @Security("has_role('ROLE_GRUPO_EDITAR')")
     */
    public function atualizaAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Grupo::class)->findOneById($id);
        $deletedUser = new ArrayCollection($entity->getUsers()->toArray());

        $form = $this->createCreateForm($entity);
        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $em->flush();
            if (!empty($entity->getUsers())) {
                foreach ($entity->getUsers() as $user) {
                    $deletedUser->removeElement($user);
                    $user->addGroup($entity);
                    $em->persist($user);
                }
            }
            if (!empty($deletedUser->toArray())) {
                foreach ($deletedUser as $user) {
                    $user->removeGroup($entity);
                }
                $em->persist($user);
            }
            $em->flush();
            $this->addFlash(
                'success',
                'Grupo atualizado com sucesso!'
            );
            return $this->redirect($this->generateUrl('mostra_usergrupo', array('id' => $entity->getId())));
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Update a Grupo entity.
     *
     * @Route("/update_meugrupo/{id}", name="update_meugrupo")
     * @Method("POST")
     * @Template("UserBundle:MeusGrupos:editar.html.twig")
     * @Security("has_role('ROLE_GRUPO_USUARIO_EDITAR')")
     */
    public function atualizaMeusGruposAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Grupo::class)->findOneById($id);
        $deletedUser = new ArrayCollection($entity->getUsers()->toArray());


        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }

        $novosMembros = [];
        $novosChefes = [];
        $formRequest = $request->request->get('form');

        foreach ($formRequest['users'] as $user){
            $entity->addUser($em->getRepository(Usuario::class)->findOneById($user));
        }
        foreach ($formRequest['chefes'] as $chefe){
            $entity->addChefe($em->getRepository(Usuario::class)->findOneById($chefe));
        }

        $form = $this->createEditMeusGruposForm($entity);


//        $formRequest['chefes'] = $novosChefes;
//        $request->request->set('form',$formRequest);


        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $em->flush();
            if (!empty($entity->getUsers())) {
                foreach ($entity->getUsers() as $user) {
                    $deletedUser->removeElement($user);
                    $user->addGroup($entity);
                    $em->persist($user);
                }
            }
            if (!empty($deletedUser->toArray())) {
                foreach ($deletedUser as $user) {
                    $user->removeGroup($entity);
                }
                $em->persist($user);
            }
            $em->flush();
            $this->addFlash(
                'success',
                'Grupo atualizado com sucesso!'
            );
            return $this->redirect($this->generateUrl('mostra_meugrupo', array('id' => $entity->getId())));
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Mostra dados da pessoa
     *
     * @Route("/mostrar/{id}", name="mostra_usergrupo")
     * @Template("UserBundle:Grupo:mostrar.html.twig")
     * @Security("has_role('ROLE_GRUPO_MOSTRAR')")
     */
    public function mostrarAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $grupo = $em->getRepository(Grupo::class)->findOneById($id);
        $filtroHierarquicos = null;
        return array(
            'entity' => $grupo,
        );
    }

    /**
     * Mostra dados da pessoa
     *
     * @Route("/mostra_meugrupo/{id}", name="mostra_meugrupo")
     * @Template("UserBundle:MeusGrupos:mostrar.html.twig")
     * @Security("has_role('ROLE_GRUPO_USUARIO_MOSTRAR')")
     */
    public function mostrarMeuGrupoAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $grupo = $em->getRepository(Grupo::class)->findOneById($id);
        return array(
            'entity' => $grupo,
        );
    }

    /**
     * Remover grupo do ldap
     *
     * @Route("/remover/{id}", name="remover_usergrupo")
     * @Security("has_role('ROLE_GRUPO_REMOVER')")
     */
    public function removerAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $grupo = $em->getRepository(Grupo::class)->findOneById($id);

        if (!$grupo) {
            throw $this->createNotFoundException('Nenhum Grupo encontrado para este id: ' . $id);
        }

        try {
            $em->remove($grupo);
            $em->flush();

            return $this->redirect($this->generateUrl('lista_usergrupo'));
        } catch (ContextErrorException $e) {
            $this->addFlash(
                'error',
                'Erro ao remover o grupo'
            );
            return $this->redirect($this->generateUrl('lista_usergrupo'));

        }


    }

}
