<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UFT\UserBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\UserBundle\Form\ChangePasswordFormType;

/**
 * Conta controller.
 *
 * @Route("/profile")
 */
class ProfileController extends Controller
{

    /**
     * Show the user
     * @Route("/", name="fos_user_profile_show")
     * @Security("has_role('ROLE_USUARIO_MOSTRAR')")
     */
    public function showAction()
    {
        $user = $this->getUser();
        $em = $this->get('ldap_entity_manager');
        $sluConta =  $em->getRepository(PessoaLdap::class)->findOneByUid($user->getUserName());
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('UserBundle:Profile:show.html.twig', array(
            'user' => $user,
            'isContaDepartamento' => ($sluConta!=null)?$sluConta->getInstitucional():0
        ));
    }

    /**
     * Edit the user
     * @Route("/edit", name="fos_user_profile_edit")
     * @Security("has_role('ROLE_USUARIO_EDITAR_BASICO')")
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('UserBundle:Profile:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * Change user password
     * @Route("/change-password", name="uft_change_password")
     * @Security("has_role('ROLE_USUARIO_ALTERAR_SENHA')")
     */
    public function changePasswordAction(Request $request)
    {
        $response = $this->forward('SluBundle:Usuario:alteraSenhaLdap', array(
            'request'  => $request,
        ));
        return $response;
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $sluConta = $em->getRepository('SluBundle:SluConta')->findOneByUid($user->getUserName());
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */


        $form = $this->createForm(new ChangePasswordFormType(),null);

        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $senhaAtual = $request->request->get('uft_user_change_password')['current_password'];
            if(!$this->verificaSenha($user->getUsername(),$senhaAtual)){
                $message = 'A senha atual informada não confere com a cadastrada!';
                $this->addFlash('error', $message);
            }else{
                /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */


                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);
                $sluConta->setSenha($user->getPlainPassword());
                $em->persist($sluConta);
                $em->flush();

                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($user);

                $ldapEntity = new LdapConta($sluConta);
                $ldapManager = $this->get('uft.ldap.manager');

                $dn = $ldapManager->dnBuilder(array('uid' => $sluConta->getUid()), 'ou=People,o=uft,dc=edu,dc=br');
                if(!$ldapManager->save($dn, $ldapEntity->__toArray(), false)){
                    $ldapManager->showError();
                }

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_profile_show');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

        }

        return $this->render('FOSUserBundle:ChangePassword:changePassword.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function verificaSenha($login,$senha)
    {
        $flag = FALSE;
        $ldapManager = $this->get('uft.ldap.manager');
        $dn = $ldapManager->dnBuilder(array('uid' => $login), 'ou=People,o=uft,dc=edu,dc=br');
        if (!($bind = $ldapManager->bind( $dn, $senha))) {
// se não validar retorna false
            $flag = FALSE;
        } else {
// se validar retorna true
            $flag = TRUE;
        }
//        $ldapManager->disconnect();
        return $flag;

    }
}
