<?php

namespace Opensixt\UserAdminBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Opensixt\UserAdminBundle\Form\GroupEditForm;
use Opensixt\BikiniTranslateBundle\Entity\Group;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class GroupController extends AbstractController
{
    /** @var int */
    public $paginationLimit;

    /** @var \Opensixt\BikiniTranslateBundle\AclHelper\Group */
    public $aclHelper;

    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page)
    {
        $this->requireAdminUser();

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('groups'));

        $query = $this->getGroupRepository()
                      ->getQueryForAllGroups();
        $pagination = $this->paginator->paginate($query, $page, $this->paginationLimit);

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Group:list.html.twig',
            array('pagination' => $pagination)
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     */
    public function viewAction($id)
    {
        $group = $this->requireGroupWithId($id);

        $this->requireAdminUser();

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('groups'), $this->generateUrl('_admin_grouplist'))
            ->addItem($this->translator->trans('group'));

        $form = $this->getGroupEditFormForGroup($group);

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Group:view.html.twig',
            array(
                'form' => $form->createView(),
                'group' => $group
            )
        );
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     */
    public function saveAction($id)
    {
        $group = $this->requireGroupWithId($id);

        $this->requireAdminUser();

        $form = $this->getGroupEditFormForGroup($group);

        $form->bind($this->request);
        if ($form->isValid()) {
            $this->em->persist($group);
            $this->em->flush();

            // flash success message
            $this->bikiniFlash->successSave();

            return $this->redirect($this->generateUrl('_admin_group', array('id' => $id)));
        } else {
            return $this->templating->renderResponse(
                'OpensixtUserAdminBundle:Group:view.html.twig',
                array(
                    'user' => $group,
                    'form' => $form->createView()
                )
            );
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $this->requireAdminUser();

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('groups'), $this->generateUrl('_admin_grouplist'))
            ->addItem($this->translator->trans('create_group'));

        $form = $this->getGroupEditFormForGroup();

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Group:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveNewAction()
    {
        $this->requireAdminUser();

        $group = new Group();

        $form = $this->getGroupEditFormForGroup($group);
        $form->bind($this->request);

        if ($form->isValid()) {
            $this->em->persist($group);
            $this->em->flush();

            // flash success message
            $this->bikiniFlash->successSave();

            $this->aclHelper->initAclForNewGroup($group);

            return $this->redirect($this->generateUrl('_admin_grouplist'));
        }

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Group:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @param Group $group
     * @return GroupEditForm|\Symfony\Component\Form\FormInterface
     */
    private function getGroupEditFormForGroup(Group $group = null)
    {
        return $this->formFactory
                    ->create(new GroupEditForm($this->translator), $group);
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\Repository\GroupRepository
     */
    private function getGroupRepository()
    {
        return $this->em->getRepository(Group::ENTITY_GROUP);
    }

    /**
     * @param int $id
     * @return Group object
     * @throws NotFoundHttpException
     */
    private function requireGroupWithId($id)
    {
        $group = $this->getGroupRepository()->find($id);

        if (!$group) {
            throw new NotFoundHttpException();
        }

        return $group;
    }
}
