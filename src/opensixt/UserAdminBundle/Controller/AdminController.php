<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class AdminController extends Controller
{

    public function indexAction()
    {
        return $this->render('opensixtUserAdminBundle:UserAdmin:index.html.twig');
    }

    public function userlistAction()
    {
        $userlist = $this->getUserList();

        return $this->render('opensixtUserAdminBundle:UserAdmin:userlist.html.twig',
            array('userlist' => $userlist));
    }

    /**
     * Get Userlist
     *
     * @return array userlist
     * @throws type
     */
    protected function getUserList()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('opensixtUserAdminBundle:User');
        $userlist = $user->getUserList();

        return $userlist;
    }
}
