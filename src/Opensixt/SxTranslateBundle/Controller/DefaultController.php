<?php

namespace Opensixt\SxTranslateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('OpensixtSxTranslateBundle:Default:index.html.twig', array('name' => $name));
    }
}
