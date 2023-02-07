<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UFT\SluBundle\Entity\GrupoLdap;
use UFT\SluBundle\Entity\NewGrupoLdap;
use UFT\SluBundle\Entity\PessoaLdap;

/**
 * Ajax controller.
 *
 * @Route("/sip")
 */
class SipController extends Controller
{


    /**
     * Displays a form to create a new SluConta entity.
     *
     * @Route("/sincronizar/{uid}", name="sip_sincronizar_pessoa")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     */
    public function sincronizarPessoaAction(Request $request, $uid)
    {
        if(!$request->server->get('HTTP_REFERER')){
            $this->addFlash('error', 'Requisição Invalida.');
            return $this->redirect($this->generateUrl('homepage'));
        }

        $syncService = $this->get('uft.sincronizacao.manager');
        $em = $this->get('ldap_entity_manager');
        $person = $em->getRepository(PessoaLdap::class)->findOneByUid($uid);

        if(is_null($person)) {
            $this->addFlash('error', 'Usuário não encontrado.');
        }else {
            if($syncService->criarSei($person)){
                $this->addFlash('success', 'Conta SEI exportada com sucesso.');
            }
        }
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }


}
