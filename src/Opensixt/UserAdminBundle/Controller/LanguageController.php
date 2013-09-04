<?php

namespace Opensixt\UserAdminBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Opensixt\UserAdminBundle\Form\LanguageEditForm;
use Opensixt\BikiniTranslateBundle\Entity\Language;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class LanguageController extends AbstractController
{
    /** @var int */
    public $paginationLimit;

    /** @var \Opensixt\BikiniTranslateBundle\AclHelper\Language */
    public $aclHelper;

    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page = 1)
    {
        $this->requireAdminUser();

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('languages'));

        $query = $this->getLanguageRepository()
                      ->getQueryForAllLanguages();
        $pagination = $this->paginator->paginate($query, $page, $this->paginationLimit);

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Language:list.html.twig',
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

        $this->requireAdminUser();

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('resources'), $this->generateUrl('_admin_langlist'))
            ->addItem($this->translator->trans('language'));

        $form = $this->getLanguageEditFormForLanguage($language);

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Language:view.html.twig',
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

        $this->requireAdminUser();

        $form = $this->getLanguageEditFormForLanguage($language);
        $form->bind($this->request);

        if ($form->isValid()) {
            $this->em->persist($language);
            $this->em->flush();

            // flash success message
            $this->bikiniFlash->successSave();

            return $this->redirect($this->generateUrl('_admin_language', array('id' => $id)));
        }

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Language:view.html.twig',
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

        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('languages'), $this->generateUrl('_admin_langlist'))
            ->addItem($this->translator->trans('create_language'));

        $form = $this->getLanguageEditFormForLanguage();

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Language:create.html.twig',
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

            // flash success message
            $this->bikiniFlash->successSave();

            $this->aclHelper->initAclForNewLanguage($language);

            return $this->redirect($this->generateUrl('_admin_langlist'));
        }

        return $this->templating->renderResponse(
            'OpensixtUserAdminBundle:Language:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @param \Opensixt\BikiniTranslateBundle\Entity\Language $language
     * @return LanguageEditForm|\Symfony\Component\Form\Tests\FormInterface
     */
    private function getLanguageEditFormForLanguage(Language $language = null)
    {
        return $this->formFactory
                    ->create(new LanguageEditForm($this->translator), $language);
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\Repository\LanguageRepository
     */
    private function getLanguageRepository()
    {
        return $this->em->getRepository(Language::ENTITY_LANGUAGE);
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
