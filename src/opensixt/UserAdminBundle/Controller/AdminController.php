<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use opensixt\BikiniTranslateBundle\Entity\User;
use opensixt\BikiniTranslateBundle\Entity\Groups;
use opensixt\BikiniTranslateBundle\Entity\Language;
use opensixt\BikiniTranslateBundle\Entity\Resource;

use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormError;

use opensixt\BikiniTranslateBundle\Helpers\Pagination;

/**
 * User Administration Controller
 */
class AdminController extends Controller
{
    public function indexAction()
    {
        return $this->render('opensixtBikiniTranslateBundle:Translate:index.html.twig');
    }
}
