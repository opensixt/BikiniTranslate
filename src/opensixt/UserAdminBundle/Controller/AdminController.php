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
    /**
     * Pagination limit
     * @var int
     */
    private $_paginationLimit;

    public function __construct() {
        $this->_paginationLimit = 15;
    }

    public function indexAction()
    {
        return $this->render('opensixtBikiniTranslateBundle:Translate:index.html.twig');
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
            $group = $em->find('opensixtBikiniTranslateBundle:Groups', $id);
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
                'class'     => 'opensixtBikiniTranslateBundle:Resource',
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
        $lr = $em->getRepository('opensixtBikiniTranslateBundle:Language');

        $langCount = $lr->getLangCount();

        $pagination = new Pagination(
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
            $lang = $em->find('opensixtBikiniTranslateBundle:Language', $id);
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
        $rr = $em->getRepository('opensixtBikiniTranslateBundle:Resource');

        $resCount = $rr->getResourceCount();

        $pagination = new Pagination(
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
            $resource = $em->find('opensixtBikiniTranslateBundle:Resource', $id);
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
