<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use opensixt\UserAdminBundle\Entity\User;

use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

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
            ->add('userroles', 'entity', array(
                'label'     => 'Roles: ',
                'class'     => 'opensixtUserAdminBundle:Role',
                'property'  => 'label',
                'multiple'  => true,
                'expanded'  => true
            ))
            ->add('userlanguages', 'entity', array(
                'label'     => 'Languages: ',
                'class'     => 'opensixtUserAdminBundle:Language',
                'property'  => 'locale',
                'multiple'  => true,
                'expanded'  => true
            ))
            ->add('newPassword', 'password', array(
                'label'         => 'New Password: ',
                'property_path' => false,
                'required'      => false))
            ->add('confirmPassword', 'password', array(
                'label'         => 'Confirm Password: ',
                'property_path' => false,
                'required'      => false))
            ->addValidator(new CallbackValidator(function($form) use ($user)
                {
                    //if($password != $user->getPassword()) {
                    //    $form['password']->addError(new FormError('Incorrect password'));
                    //}
                    if($form['confirmPassword']->getData() != $form['newPassword']->getData()) {
                        $form['confirmPassword']->addError(new FormError('Passwords must match.'));
                    }
                    /*if($form['newPassword']->getData() == '') {
                        $form['newPassword']->addError(new FormError('Password cannot be blank.'));
                    }*/
                }))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bindRequest($request);

            if ($form->isValid()) {

                if ($form->get('newPassword')->getData()) {
                    if ($form['confirmPassword']->getData() == $form['newPassword']->getData()) {
                        $encoder = new MessageDigestPasswordEncoder('md5', false, 1);
                        $password = $encoder->encodePassword(
                            $form->get('newPassword')->getData(),
                            $user->getSalt());
                        $user->setPassword($password);
                    }
                }

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
