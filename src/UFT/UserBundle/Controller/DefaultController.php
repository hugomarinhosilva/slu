<?php

namespace UFT\UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{


    public function loginAction(Request $request)
    {


        $idpEntityId = $request->get('idp');
        $padrao = '/^(https?:\/\/)/';

        if (null === $idpEntityId || empty(preg_match($padrao, $idpEntityId))) {
            return $this->redirect($this->generateUrl($this->container->getParameter('lightsaml_sp.route.discovery')));
        }


        $profile = $this->get('ligthsaml.profile.login_factory')->get($idpEntityId);
        $context = $profile->buildContext();
        $action = $profile->buildAction();

        $action->execute($context);

        return $context->getHttpResponseContext()->getResponse();
    }


}

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//
//class DefaultController extends Controller
//{
//    public function indexAction($name)
//    {
//         $repository = $this->getDoctrine()->getManager()->getRepository('SluBundle:SluVinculo');
//         $pessoa = $repository->findAll();
//         dump($pessoa);die();
//        return $this->render('UserBundle:Default:index.html.twig', array('name' => $name));
//    }
//
//    public function listUserAction()
//    {
//         $repository = $this->getDoctrine()->getManager()->getRepository('UserBundle:Usuario');
//         $user = $repository->findAll();
//         var_dump($user);
//        return $this->render('SluBundle:Usuario:userlist.html.twig', array('name' => $user));
//    }
//}

