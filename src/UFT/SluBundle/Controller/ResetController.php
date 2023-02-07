<?php

namespace UFT\SluBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use UFT\SluBundle\Entity\LdapConta;
use UFT\SluBundle\Entity\PessoaLdap;
//use UFT\SluBundle\Entity\SluConta;
use UFT\SluBundle\Form\ResetaContaType;
use UFT\SluBundle\Form\ResetType;
use UFT\SluBundle\Validator\CpfCnpjValidator;
use UFT\UserBundle\Entity\Usuario;
use UFT\UserBundle\Exception\CustomMessageException;

/**
 * Reset controller.
 *
 * @Route("/reset")
 */
class ResetController extends Controller
{
    public function formataCPF($string)
    {

        return preg_replace('/[^0-9]/', '', $string);

    }

    /** @var $user UserInterface */
    public function findContaByUidOrCpf($usernameOrCpf)
    {
        $validador = new CpfCnpjValidator();
        $em = $this->get('ldap_entity_manager');
        if ($validador->validador($usernameOrCpf)) {
            $usernameOrCpf = $this->formataCPF($usernameOrCpf);

            try {
                $filtro = array();
                $filtro['brPersonCPF'] = preg_replace("/[^0-9]/", "", $usernameOrCpf);
                $filtro['CPF'] = $filtro['brPersonCPF'];

                $user = $em->getRepository(PessoaLdap::class)->findOneByBrPersonCPF($usernameOrCpf);
                if (empty($user)) {
                    $user = $em->getRepository(PessoaLdap::class)->findOneByCpf($usernameOrCpf);
                }
                return $user;
            } catch (\Doctrine\ORM\ORMException $e) {
                // flash msg
                $this->get('session')->getFlashBag()->add('error', 'Nenhum registro encontrado com esse CPF.');
                // or some shortcut that need to be implemented
                $this->addFlash('error', 'Nenhum registro encontrado com esse CPF.');
                $this->get('logger')->error($e->getMessage());
                //$this->get('logger')->error($e->getTraceAsString());
                // $this->logError($e);
                return null;
            } catch (\Exception $e) {
                // other exceptions
                // flash
                // logger
                // redirection
                $this->get('logger')->error($e->getMessage());
                return null;
            }

        } else if (filter_var($usernameOrCpf, FILTER_VALIDATE_EMAIL)) {
            return $entity = $em->getRepository(PessoaLdap::class)->findOneByMail($usernameOrCpf);
        }
        return $entity = $em->getRepository(PessoaLdap::class)->findOneByUid($usernameOrCpf);
    }

    /**
     * Requisição do formulario de reset de senha
     *
     * @Route("/", name="senha_perdida")
     */
    public function requestAction()
    {
        return $this->render('@Slu/Reset/request.html.twig');
    }

    public function getUidByDn($dn)
    {
        return explode('=', explode(',', $dn[0])[0])[1];
    }

    /**
     * Enviar Email para reset de senha
     *
     * @Route("/envia_email", name="envia_email_recuperar_senha")
     */
    public function enviaEmailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
//        dump($request->request->get('username'));die();
        $username = $request->request->get('username');
        $userLdap = $this->findContaByUidOrCpf($username);
        $mail = null;
        $mail2 = null;
        if (null == $userLdap) {
            return $this->render('@Slu/Reset/request.html.twig', array(
                'invalid_username' => $username
            ));
        }
        if ($userLdap->getInstitucional()) {
            $manager = $userLdap->getManager();
            if ($manager == null) {
                return $this->render('@Slu/Reset/request.html.twig', array(
                    'null_manager' => $username
                ));
            }

            $emLdap = $this->get('ldap_entity_manager');
            $responsavel = $emLdap->getRepository(PessoaLdap::class)->findOneByUid($this->getUidByDn($manager));
            if (null == $responsavel) {
                return $this->render('@Slu/Reset/request.html.twig', array(
                    'invalid_manager' => $username
                ));
            }
            $mail2 = is_array($responsavel->getMail())?$responsavel->getMail()[0]:$responsavel->getMail();
            $mail = $responsavel->getPostalAddress();
//            foreach ($responsavel->getMail() as $email) {
//                if( (strpos($email, '@uft') !== false OR strpos($email, '@mail.uft') !== false)){
//                    $mail2= $email;
//                }
//                elseif ((strpos($email, $responsavel->getUid()) !== false) || ((strpos($email, '@uft') === false AND strpos($email, '@mail.uft') === false))) {
//                    $mail = $email;
//                }
//            }
        } else {
            $mail = $userLdap->getPostalAddress();
//            foreach ($userLdap->getMail() as $email) {
//                if ((strpos($email, $userLdap->getUid()) === false) || (strpos($email, '@uft') === false && strpos($email, '@mail.uft') === false)) {
//                    $mail = $email;
//
//                }
//            }
        }

