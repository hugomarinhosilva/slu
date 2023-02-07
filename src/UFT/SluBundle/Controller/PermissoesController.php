<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Form\RolesType;

/**
 * Permissoes controller.
 *
 * @Route("/permissoes")
 */

class PermissoesController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    /**
     *
     * @Route("/alterar", name="alterar_permissoes")
     * @Method({"GET"})
     * @Template("@Slu/Permissoes/alterar_usuario.html.twig")
     * @Security("has_role('ROLE_GRUPO_USUARIO_EDITAR')")
     */
    public function alteraPermissoesAction()
    {

        $em = $this->getDoctrine()->getManager();
        $entity  = $em->getRepository('UserBundle:Usuario')->findOneByUsername('carlosalves');
        $roles = array();
        foreach($entity->getRoles() as $k => $role){
            $roles[$role] = $role;
        }
        $entity->setRoles($roles);
        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no DB.');
        }


        $editForm = $this->createForm(RolesType::class, $entity, array(
            'action' => $this->generateUrl('permissoes_update', array('uid' => $entity->getUsername())),
            'method' => 'POST',
        ));

        return array(
            'entity' => $entity,
            'form' => $editForm->createView()
        );
    }

    /**
     *
     * @Route("/atualizar", name="permissoes_update")
     * @Method({"POST"})
     * @Template("@Slu/Permissoes/alterar_usuario.html.twig")
     * @Security("has_role('ROLE_GRUPO_USUARIO_EDITAR')")
     */
    public function atualizaPermissoesAction(Request $request)
    {

        $em = $this->get('ldap_entity_manager');
        $uid = $this->getUser()->getUserName();

        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);


        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }


        $editForm = $this->createForm(RolesType::class, $entity, array(
            'action' => $this->generateUrl('permissoes_update', array('uid' => $entity->getUid())),
            'method' => 'POST',
        ));

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/reativar_google/{uid}", name="reativar_google")
     * @Method("GET")
     * @Security("has_role('ROLE_SLU_USUARIO_SINCRONIZAR')")
     */
    public function reativarPermissaoGoogle(Request $request,  $uid)
    {

        if(!$request->server->get('HTTP_REFERER')){
            $this->addFlash('error', 'Requisição Invalida.');
            return $this->redirect($this->generateUrl('homepage'));
        }

        $em = $this->get('ldap_entity_manager');

        $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);


        if (!$entity) {
            throw $this->createNotFoundException('Não foi possivel encontrar esse login no LDAP.');
        }
        $emailManager = $this->get('uft.email.manager');
        $suspenso = $emailManager->isSuspenso($entity->getUid());


        if($suspenso){
            try{
                $emailManager->reativarEmail($entity->getUid());
                $this->addFlash(
                    'success',
                    'Suspensão revertida.'
                );
            }catch (\Exception $e)
            {
                $this->addFlash(
                    'error',
                    'Falha na conexão com o google.'
                );
            }
        } else{
            $this->addFlash(
                'warning',
                'A conta nâo está suspensa.'
            );
        }

        return $this->redirect($request->server->get('HTTP_REFERER'));


    }


}
