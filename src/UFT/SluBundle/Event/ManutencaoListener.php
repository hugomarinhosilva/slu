<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 09/10/17
 * Time: 14:53
 */

namespace UFT\SluBundle\Event;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ManutencaoListener
{
    private $container;
    private $twig;

    public function __construct(ContainerInterface $container, \Twig_Environment $twig)
    {
        $this->container = $container;
        $this->twig = $twig;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $maintenanceUntil = $this->container->hasParameter('manutencaoAte') ? $this->container->getParameter('manutencaoAte') : false;
        $maintenance = $this->container->hasParameter('manutencao') ? $this->container->getParameter('manutencao') : false;

        $debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));
        $engine = $this->container->get('templating');



        if ($maintenance && !$debug) {
            $event->setResponse(new Response('

        <div class="row" align="center">
        <div class="login-logo row">
            <a href="http://ww1.uft.edu.br/"><img src="/slu/bundles/slu/image/logo_slu.png" class="logo-img" height="108px" width="158px"></a>
        </div>
     <div class="col-md-12 col-centered">
         <div class="box box-primary" style="padding:  12em 3em 12em 3em">
             <p style="font-size: 600%">Sistema em Manutenção!</p>
             <h3>Desculpe, estamos passando por uma manutenção.</h3>
             <h4>É rapidinho, estamos rodando alguns procedimentos de segurança para que seus dados não se
                 percam.</h4>
           
             <br/>
         </div>
     </div>
 </div>',503));
            $event->stopPropagation();
        }

    }
}