        if (null === $mail and null === $mail2) {
            return $this->render('@Slu/Reset/request.html.twig', array(
                'invalid_email' => $username
            ));
        }

//        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
//            return $this->render('FOSUserBundle:Resetting:passwordAlreadyRequested.html.twig');
//        }
        $user = $em->getRepository('UserBundle:Usuario')->findOneByUsername($userLdap->getUid());
        if ($user === null) {
            $user = new Usuario();
            $user->setUsername($userLdap->getUid());
            $user->setUsernameCanonical($userLdap->getUid());
            if (count($userLdap->getMail()) > 1) {
                $user->setEmailCanonical($userLdap->getMail()[1]);
                $user->setEmail($userLdap->getMail()[1]);
            } else {
                $user->setEmailCanonical($userLdap->getMail()[0]);
                $user->setEmail($userLdap->getMail()[0]);
            }
            $user->setPassword("");
            $user->setEnabled(1);

        }


        if (null === $user->getConfirmationToken() || $user->getConfirmationToken() == '') {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        try {

            $em->persist($user);
            $em->flush($user);
            $this->get('fos_user.mailer')->sendResettingEmailMessage($user, $mail);
            if($userLdap->getInstitucional() && null!=$mail2){
                $this->get('fos_user.mailer')->sendResettingEmailMessage($user, $mail2);
            }
        } catch (UniqueConstraintViolationException $exception){
            /** Error EM1 - cadastro com email duplicado*/
            $this->get('logger')->error($exception->getMessage());
            $this->addFlash(
                'error',
                'Há uma inconsistência no seu cadastro, favor procurar a STI para maior informações, informando o erro: EM1'
            );
            return new RedirectResponse($this->generateUrl('senha_perdida'
            ));
        }

        return new RedirectResponse($this->generateUrl('recuperar_senha_check_email',
            array('email' => $this->getObfuscatedEmail($mail))
        ));
    }

