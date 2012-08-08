<?php

namespace opensixt\UserAdminBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use opensixt\UserAdminBundle\Form\ResourceEdit as ResourceEditForm;
use opensixt\BikiniTranslateBundle\Entity\Resource;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class ResourceController extends AbstractController
{
    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($page = 1)
    {
        $this->requireAdminUser();

        $query = $this->getResourceRepository()
                      ->getQueryForAllResources();
        $pagination = $this->paginator->paginate($query, $page, 25);

        return $this->templating->renderResponse('opensixtUserAdminBundle:Resource:list.html.twig',
                                                 array('pagination' => $pagination));
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function viewAction($id)
    {
        $resource = $this->requireResourceWithId($id);

        if (!($this->isAdminUser() || $this->securityContext->isGranted('VIEW', $resource))) {
            throw new AccessDeniedException();
        }

        $form = $this->getResourceEditFormForResource($resource);

        return $this->templating->renderResponse('opensixtUserAdminBundle:Resource:view.html.twig',
                                                 array('form' => $form->createView(),
                                                       'resource' => $resource));
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function saveAction($id)
    {
        $resource = $this->requireResourceWithId($id);

        if (!($this->isAdminUser() || $this->securityContext->isGranted('EDIT', $resource))) {
            throw new AccessDeniedException();
        }

        $form = $this->getResourceEditFormForResource($resource);
        $form->bind($this->request);

        if ($form->isValid()) {
            $this->em->persist($resource);
            $this->em->flush();

            return $this->redirect($this->generateUrl('_admin_resource', array('id' => $id)));
        }

        return $this->templating->renderResponse('opensixtUserAdminBundle:Resource:view.html.twig',
                                                 array('form' => $form->createView(),
                                                       'resource' => $resource));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $this->requireAdminUser();

        $form = $this->getResourceEditFormForResource();

        return $this->templating->renderResponse('opensixtUserAdminBundle:Resource:create.html.twig',
                                                 array('form' => $form->createView()));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveNewAction()
    {
        $this->requireAdminUser();

        $resource = new Resource();

        $form = $this->getResourceEditFormForResource($resource);
        $form->bind($this->request);

        if ($form->isValid()) {
            $this->em->persist($resource);
            $this->em->flush();

            $this->initAclForNewResource($resource);

            return $this->redirect($this->generateUrl('_admin_reslist'));
        }

        return $this->templating->renderResponse('opensixtUserAdminBundle:Resource:create.html.twig',
                                                 array('form' => $form->createView()));
    }

    /**
     * @param Resource $resource
     */
    private function initAclForNewResource(Resource $resource)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($resource));

        $roleIdentity = new RoleSecurityIdentity('ROLE_ADMIN');

        $mask = new MaskBuilder();
        $mask->reset();
        $mask->add('master');
        $acl->insertObjectAce($roleIdentity, $mask->get());

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * @param \opensixt\BikiniTranslateBundle\Entity\Resource $resource
     * @return ResourceEditForm|\Symfony\Component\Form\Tests\FormInterface
     */
    private function getResourceEditFormForResource(Resource $resource = null)
    {
        return $this->formFactory
                    ->create(new ResourceEditForm($this->translator), $resource);
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Repository\ResourceRepository
     */
    private function getResourceRepository()
    {
        return $this->em->getRepository('opensixtBikiniTranslateBundle:Resource');
    }

    /**
     * @param int $id
     * @return Resource
     * @throws NotFoundHttpException
     */
    private function requireResourceWithId($id)
    {
        $resource = $this->getResourceRepository()->find($id);

        if (!$resource) {
            throw new NotFoundHttpException();
        }
        return $resource;
    }
}