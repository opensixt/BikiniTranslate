<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use opensixt\UserAdminBundle\Entity\User;
use opensixt\UserAdminBundle\Entity\Groups;
use opensixt\UserAdminBundle\Entity\Language;
use opensixt\UserAdminBundle\Entity\Resource;

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
    private $_paginationLimit;

    public function __construct() {
        $this->_paginationLimit = 10;
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

        $pagination = new PaginationBar($userCount, $this->_paginationLimit, $page);
        $paginationBar = $pagination->getPaginationBar();

        $userlist = $ur->getUserListWithPagination(
            $this->_paginationLimit,
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
     * @param int $page
     * @return Response A Response instance
     */
    public function grouplistAction($page)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $gr = $em->getRepository('opensixtUserAdminBundle:Groups');

        $groupCount = $gr->getGroupCount();

        $pagination = new PaginationBar($groupCount, $this->_paginationLimit, $page);
        $paginationBar = $pagination->getPaginationBar();

        $grouplist = $gr->getGroupListWithPagination(
            $this->_paginationLimit,
            $pagination->getOffset());

        return $this->render('opensixtUserAdminBundle:UserAdmin:grouplist.html.twig',
            array(
                'grouplist' => $grouplist,
                'paginationbar' => $paginationBar,
                )
            );
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

    /**
     * Controller Action: langlist - Languages
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function langlistAction($page = 1)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository('opensixtUserAdminBundle:Language');

        $langCount = $lr->getLangCount();

        $pagination = new PaginationBar(
            $langCount,
            $this->_paginationLimit,
            $page);
        $paginationBar = $pagination->getPaginationBar();

        $langList = $lr->getLangListWithPagination(
            $this->_paginationLimit,
            $pagination->getOffset());

        return $this->render('opensixtUserAdminBundle:UserAdmin:langlist.html.twig',
            array(
                'langlist' => $langList,
                'paginationbar' => $paginationBar,
                )
            );
    }

    /**
     * Controller Action: langdata
     *
     * @param int $id
     * @return Response a Response instance
     */
    public function langdataAction($id)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $translator = $this->get('translator');

        if ($id) {
            // get language from db
            $lang = $em->find('opensixtUserAdminBundle:Language', $id);
        } else {
            // new language
            $lang = new Language();
        }

        $form = $this->createFormBuilder($lang)
            ->add('locale', 'text', array(
                'label'     =>  $translator->trans('language_name') . ': ',
            ))
            ->add('description', 'text', array(
                'label'     => $translator->trans('description') . ': ',
                'required'  => false
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bindRequest($request);

            if ($form->isValid()) {
                // save changes
                $em->persist($lang);
                $em->flush();
            } else {
                var_dump($form->getErrors());
            }
        }

        return $this->render('opensixtUserAdminBundle:UserAdmin:langdata.html.twig',
            array(
                'form' => $form->createView(),
                'id' => $id,
                ));
    }

    /**
     * Controller Action: reslist - Resources
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function reslistAction($page = 1)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $rr = $em->getRepository('opensixtUserAdminBundle:Resource');

        $resCount = $rr->getResourceCount();

        $pagination = new PaginationBar(
            $resCount,
            $this->_paginationLimit,
            $page);
        $paginationBar = $pagination->getPaginationBar();

        $resList = $rr->getResourceListWithPagination(
            $this->_paginationLimit,
            $pagination->getOffset());

        return $this->render('opensixtUserAdminBundle:UserAdmin:reslist.html.twig',
            array(
                'reslist' => $resList,
                'paginationbar' => $paginationBar,
                )
            );
    }

    /**
     * Controller Action: resdata
     *
     * @param int $id
     * @return Response a Response instance
     */
    public function resdataAction($id)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $translator = $this->get('translator');

        if ($id) {
            // get $resource from db
            $resource = $em->find('opensixtUserAdminBundle:Resource', $id);
        } else {
            // new resource
            $resource = new Resource();
        }

        $form = $this->createFormBuilder($resource)
            ->add('name', 'text', array(
                'label'     =>  $translator->trans('resource_name') . ': ',
            ))
            ->add('description', 'text', array(
                'label'     => $translator->trans('description') . ': ',
                'required'  => false
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bindRequest($request);

            if ($form->isValid()) {
                // save changes
                $em->persist($resource);
                $em->flush();
            } else {
                var_dump($form->getErrors());
            }
        }

        return $this->render('opensixtUserAdminBundle:UserAdmin:resdata.html.twig',
            array(
                'form' => $form->createView(),
                'id' => $id,
                ));
    }

}
