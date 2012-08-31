<?php

namespace opensixt\BikiniTranslateBundle\Controller;

use opensixt\BikiniTranslateBundle\Form\CleanTextForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class CleanTextController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \opensixt\BikiniTranslateBundle\Services\FlaggedText
     */
    public $searcher;

    /** @var \opensixt\BikiniTranslateBundle\Acl\UserPermissions */
    public $userPermissions;

    /**
     * cleantext Action
     *
     * @return Response A Response instance
     */
    public function cleantextAction($page)
    {
        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();

        // retrieve request parameters
        $searchResource = $this->getFieldFromRequest('resource');
        $searchLanguage = $this->getFieldFromRequest('locale');
        $searchResources = $this->getSearchResources();

        // Update texts or new search
        if ($this->request->getMethod() == 'POST') {
            $formData = $this->getRequestData($this->request);

            if (isset($formData['action']) && $formData['action'] == 'search') {
                $page = 1;
            }
            if (isset($formData['action']) && $formData['action'] == 'save') {
                $textsToRelease = $this->getTextsToSaveFromRequest(
                    $formData,
                    'chk'
                );
                if (count($textsToRelease)) {
                    $this->searcher->deleteTexts(array_keys($textsToRelease));
                    $this->session->getFlashBag()->add(
                        'notice',
                        $this->translator->trans('save_success')
                    );
                }
            }
        }

        // set search parameters
        $this->searcher->setLocales(array_keys($locales));

        // get search results
        $results = $this->searcher->getData(
            $page,
            $searchLanguage,
            $searchResources,
            date("Y-m-d")
        );

        // ids of texts
        $ids = $this->getIdsFromResults($results);

        $form = $this->formFactory
            ->create(
                new CleanTextForm(),
                null,
                array(
                    'searchResource' => $searchResource,
                    'resources'      => $resources,
                    'searchLanguage' => $searchLanguage,
                    'locales'        => $locales,
                    'ids'            => $ids,
                )
            );

        $templateParam = array(
            'form'          => $form->createView(),
            'resource'      => $searchResource,
            'locale'        => $searchLanguage,
        );
        if (isset($results)) {
            $templateParam['searchResults'] = $results;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:cleantext.html.twig',
            $templateParam
        );
    }
}

