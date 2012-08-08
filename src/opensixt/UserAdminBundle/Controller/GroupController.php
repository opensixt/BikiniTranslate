<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use opensixt\UserAdminBundle\Form\GroupEdit as GroupEditForm;
use opensixt\BikiniTranslateBundle\Entity\Groups;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class GroupController extends AbstractController
{
    /** @var int */
    public $listNumItems;

    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page)
    {
        $this->requireAdminUser();

        $query = $this->getGroupRepository()
                      ->getQueryForAllGroups();
        $pagination = $this->paginator->paginate($query, $page, $this->listNumItems);

        return $this->templating->renderResponse('opensixtUserAdminBundle:Group:list.html.twig',
                                                 array('pagination' => $pagination));
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

        if (!($this->isAdminUser() || $this->securityContext->isGranted('VIEW', $group))) {
            throw new AccessDeniedException();
        }

        $form = $this->getGroupEditFormForGroup($group);

        return $this->templating->renderResponse('opensixtUserAdminBundle:Group:view.html.twig',
                                                 array('form' => $form->createView(),
                                                       'group' => $group));
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

        if (!($this->isAdminUser() || $this->securityContext->isGranted('EDIT', $group))) {
            throw new AccessDeniedException();
        }

        $form = $this->getGroupEditFormForGroup($group);

        $form->bind($this->request);
        if ($form->isValid()) {
            $this->em->persist($group);
            $this->em->flush();

            return $this->redirect($this->generateUrl('_admin_group', array('id' => $id)));
        } else {
            return $this->templating->renderResponse('opensixtUserAdminBundle:Group:view.html.twig',
                                                             array('user' => $group,
                                                                   'form' => $form->createView()));
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $this->requireAdminUser();

        $form = $this->getGroupEditFormForGroup();

        return $this->templating->renderResponse('opensixtUserAdminBundle:Group:create.html.twig',
                                                 array('form' => $form->createView()));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveNewAction()
    {
        $this->requireAdminUser();

        $group = new Groups();

        $form = $this->getGroupEditFormForGroup($group);
        $form->bind($this->request);

        if ($form->isValid()) {
            $this->em->persist($group);
            $this->em->flush();

            $this->initAclForNewGroup($group);

            return $this->redirect($this->generateUrl('_admin_grouplist'));
        }

        return $this->templating->renderResponse('opensixtUserAdminBundle:Group:create.html.twig',
                                                 array('form' => $form->createView()));
    }

    /**
     * @param Groups $group
     */
    private function initAclForNewGroup(Groups $group)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($group));

        $roleIdentity = new RoleSecurityIdentity('ROLE_ADMIN');

        $mask = new MaskBuilder();
        $mask->reset();
        $mask->add('master');
        $acl->insertObjectAce($roleIdentity, $mask->get());

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * @param Groups $group
     * @return GroupEditForm|\Symfony\Component\Form\FormInterface
     */
    private function getGroupEditFormForGroup(Groups $group = null)
    {
        return $this->formFactory
                    ->create(new GroupEditForm($this->translator), $group);
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Repository\GroupsRepository
     */
    private function getGroupRepository()
    {
        return $this->em->getRepository('opensixtBikiniTranslateBundle:Groups');
    }

    /**
     * @param int $id
     * @return Groups object
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