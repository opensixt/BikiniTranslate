<?php

namespace opensixt\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PageController extends Controller
{
    
    public function indexAction($page)
    {
        return $this->render('opensixtContentBundle:Page:index.html.twig', array('page' => $page));
    }
}
