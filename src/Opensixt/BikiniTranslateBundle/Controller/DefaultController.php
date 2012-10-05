<?php

namespace Opensixt\BikiniTranslateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class DefaultController extends Controller
{
    /**
     * @param string $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page)
    {
        return $this->redirect($this->generateUrl('_user_admin_home'));
    }

    public function adminAction()
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");

        // Simple example
        $breadcrumbs
            ->addItem($this->get('translator')->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->get('translator')->trans('admin_home'), $this->generateUrl('_user_admin_home'));

        return $this->render('::admin.html.twig');
    }
}
