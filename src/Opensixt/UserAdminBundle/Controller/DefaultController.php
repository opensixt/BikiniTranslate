<?php

namespace Opensixt\UserAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Opensixt\BikiniTranslateBundle\Entity\User;
use Opensixt\BikiniTranslateBundle\Entity\Group;
use Opensixt\BikiniTranslateBundle\Entity\Language;
use Opensixt\BikiniTranslateBundle\Entity\Resource;

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

        return $this->render('OpensixtUserAdminBundle:Default:index.html.twig');
    }
}
