<?php

namespace opensixt\UserAdminBundle\Controller;

use opensixt\BikiniTranslateBundle\Helpers\Pagination;
use opensixt\UserAdminBundle\Form\UserSearch as UserSearchForm;
use opensixt\UserAdminBundle\Form\UserEdit as UserEditForm;

use opensixt\BikiniTranslateBundle\Entity\User;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class UserController
{
    /** @var \Symfony\Component\HttpFoundation\Request */
    public $request;

    /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    public $translator;

    /** @var \Doctrine\ORM\EntityManager */
    public $em;

    /** @var \Symfony\Component\Form\FormFactory */
    public $formFactory;

    /** @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface */
    public $templating;

    /** @var \Knp\Component\Pager\Paginator */
    public $paginator;

    /** @var \Symfony\Component\Security\Core\SecurityContext */
    public $securityContext;

    /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
    public $router;

    /** @var \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface */
    public $aclProvider;

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
        $pagination = $this->paginator->paginate($query, $page, 25);

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
        $user = $this->getUserWithId($id);

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveAction($id)
    {
        $user = $this->getUserWithId($id);

        if (!($this->isAdminUser() || $this->securityContext->isGranted('EDIT', $user))) {
            throw new AccessDeniedException();
        }

        $form = $this->getEditUserFormForUser($user);

        $form->bind($this->request);
        if ($form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            return $this->redirect($this->generateUrl('_admin_user', array('id' => $id)));
        } else {
            return $this->templating->renderResponse('opensixtUserAdminBundle:User:view.html.twig',
                                                             array('user' => $user,
                                                                   'form' => $form->createView()));
        }
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveNewAction()
    {
        $this->requireAdminUser();

        $user = new User();

        $form = $this->getEditUserFormForUser($user);
        $form->bind($this->request);

        if ($form->isValid()) {
            if ($form->get('newPassword')->getData()) {
                if ($form['confirmPassword']->getData() == $form['newPassword']->getData()) {
                    $user->setPassword($form['newPassword']->getData());
                }
            }

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
    private function getUserWithId($id)
    {
        $user = $this->getUserRepository()->find($id);
        if (!$user) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
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
        return $this->formFactory
                    ->create(new UserEditForm($this->translator), $user);
    }

    /**
     * @param string $url
     * @param int $status
     * @return RedirectResponse
     */
    private function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * @param string $route
     * @param array $parameters
     * @param bool $absolute
     * @return mixed
     */
    private function generateUrl($route, $parameters = array(), $absolute = false)
    {
        return $this->router->generate($route, $parameters, $absolute);
    }

    /**
     * @throws AccessDeniedException
     */
    private function requireAdminUser()
    {
        if (!$this->isAdminUser()) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @return bool
     */
    private function isAdminUser()
    {
        return $this->securityContext->isGranted('ROLE_ADMIN');
    }
}