    /**
     * Avisar ao usuario para checkar o email
     *
     * @Route("/check_email", name="recuperar_senha_check_email")
     */
    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('email');

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('senha_perdida'));
        }

        return $this->render('SluBundle:Reset:checkEmail.html.twig', array(
            'email' => $email,
        ));
    }

    /**
     * Reseta senha do usuario
     *
     * @Route("/reset_senha/{token}", name="recuperar_senha_reset")
     *
     */
    public function resetAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $ldapManager = $this->get('ldap_entity_manager');
        $userManager = $this->getDoctrine()->getManager()->getRepository('UserBundle:Usuario');


        $user = $userManager->findOneByConfirmationToken($token);

        if (null === $user) {
            throw new CustomMessageException(sprintf('Este token expirou tente novamente em: <a href="https://sistemas.uft.edu.br/slu/reset/">Esqueci meu Usuário ou Senha</a>'));
        }
        $form = $this->createForm(ResetType::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);

            $user->setConfirmationToken(null);
            $user->setEnabled(true);

            try {
                $userLdap = $ldapManager->getRepository(PessoaLdap::class)->findOneByUid($user->getUsername());
                $userLdap->setCryptPassword($user->getPassword());
                $user->setPassword('');

                $ldapUtil = $this->get('uft.ldap.manager');

                if (!$ldapUtil->update($userLdap->getDn(), array('userPassword' => $userLdap->getUserPassword()), false)) {
                    $ldapUtil->showError();
                    $this->addFlash(
                        'error',
                        'Erro ao alterar senha.'
                    );
                } else {
                    $em->persist($user);
                    $em->flush($user);
                    if (null === $response = $event->getResponse()) {
                        $message = 'Senha alterada com sucesso!';
                        $this->addFlash('success', $message);
                        $url = $this->generateUrl('fos_user_profile_show');
                        $response = new RedirectResponse($url);
                    }
                    return $response;
                }

            } catch (ContextErrorException $e) {
                $this->get('logger')->error($e->getMessage());
                $this->addFlash(
                    'error',
                    'Erro ao alterar senha.'
                );
            }

        }

        return $this->render('SluBundle:Reset:reset.html.twig', array(
            'token' => $token,
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Reseta senha do usuario
     *
     * @Route("/senha_padrao/", name="senha_padrao")
     * @Security("has_role('ROLE_SLU_USUARIO_EDITAR_BASICO')")
     *
     */
    public function senhaPadraoAction(Request $request)
    {
        $ldapManager = $this->get('ldap_entity_manager');
        $em = $this->getDoctrine()->getManager();
        $emailSecundario = null;
        $mail = null;
        if ($request->request->get('reseta_conta') !== null) {
            $username = $request->request->get('reseta_conta')['username'];
            $mail = $request->request->get('reseta_conta')['mail'];
            $cpf = $request->request->get('reseta_conta')['cpf'];
            $cpf = preg_replace("/[^0-9]/", "", $cpf);

        } else {
            $username = $request->request->get('username');
        }

        $userLdap = $ldapManager->getRepository(PessoaLdap::class)->findOneByUid($username);

        if (null === $userLdap or empty($userLdap)) {
            throw new NotFoundHttpException(sprintf('Não existe usuario com este login "%s"', $username));
        }elseif (!($userLdap instanceof PessoaLdap)){
            $this->get('logger')->critical("Usuario {$username} encontrado, mas não é uma instância valida",[
                'cause' => json_encode($userLdap),
            ]);
            throw new NotFoundHttpException(sprintf('Ocorreu um erro nesta operação, tente novamente 
            e caso o erro persista entre em contato com os desenvolvedores informando o login "%s" e a operação que desejava realizar', $username));
        }
        if (!empty($userLdap)) {
            $emailSecundario = $userLdap->getPostalAddress();
        }
        if (empty($emailSecundario) && $mail != null) {
            $emailSecundario = $mail;
        }

        $data['username'] = $username;
        $data['mail'] = $emailSecundario;
        $form = $this->createForm(ResetaContaType::class, $data, array(
            'action' => $this->generateUrl('senha_padrao'),
            'method' => 'POST'
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $aux = $username . time();        // Ele faz um md5 da variavel $aux e captura os 6 primeiros caracteres
                $senha = substr(md5($aux), 0, 8);
                $userLdap->setCryptPassword($senha);
                $arrayAlteracao = array('userPassword' => $userLdap->getUserPassword(), 'cpf' => $cpf);
                if ($emailSecundario == null) {
                    $userLdap->addMail($mail);
                    $arrayAlteracao['mail'] = $userLdap->getMail();
                }
                $ldapUtil = $this->get('uft.ldap.manager');
                if (!$ldapUtil->update($userLdap->getDn(), $arrayAlteracao, false)) {
                    $ldapUtil->showError();
                    $this->addFlash(
                        'error',
                        'Erro ao alterar senha no ldap.'
                    );
                } else {
                    try {
                        $user = $em->getRepository('UserBundle:Usuario')->findOneByUsername($username);
                        if ($user !== null) {
                            $user->setEnabled(true);
                            $user->setConfirmationToken(null);
                            $em->persist($user);
                            $em->flush();
                        }
                    } catch (Exception $e) {
                        $this->addFlash(
                            'error',
                            $e->getMessage()
                        );
                    }
                    $this->get('fos_user.mailer')->sendResettingAccountMessage($username, $senha, $mail);
                    $this->addFlash('info', 'A senha temporária é: <h3 ><b>' . $senha . '</b></h3>');
                    $emailManager = $this->get('uft.email.manager');
                    $suspenso = $emailManager->isSuspenso($userLdap->getUid());

                    if($suspenso){
                        try{
                            $emailManager->reativarEmail($userLdap->getUid());
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
                    }

                    $url = $this->generateUrl('mostra_pessoa', array('uid' => $username));
                    $response = new RedirectResponse($url);
                    return $response;
                }
            } catch (ContextErrorException $e) {
                $this->get('logger')->error($e->getMessage());
                $this->addFlash(
                    'error',
                    'Erro ao alterar senha.'
                );
            }
        }
        return $this->render('@Slu/Reset/reseta_conta.html.twig', array(
            'username' => $username,
            'form' => $form->createView(),
        ));
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail($email)
    {
        if (false !== $pos = strpos($email, '@')) {
            $email = substr($email, 0, 2) . '...' . substr($email, $pos);
        }

        return $email;
    }
}
