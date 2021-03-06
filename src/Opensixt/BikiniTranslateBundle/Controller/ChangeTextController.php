<?php

namespace Opensixt\BikiniTranslateBundle\Controller;

use Opensixt\BikiniTranslateBundle\Form\ChangeTextForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class ChangeTextController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \Opensixt\BikiniTranslateBundle\IntermediateLayer\SearchString
     */
    public $searcher;

    /** @var \Opensixt\BikiniTranslateBundle\AclHelper\UserPermissions */
    public $userPermissions;

    /**
     * changetext Action
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function indexAction($page)
    {
        $this->breadcrumbs
            ->addItem($this->translator->trans('home'), $this->generateUrl('_home'))
            ->addItem($this->translator->trans('menu.translation'))
            ->addItem($this->translator->trans('menu.translation.change_text'));

        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();

        // retrieve request parameters
        $searchPhrase = $this->getFieldFromRequest('search');
        $searchLanguage = $this->getFieldFromRequest('locale');
        $searchResource = $this->getFieldFromRequest('resource');

        // Update texts with entered values
        if ($this->request->getMethod() == 'POST') {
            $formData = $this->getRequestData($this->request);

            if (isset($formData['action']) && $formData['action'] == 'search') {
                $page = 1;
            }

            if (isset($formData['action']) && $formData['action'] == 'save') {
                $textsToSave = $this->getTextsToSaveFromRequest(
                    $formData,
                    'text'
                );
                if (count($textsToSave)) {
                    $this->searcher->updateTexts($textsToSave);
                    $this->bikiniFlash->successSave();
                    return $this->redirectAfterSave(
                        '_translate_changetext',
                        $page
                    );
                }
            }
        }

        if (strlen($searchPhrase)) {
            // set search parameters
            $searchResources = $this->getSearchResources();
            $this->searcher->setSearchParameters($searchPhrase);

            // get search results
            $results = $this->searcher->getData(
                $page,
                $searchLanguage,
                $searchResources
            );

            // ids of texts for textareas
            $ids = $this->getIdsFromResults($results);
        }

        $form = $this->formFactory
            ->create(
                new ChangeTextForm(),
                null,
                array(
                    'searchPhrase'    => $searchPhrase,
                    'searchResource'  => $searchResource,
                    'resources'       => $resources,
                    'searchLanguage'  => $searchLanguage,
                    'locales'         => $locales,
                    'ids'             => !empty($ids) ? $ids : array(),
                )
            );

        $templateParam = array(
            'form'          => $form->createView(),
            'search'        => urlencode($searchPhrase),
            'searchPhrase'  => $searchPhrase,
            'locale'        => $searchLanguage,
            'resource'      => $searchResource,
        );
        if (isset($results)) {
            $templateParam['searchResults'] = $results;
        }

        return $this->render(
            'OpensixtBikiniTranslateBundle:Translate:changetext.html.twig',
            $templateParam
        );
    }
}
