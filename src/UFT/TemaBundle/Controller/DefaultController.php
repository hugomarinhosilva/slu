<?php

namespace UFT\TemaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
  
    public function indexAction()
    {
        return $this->render('TemaBundle:Default:index.html.twig');
    }
}
