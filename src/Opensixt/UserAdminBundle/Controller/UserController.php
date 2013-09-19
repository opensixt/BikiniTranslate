<?php

namespace Opensixt\UserAdminBundle\Controller;

use Opensixt\UserAdminBundle\Form\UserSearchForm;
use Opensixt\UserAdminBundle\Form\UserEditForm;
use Opensixt\BikiniTranslateBundle\Entity\User;
use Opensixt\BikiniTranslateBundle\Entity\Text;

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
    public $paginationLimit;

    /** @var \Opensixt\BikiniTranslateBundle\AclHelper\User */
    public $aclHelper;

    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page = 1)
    {

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('user_list'));

        $searchTerm     = $this->request->get('search', '');
        $searchLanguage = $this->getFieldFromRequest('locale');

        $currentUser = null;
        if (!$this->isAdminUser()) {
            $currentUser = $this->securityContext->getToken()->getUser()->getId();
        }

        $locales = $this->getUserLocales();

        // only user with admin role can see complete user list, otherwise only himself
        $query = $this->getUserRepository()->getQueryForUserSearch(
            $searchTerm,
            $searchLanguage,
            $currentUser
        );

        $pagination = $this->paginator->paginate($query, $page, $this->paginationLimit);

        /** @var $form UserSearchForm|\Symfony\Component\Form\FormInterface */
        $form = $this->formFactory
            ->create(
                new UserSearchForm($this->translator),
                array(
                    'search'  => $searchTerm,
                ),
                array(
                    'locales'        => $locales,
                    'searchLanguage' => $searchLanguage,
                )
            );

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:User:list.html.twig',
            array(
                'form' => $form->createView(),
                'pagination' => $pagination,
                'isAdmin' => $this->isAdminUser(),
            )
        );
    }

    /**
     * @param int $id
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($id)
    {
        // non admin user can view only himself
        if (!$this->isAdminUser()) {
            $currentUser = $this->securityContext->getToken()->getUser()->getId();
            if ($currentUser != $id) {
                throw new AccessDeniedException();
            }
        }

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('user_list'), $this->generateUrl('_admin_userlist'))
            ->addItem($this->translator->trans('user'));

        $user = $this->requireUserWithId($id);
        $countNotTranslatedTexts = $this->getCountNotTranslatedTexts($user);

        if (!$this->securityContext->isGranted('VIEW', $user)) {
            throw new AccessDeniedException();
        }

        $form = $this->getEditUserFormForUser($user);

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:User:view.html.twig',
            array(
                'user' => $user,
                'form' => $form->createView(),
                'countNotTranslatedTexts' => $countNotTranslatedTexts,
            )
        );
    }

    /**
     * @param int $id
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveAction($id)
    {
        $this->requireAdminUser();

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('user_list'), $this->generateUrl('_admin_userlist'))
            ->addItem($this->translator->trans('user'));

        $user = $this->requireUserWithId($id);
        $countNotTranslatedTexts = $this->getCountNotTranslatedTexts($user);

        $form = $this->getEditUserFormForUser($user);

        $form->bind($this->request);

        if ($form->isValid()) {

            $this->em->persist($user);
            $this->em->flush();

            // flash success message
            $this->bikiniFlash->successSave();

            return $this->redirect($this->generateUrl('_admin_user', array('id' => $id)));
        }

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:User:view.html.twig',
            array(
                'user' => $user,
                'form' => $form->createView(),
                'countNotTranslatedTexts' => $countNotTranslatedTexts,
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $this->requireAdminUser();

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('user_list'), $this->generateUrl('_admin_userlist'))
            ->addItem($this->translator->trans('create_user'));

        $form = $this->getEditUserFormForUser();

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:User:create.html.twig',
            array(
                'form' => $form->createView()
            )
        );
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

            // flash success message
            $this->bikiniFlash->successSave();

            // ACL
            $this->aclHelper->initAclForNewUser($user);

            return $this->redirect($this->generateUrl('_admin_userlist'));
        }

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:User:create.html.twig',
            array(
                'form' => $form->createView()
            )
        );
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
     * @return \Opensixt\UserAdminBundle\Repository\UserRepository
     */
    private function getUserRepository()
    {
        return $this->em->getRepository(User::ENTITY_USER);
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
            ->create(
                new UserEditForm($this->securityContext),
                $user,
                array(
                    'intention' => $intention,
                )
            );
    }

    /**
     *
     * @param \Opensixt\BikiniTranslateBundle\Entity\User $user
     */
    private function getCountNotTranslatedTexts($user) {

        $userLocales = $user->getUserLanguages();
        $locales = array();
        foreach ($userLocales as $locale) {
            $locales[] = $locale->getId();
        }

        $textRep = $this->em->getRepository(Text::ENTITY_TEXT);

        return $textRep->getCountNotTranslatedTexts($locales);
    }
}