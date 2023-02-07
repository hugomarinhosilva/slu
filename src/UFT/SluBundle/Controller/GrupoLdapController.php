<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\HttpFoundation\Request;
use UFT\SluBundle\Entity\GrupoLdap;
use UFT\SluBundle\Entity\NewGrupoLdap;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Form\GrupoLdapType;

/**
 * Conta controller.
 *
 * @Route("/grupo")
 */
class GrupoLdapController extends Controller
{
    protected $em;

    /**
     * Lists all SluConta entities.
     *
     * @Route("/", name="lista_grupos")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {

//        $form = $this->get('form.factory')->createNamedBuilder('buscar_grupo_ldap', FormType::class)
//            ->setMethod('GET')
//            ->setAction($this->generateUrl('lista_grupos'))
//            ->add('uid', TextType::class, array(
//                'label' => 'Login:',
//                'attr' => array(
//                    'style' => 'text-transform:lowercase',
//                    'autocomplete' => "off"),
//                'required' => false))
//
//            ->getForm();

//        if (!empty($request->query->get('buscar_grupo_ldap')['uid'])){
//            $this->get('session')->set('query',array('buscar_grupo_ldap' => $request->query->get('buscar_grupo_ldap')));
//            $uid = $request->query->get('buscar_grupo_ldap')['uid'];
//            $em = $this->get('ldap_entity_manager');
//            $person = $em->getRepository(GrupoLdap::class)->findByUid( '*'.$uid.'*' ,array('searchDn' => 'o=uft,dc=edu,dc=br'));
//            if (count($person) > 1){
//                return array_merge(array('entities' => $person), array('form' => $form->createView()));
//            }else if(count($person) == 1 ){
//                return $this->redirect($this->generateUrl('mostra_grupo_ldap', array('uid' => $person[0]->getUid())));
//            }else{
//                $this->addFlash(
//                    'warning',
//                    'Nenhum Resultado Encontrado!'
//                );
//            }
//        }
//        $person = array();
//        $this->get('session')->set('query','');
        $em = $this->get('ldap_entity_manager');

        if($this->isGranted('ROLE_DESENVOLVEDOR')){
            $groups = $em->getRepository(NewGrupoLdap::class)->findAll();
        } else if($this->isGranted('ROLE_SLU_GRUPO_ADMIN')){
            $groups = $em->getRepository(NewGrupoLdap::class)->findByCn( '*Estagiários*' ,array('searchDn' => 'o=uft,dc=edu,dc=br'));
        }


        return array(
            'entities' => $groups,
//            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new GrupoLdap entity.
     *
     * @Route("/", name="grupoLdap_create")
     * @Method("POST")
     * @Template("SluBundle:GrupoLdap:criar.html.twig")
     * @Security("has_role('ROLE_SLU_GRUPO_CRIAR')")
     */
    public function createAction(Request $request)
    {
        $entity = new GrupoLdap();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $entity->setMember($request->request->get('grupo_ldap')['member']);
        if ($form->isValid()) {
            $nomeGrupo= $request->request->get('grupo_ldap')['nomeGrupo'];
            $em = $this->get('ldap_entity_manager');
            $membros = array();
            if (!empty($entity->getMember())) {
                foreach ($entity->getMember() as $member) {
                    $person = $em->getRepository(PessoaLdap::class)->findOneByUid($member);
                    $membros[] = $person;
                }

            }

            $entity->setMember($membros);
            $entity->setCn(array($nomeGrupo));

            $em->persist($entity);

//            return $this->redirect($this->generateUrl('colegiado_show', array('id' => $entity->getId())));
            return $this->redirect($this->generateUrl('mostra_grupo_ldap', array('dn' => "cn={$entity->getCn()[0]},ou=Group,".$this->getParameter('ldap_basedn'))));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a SluConta entity.
     *
     * @param GrupoLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(GrupoLdap $entity)
    {
        $form = $this->createForm(GrupoLdapType::class, $entity, array(
            'action' => $this->generateUrl('grupoLdap_create'),
            'method' => 'POST',
        ));
//     $form->add('submit', 'submit', array('label' => 'Create'));
        return $form;
    }

    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/novo", name="grupoLdap_nova")
     * @Method("GET")
     * @Template("SluBundle:GrupoLdap:criar.html.twig")
     * @Security("has_role('ROLE_SLU_GRUPO_CRIAR')")
     */
    public function newAction()
    {
        $entity = new GrupoLdap();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Mostra dados da grupo
     *
     * @Route("/mostrar/{dn}", name="mostra_grupo_ldap")
     * @Template()
     * @Security("has_role('ROLE_SLU_GRUPO_MOSTRAR')")
     */
    public function mostrarAction($dn)
    {
//        dump($dn);
        $em = $this->get('ldap_entity_manager');
        $grupo = $em->getRepository(NewGrupoLdap::class)->findOneByCn($this->getIdByDn($dn));

        return array(
            'entity' => $grupo,
        );
    }

    public function getIdByDn($dn)
    {
        return explode("=", explode(',', $dn)[0])[1];
    }

    /**
     * Displays a form to edit an existing GrupoLdap entity.
     *
     * @Route("/edicao/{dn}/editar", name="edita_grupo_ldap")
     * @Method({"GET"})
     * @Template("SluBundle:GrupoLdap:editar.html.twig")
     * @Security("has_role('ROLE_SLU_GRUPO_EDITAR')")
     */
    public function editaGrupoLdapAction($dn)
    {

        $em = $this->get('ldap_entity_manager');

        $grupo = $em->getRepository(GrupoLdap::class)->findOneByCn($this->getIdByDn($dn));
        if (!$grupo) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }


        $editForm = $this->createEditForm($grupo);
//        $deleteForm = $this->createFormBuilder()
//            ->setAction($this->generateUrl('conta_departamento_delete', array('id' => $id)))
//            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Delete'))
//            ->getForm();

        return array(
            'entity' => $grupo,
            'edit_form' => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a SluConta entity.
     *
     * @param GrupoLdap $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(GrupoLdap $entity)
    {
        $form = $this->createForm( GrupoLdapType::class, $entity, array(
            'action' => $this->generateUrl('grupoLdap_update', array('dn' => $entity->getDn())),
            'method' => 'POST',
        ));

        return $form;
    }   

    /**
     * Update a GrupoLdap entity.
     *
     * @Route("/update/{dn}", name="grupoLdap_update")
     * @Method("POST")
     * @Template("SluBundle:GrupoLdap:editar.html.twig")
     * @Security("has_role('ROLE_SLU_GRUPO_EDITAR')")
     */
    public function updateAction(Request $request, $dn)
    {
        $em = $this->get('ldap_entity_manager');
//        dump($request, $dn);
//        die();

        $entity = $em->getRepository(GrupoLdap::class)->findOneByCn($this->getIdByDn($dn));
        $cnAntiga = $entity->getCn();
        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }

        $editForm = $this->createEditForm($entity);
        $diffMember = array_diff($entity->getMember(),$request->request->get('grupo_ldap')['member']);
        if(!empty($diffMember)){
            $entity->removeMember($diffMember);
        }
        $editForm->handleRequest($request);



//        $editForm->submit($request);
        if ($editForm->isValid()) {
            $entity->setCn(array($request->request->get('grupo_ldap')['nomeGrupo']));

            $oldDn = '';
            $newDn = '';

            $cnNova = $entity->getCn();
            try {
                if ($cnNova != $cnAntiga) {


                    $oldDn = $entity->getDn();
                    $position = strpos($entity->getDn(), ",");
                    $newRdn = substr($entity->getDn(), 0, $position);
                    $newParent = 'ou=Desativados,'.$this->getParameter('ldap_basedn');
                    $newDn = $newRdn . ',' . $newParent;

                    $em->rename($oldDn, $newRdn, $newParent);

                    $entity->setDn(null);
                }

            } catch (ContextErrorException $e) {
                $this->addFlash(
                    'error',
                    'Erro ao remover o grupo antigo'
                );
            }

            try {
                $membros = array();
                if(!empty($entity->getMember())){
                    foreach ($entity->getMember() as $member){
                        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($member);
                        $membros[] = $person;
                    }

                }
                $entity->setMember($membros);
                $entity->setDn(null);

                $em->persist($entity);
                $em->flush($entity);
                if ($cnNova != $cnAntiga) {
                    $grupo = $em->getRepository(GrupoLdap::class)->findOneByCn($cnAntiga, array(
                        'searchDn' => 'ou=Desativados,'.$this->getParameter('ldap_basedn')));
                    $em->deleteByDn($grupo->getDn());
                }

                return $this->redirect($this->generateUrl('mostra_grupo_ldap', array('dn' => "cn={$entity->getCn()[0]},ou=Group,".$this->getParameter('ldap_basedn'))));
            } catch(ContextErrorException $e){
                $position = strpos( $oldDn,",");
                $newRdn = substr($oldDn,0,$position);
                $newParent = substr($oldDn,$position+1);
                $em->rename($newDn,$newRdn,$newParent);
                $this->addFlash(
                    'error',
                    'Erro ao atualizar o grupo'
                );
            }


        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Remover grupo do ldap
     *
     * @Route("/remover/{dn}", name="remover_grupo")
     * @Security("has_role('ROLE_SLU_GRUPO_REMOVER')")
     */
    public function removerAction($dn, $baseDn = null)
    {

        $em = $this->get('ldap_entity_manager');

        try{
            $em->deleteByDn($dn);
            return $this->redirect($this->generateUrl('lista_grupos'));
        }catch(ContextErrorException $e){
            $this->addFlash(
                'error',
                'Erro ao remover o grupo'
            );
            return $this->redirect($this->generateUrl('lista_grupos'));

        }


    }
}