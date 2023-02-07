<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 06/02/17
 * Time: 17:30
 */

namespace UFT\UserBundle\EventListener;


use LightSaml\Error\LightSamlBindingException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticationFailureListener implements EventSubscriberInterface
{


    private $container;

    /**
     * AuthenticationFailureListener constructor.
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            SecurityEvents::INTERACTIVE_LOGIN => 'onAuthenticationSuccess',
        );
    }

    public function onAuthenticationFailure($event)
    {
        $exception =null;
        if($event instanceof KernelEvent){
            $exception = $event->getException();
        }elseif($event instanceof AuthenticationFailureEvent){
            $exception = $event->getAuthenticationException();
        }

        if ($exception instanceof LightSamlBindingException) {
            $url = $this->container->get('router')->generate('homepage');
            $response = new RedirectResponse($url);
            $response->send();
        }
//        $this->container->get('session')->set('liberado',false);
        // ...
    }

    public function onAuthenticationSuccess()
    {

        $attributes = $this->container->get('security.context')->getToken()->getAttributes();
        $usuario = $this->container->get('security.context')->getToken()->getUser();
        $institucional = null;
        $departmentNumber = null;
        if( isset($attributes['Institucional'])){
            $institucional = $attributes['Institucional'];
        }
        if( isset($attributes['departmentNumber'])){
            $departmentNumber = $attributes['departmentNumber'];
        }
        $userManager = $this->container->get('doctrine')->getManager();

        if ($usuario->getInstitucional() != $institucional) {
            $usuario->setInstitucional($institucional);
            $userManager->persist($usuario);
            $userManager->flush();
        }
        if ($usuario->getDepartmentNumber() == 0) {
            $usuario->setDepartmentNumber($departmentNumber);
            $userManager->persist($usuario);
            $userManager->flush();
        }

    }


}