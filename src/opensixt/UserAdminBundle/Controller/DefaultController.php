<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use opensixt\BikiniTranslateBundle\Entity\User;
use opensixt\BikiniTranslateBundle\Entity\Group;
use opensixt\BikiniTranslateBundle\Entity\Language;
use opensixt\BikiniTranslateBundle\Entity\Resource;

use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormError;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");

        // Simple example
        $breadcrumbs
            ->addItem($this->get('translator')->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->get('translator')->trans('admin_home'), $this->generateUrl('_user_admin_home'));

        return $this->render('opensixtUserAdminBundle:Default:index.html.twig');
    }
}

