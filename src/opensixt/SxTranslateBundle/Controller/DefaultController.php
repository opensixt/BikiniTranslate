<?php

namespace opensixt\SxTranslateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('opensixtSxTranslateBundle:Default:index.html.twig', array('name' => $name));
    }
}

