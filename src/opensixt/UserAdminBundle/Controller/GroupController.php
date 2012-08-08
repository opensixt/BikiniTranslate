<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use opensixt\UserAdminBundle\Form\GroupEdit as GroupEditForm;
use opensixt\BikiniTranslateBundle\Entity\Groups;

class GroupController extends AbstractController
{
    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page)
    {
        $this->requireAdminUser();

        $query = $this->getGroupRepository()
                      ->getQueryForAllGroups();
        $pagination = $this->paginator->paginate($query, $page, 25);

        return $this->templating->renderResponse('opensixtUserAdminBundle:Group:list.html.twig',
                                                 array('pagination' => $pagination));
    }

    /**
     * @param int $id
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($id)
    {
        $group = $this->getGroupRepository()->find($id);

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
     * @throws AccessDeniedException
     */
    public function saveAction($id)
    {
        $group = $this->getGroupRepository()->find($id);

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
     * @param Groups $group
     * @return GroupEditForm|\Symfony\Component\Form\FormInterface
     */
    private function getGroupEditFormForGroup(Groups $group)
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
}