<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use opensixt\UserAdminBundle\Form\LanguageEditForm;
use opensixt\BikiniTranslateBundle\Entity\Language;
use opensixt\BikiniTranslateBundle\Repository\LanguageRepository as LangRepo;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class LanguageController extends AbstractController
{
    /** @var int */
    public $paginationLimit;

    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page = 1)
    {
        $this->requireAdminUser();

        $query = $this->getLanguageRepository()
                      ->getQueryForAllLanguages();
        $pagination = $this->paginator->paginate($query, $page, $this->paginationLimit);

        return $this->templating->renderResponse(
            'opensixtUserAdminBundle:Language:list.html.twig',
            array('pagination' => $pagination)
        );
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function viewAction($id)
    {
        $language = $this->requireLanguageWithId($id);

        if (!$this->securityContext->isGranted('VIEW', $language)) {
            throw new AccessDeniedException();
        }

        $form = $this->getLanguageEditFormForLanguage($language);

        return $this->templating->renderResponse(
            'opensixtUserAdminBundle:Language:view.html.twig',
            array(
                'form' => $form->createView(),
                'language' => $language
            )
        );
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function saveAction($id)
    {
        $language = $this->requireLanguageWithId($id);

        if (!$this->securityContext->isGranted('EDIT', $language)) {
            throw new AccessDeniedException();
        }

        $form = $this->getLanguageEditFormForLanguage($language);
        $form->bind($this->request);

        if ($form->isValid()) {
            $this->em->persist($language);
            $this->em->flush();

            return $this->redirect($this->generateUrl('_admin_language', array('id' => $id)));
        }

        return $this->templating->renderResponse(
            'opensixtUserAdminBundle:Language:view.html.twig',
            array(
                'form' => $form->createView(),
                'language' => $language
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $this->requireAdminUser();

        $form = $this->getLanguageEditFormForLanguage();

        return $this->templating->renderResponse(
            'opensixtUserAdminBundle:Language:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveNewAction()
    {
        $this->requireAdminUser();

        $language = new Language();

        $form = $this->getLanguageEditFormForLanguage($language);
        $form->bind($this->request);

        if ($form->isValid()) {
            $this->em->persist($language);
            $this->em->flush();

            $this->initAclForNewLanguage($language);

            return $this->redirect($this->generateUrl('_admin_langlist'));
        }

        return $this->templating->renderResponse(
            'opensixtUserAdminBundle:Language:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @param Language $language
     */
    private function initAclForNewLanguage(Language $language)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($language));

        $roleIdentity = new RoleSecurityIdentity('ROLE_ADMIN');

        $mask = new MaskBuilder();
        $mask->reset();
        $mask->add('master');
        $acl->insertObjectAce($roleIdentity, $mask->get());

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * @param \opensixt\BikiniTranslateBundle\Entity\Language $language
     * @return LanguageEditForm|\Symfony\Component\Form\Tests\FormInterface
     */
    private function getLanguageEditFormForLanguage(Language $language = null)
    {
        return $this->formFactory
                    ->create(new LanguageEditForm($this->translator), $language);
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Repository\LanguageRepository
     */
    private function getLanguageRepository()
    {
        return $this->em->getRepository(LangRepo::ENTITY_LANGUAGE);
    }

    /**
     * @param int $id
     * @return Language
     * @throws NotFoundHttpException
     */
    private function requireLanguageWithId($id)
    {
        $language = $this->getLanguageRepository()->find($id);

        if (!$language) {
            throw new NotFoundHttpException();
        }
        return $language;
    }
}

