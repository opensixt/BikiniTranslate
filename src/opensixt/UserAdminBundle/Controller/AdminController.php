<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use opensixt\UserAdminBundle\Entity\User;
use opensixt\UserAdminBundle\Entity\Groups;

use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

use opensixt\UserAdminBundle\Helpers\PaginationBar;

/**
 * User Administration Controller
 */
class AdminController extends Controller
{

    /**
     * Pagination limit
     * @var int
     */
    private $paginationLimit;

    public function __construct() {
        $this->paginationLimit = 5;
    }


    public function indexAction()
    {
        return $this->render('opensixtUserAdminBundle:UserAdmin:index.html.twig');
    }

    /**
     * Controller Action: userlist
     *
     * @param $page
     * @return Response A Response instance
     */
    public function userlistAction($page = 1)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ur = $em->getRepository('opensixtUserAdminBundle:User');

        $userCount = $ur->getUserCount();

        $pagination = new PaginationBar($userCount, $this->paginationLimit, $page);
        $paginationBar = $pagination->getPaginationBar();

        $userlist = $ur->getUserListWithPagination(
            $page,
            $this->paginationLimit,
            $pagination->getOffset());

        return $this->render('opensixtUserAdminBundle:UserAdmin:userlist.html.twig',
            array(
                'userlist' => $userlist,
                'paginationbar' => $paginationBar,
                )
            );
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

        $translator = $this->get('translator');

        if ($id) {
            // get user from db
            $user = $em->find('opensixtUserAdminBundle:User', $id);
        } else {
            // new user
            $user = new User();
        }

        $form = $this->createFormBuilder($user)
            ->add('username', 'text', array(
                'label' =>  $translator->trans('username') . ': ',
            ))
            ->add('email', 'email', array(
                'label' => $translator->trans('email') . ': ',
            ))
            ->add('isactive', 'checkbox', array(
                'label'    => $translator->trans('active') . ': ',
                'value'    => 1,
                'required' => false
            ))
            ->add('userroles', 'entity', array(
                'label'     => $translator->trans('roles') . ': ',
                'class'     => 'opensixtUserAdminBundle:Role',
                'property'  => 'label',
                'multiple'  => true,
                'expanded'  => true
            ))
            ->add('userlanguages', 'entity', array(
                'label'     => $translator->trans('languages') . ': ',
                'class'     => 'opensixtUserAdminBundle:Language',
                'property'  => 'locale',
                'multiple'  => true,
                'expanded'  => true
            ))
            ->add('usergroups', 'entity', array(
                'label'     => $translator->trans('groups') . ': ',
                'class'     => 'opensixtUserAdminBundle:Groups',
                'property'  => 'name',
                'multiple'  => true,
                'expanded'  => true
            ))
            ->add('newPassword', 'password', array(
                'label'         => $translator->trans('new_password') . ': ',
                'property_path' => false,
                'required'      => false))
            ->add('confirmPassword', 'password', array(
                'label'         => $translator->trans('confirm_password') . ': ',
                'property_path' => false,
                'required'      => false))
            ->addValidator(new CallbackValidator(function($form) use ($user, $translator)
                {
                    //if($password != $user->getPassword()) {
                    //    $form['password']->addError(new FormError('Incorrect password'));
                    //}
                    if($form['confirmPassword']->getData() != $form['newPassword']->getData()) {
                        $form['confirmPassword']->addError(new FormError($translator->trans('passwords_must_match')));
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

    /**
     * Controller Action: grouplist
     *
     * @return Response A Response instance
     */
    public function grouplistAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $gr = $em->getRepository('opensixtUserAdminBundle:Groups');

        $grouplist = $gr->getGroupList();

        return $this->render('opensixtUserAdminBundle:UserAdmin:grouplist.html.twig',
            array('grouplist' => $grouplist));
    }

    /**
     * Controller Action: groupdata
     *
     * @param int $id
     * @return Response a Response instance
     */
    public function groupdataAction($id)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $translator = $this->get('translator');

        if ($id) {
            // get group from db
            $group = $em->find('opensixtUserAdminBundle:Groups', $id);
        } else {
            // new group
            $group = new Groups();
        }

        $form = $this->createFormBuilder($group)
            ->add('name', 'text', array(
                'label'     =>  $translator->trans('groupname') . ': ',
            ))
            ->add('description', 'text', array(
                'label'     => $translator->trans('description') . ': ',
                'required'  => false
            ))
            ->add('resources', 'entity', array(
                'label'     => $translator->trans('resources') . ': ',
                'class'     => 'opensixtUserAdminBundle:Resource',
                'property'  => 'name',
                'multiple'  => true,
                'expanded'  => true
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bindRequest($request);

            if ($form->isValid()) {
                // save changes
                $em->persist($group);
                $em->flush();
            } else {
                var_dump($form->getErrors());
            }
        }

        return $this->render('opensixtUserAdminBundle:UserAdmin:groupdata.html.twig',
            array(
                'form' => $form->createView(),
                'id' => $id,
                ));
    }



}
