<?php

namespace opensixt\UserAdminBundle\Controller;

use opensixt\BikiniTranslateBundle\Helpers\Pagination;
use opensixt\UserAdminBundle\Form\UserSearch as UserSearchForm;
use opensixt\UserAdminBundle\Form\UserEdit as UserEditForm;

use opensixt\BikiniTranslateBundle\Entity\User;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class UserController extends AbstractController
{
    /** @var int */
    public $listNumItems;

    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page = 1)
    {
        $this->requireAdminUser();

        $searchTerm = $this->request->get('search', '');

        $query = $this->getUserRepository()
                      ->getQueryForUserSearch($searchTerm);
        $pagination = $this->paginator->paginate($query, $page, $this->listNumItems);

        /** @var $form UserSearchForm|\Symfony\Component\Form\FormInterface */
        $form = $this->formFactory
                     ->create(new UserSearchForm($this->translator), array('search' => $searchTerm));

        return $this->templating->renderResponse('opensixtUserAdminBundle:User:list.html.twig',
                                                 array('form' => $form->createView(),
                                                       'pagination' => $pagination));
    }

    /**
     * @param int $id
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($id)
    {
        $user = $this->requireUserWithId($id);

        if (!($this->isAdminUser() || $this->securityContext->isGranted('VIEW', $user))) {
            throw new AccessDeniedException();
        }

        $form = $this->getEditUserFormForUser($user);

        return $this->templating->renderResponse('opensixtUserAdminBundle:User:view.html.twig',
                                                 array('user' => $user,
                                                       'form' => $form->createView()));
    }

    /**
     * @param int $id
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveAction($id)
    {
        $user = $this->requireUserWithId($id);

        if (!($this->isAdminUser() || $this->securityContext->isGranted('EDIT', $user))) {
            throw new AccessDeniedException();
        }

        $form = $this->getEditUserFormForUser($user);

        $form->bind($this->request);

        if ($form->isValid()) {
            $newPassword = $form->getData()->getPassword();
            if (!empty($newPassword)) {
                $user->setPassword($newPassword);
            }

            $this->em->persist($user);
            $this->em->flush();

            return $this->redirect($this->generateUrl('_admin_user', array('id' => $id)));
        }
        return $this->templating->renderResponse('opensixtUserAdminBundle:User:view.html.twig',
                                                         array('user' => $user,
                                                               'form' => $form->createView()));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $this->requireAdminUser();

        $form = $this->getEditUserFormForUser();

        return $this->templating->renderResponse('opensixtUserAdminBundle:User:create.html.twig',
                                                 array('form' => $form->createView()));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveNewAction()
    {
        $this->requireAdminUser();

        $form = $this->getEditUserFormForUser();

        $form->bind($this->request);

        if ($form->isValid()) {
            $user = $form->getData();

            $this->em->persist($user);
            $this->em->flush();

            $this->initAclForNewUser($user);

            return $this->redirect($this->generateUrl('_admin_userlist'));
        }

        return $this->templating->renderResponse('opensixtUserAdminBundle:User:create.html.twig',
                                                 array('form' => $form->createView()));
    }

    /**
     * @param User $user
     */
    private function initAclForNewUser(User $user)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($user));

        $userIdentity = new UserSecurityIdentity($user->getUsername(), get_class($user));
        $mask = new MaskBuilder();
        $mask->add('view')
             ->add(256);

        $acl->insertObjectAce($userIdentity, $mask->get());

        $roleIdentity = new RoleSecurityIdentity('ROLE_ADMIN');
        $mask->reset();
        $mask->add('master');
        $acl->insertObjectAce($roleIdentity, $mask->get());

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * @param int $id
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return User
     */
    private function requireUserWithId($id)
    {
        $user = $this->getUserRepository()->find($id);

        if (!$user) {
            throw new NotFoundHttpException();
        }
        return $user;
    }

    /**
     * @return \opensixt\UserAdminBundle\Repository\UserRepository
     */
    private function getUserRepository()
    {
        return $this->em->getRepository('opensixtBikiniTranslateBundle:User');
    }

    /**
     * @param User $user
     * @return UserEditForm|\Symfony\Component\Form\FormInterface
     */
    private function getEditUserFormForUser(User $user = null)
    {
        $intention = 'edit';
        if (null === $user) {
            $intention = 'create';
            $user = new User();
        }

        return $this->formFactory
                    ->create(new UserEditForm(), $user, array('intention' => $intention));
    }
}