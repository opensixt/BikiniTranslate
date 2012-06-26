<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use opensixt\UserAdminBundle\Entity\User;

/**
 * User Administration Controller
 */
class AdminController extends Controller
{

    public function indexAction()
    {
        return $this->render('opensixtUserAdminBundle:UserAdmin:index.html.twig');
    }

    /**
     * Controller Action: userlist
     *
     * @return Response A Response instance
     */
    public function userlistAction()
    {
        $userlist = $this->getUserData();

        return $this->render('opensixtUserAdminBundle:UserAdmin:userlist.html.twig',
            array('userlist' => $userlist));
    }

    /**
     * Get list of users or current user data (if $useris doesn't set)
     *
     * @return array userlist
     */
    protected function getUserData()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository('opensixtUserAdminBundle:User');

        $userlist = $ur->getUserData();

        return $userlist;
    }

    /**
     * Controller Action: userdata
     *
     * @param int $id
     * @return Response a Response instance
     */
    public function userdataAction($id)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        if ($id) {
            // get user from db
            $user = $em->find('opensixtUserAdminBundle:User', $id);
        } else {
            // new user
            $user = new User();
        }

        $form = $this->createFormBuilder($user)
            ->add('username', 'text', array(
                'label' => 'Username: '
            ))
            ->add('email', 'email', array(
                'label' => 'Email: '
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bindRequest($request);

            if ($form->isValid()) {
                // save changes
                $em->persist($user);
                $em->flush();
            } else {
                var_dump($form->getErrors());
            }
        }

        return $this->render('opensixtUserAdminBundle:UserAdmin:userdata.html.twig',
            array(
                'form' => $form->createView(),
                'id' => $id,
                ));
    }

}
